<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Optional login through an external auth service instead of this api's own users.
 *
 * A developer points an apikey at an "auth URL" (keys service: POST /api/keys/auth-url). For that key this api sends
 *
 *     GET <auth URL>      Authorization: Bearer <token>
 *
 * and expects `200` with JSON {id, name?, email?, email_verified?} for a valid user, `401` otherwise. Any service that
 * answers like that works, including the SimpleAPI Auth API (GET /api/users/me).
 *
 * Nothing here is needed unless a key has an auth URL: keys without one never reach this class.
 * The URL is developer supplied and fetched from our servers, so it is checked before every call (https, public
 * addresses only, no redirects, short timeout, small response) and the address that was checked is the one connected to.
 */
class ExternalAuth
{
    private const VALID_SECONDS = 60;
    private const INVALID_SECONDS = 15;
    private const UNAVAILABLE_SECONDS = 5;
    private const TIMEOUT_SECONDS = 3;
    private const MAX_BODY_BYTES = 16384;

    /**
     * Ask the auth service about a token.
     *
     * @return array one of
     *   ['status' => 'ok', 'user' => ['id' => string, 'name' => string, 'email' => string, 'email_verified' => bool]]
     *   ['status' => 'invalid', 'reason' => string]       the token is not valid (answer 401)
     *   ['status' => 'unavailable', 'reason' => string]   the auth service could not be reached or used (answer 503)
     */
    public static function verify(string $url, string $token, string $integration_id) : array
    {
        // keyed by integration as well as url and token, so one developer's cached answer never serves another developer
        $cache_key = 'extauth:' . sha1($integration_id . '|' . $url . '|' . $token);
        $cached = Cache::get($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $result = self::fetch($url, $token);
        $seconds = match ($result['status']) {
            'ok' => self::VALID_SECONDS,
            'invalid' => self::INVALID_SECONDS,
            default => self::UNAVAILABLE_SECONDS,
        };
        Cache::put($cache_key, $result, $seconds);

        return $result;
    }

    private static function fetch(string $url, string $token) : array
    {
        $allow_private = filter_var(env('AUTH_URL_ALLOW_PRIVATE', false), FILTER_VALIDATE_BOOLEAN);

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower(trim($parts['host'] ?? '', '[]'));
        if ($host === '' || isset($parts['user']) || isset($parts['pass'])
            || !($scheme === 'https' || ($allow_private && $scheme === 'http'))) {
            return self::unavailable("the auth URL is not an acceptable https URL");
        }
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        $ips = self::resolve($host);
        if (!$ips) {
            return self::unavailable("the auth URL host could not be resolved");
        }
        if (!$allow_private) {
            foreach ($ips as $ip) {
                if (!self::isPublicIp($ip)) {
                    return self::unavailable("the auth URL points at a private or reserved address");
                }
            }
        }

        $options = [
            'timeout' => self::TIMEOUT_SECONDS,
            'connect_timeout' => 2,
            'http_errors' => false,
            'allow_redirects' => false,
            'stream' => true,
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'],
        ];
        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            // connect to the address that was just checked, not to whatever DNS says a moment later
            $ip = $ips[0];
            $options['curl'] = [CURLOPT_RESOLVE => ["{$host}:{$port}:" . (str_contains($ip, ':') ? "[{$ip}]" : $ip)]];
        }

        try {
            $response = (new Client())->get($url, $options);
            $status = $response->getStatusCode();
            $body = $status === 200 ? $response->getBody()->read(self::MAX_BODY_BYTES + 1) : '';
        } catch (\Throwable $ex) {
            return self::unavailable("the auth service could not be reached");
        }

        if ($status === 200) {
            return self::readUser($body);
        }
        if ($status === 408 || $status === 429 || $status >= 500 || ($status >= 300 && $status < 400)) {
            return self::unavailable("the auth service answered {$status}");
        }
        return ['status' => 'invalid', 'reason' => "the auth service answered {$status}"];
    }

    private static function readUser(string $body) : array
    {
        $json = strlen($body) > self::MAX_BODY_BYTES ? null : json_decode($body, true);
        $id = is_array($json) ? ($json['id'] ?? null) : null;

        if (!(is_string($id) || is_int($id)) || trim((string)$id) === '' || strlen((string)$id) > 191) {
            return ['status' => 'invalid', 'reason' => "the auth service did not return a user id"];
        }

        $id = trim((string)$id);
        $email = isset($json['email']) && is_string($json['email']) ? strtolower(trim($json['email'])) : '';
        if ($email === '' || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'u' . substr(sha1($id), 0, 16) . '@users.invalid';
            $verified = false;
        } else {
            $verified = ($json['email_verified'] ?? false) === true;
        }
        $name = isset($json['name']) && is_string($json['name']) && trim($json['name']) !== ''
            ? mb_substr(trim($json['name']), 0, 255) : (explode('@', $email)[0] ?: 'user');

        return ['status' => 'ok', 'user' => ['id' => $id, 'name' => $name, 'email' => $email, 'email_verified' => $verified]];
    }

    /**
     * The local user for an external user, created the first time it is seen so that ownership, votes and the like keep working.
     * Returns null when the local user has been disabled.
     */
    public static function localUser(array $external, string $integration_id, string $developer_email) : ?User
    {
        $email = $external['email'];
        $verified = $external['email_verified'];
        $admin = $verified && $email === strtolower(trim($developer_email));

        $user = User::where([['integration_id', $integration_id], ['external_id', $external['id']]])->first();

        // keys that used built in users before: a local account with the same verified email becomes this user
        if (!$user && $verified) {
            $user = User::where([['integration_id', $integration_id], ['email', $email]])->whereNull('external_id')->first();
            if ($user) {
                $user->external_id = $external['id'];
            }
        }

        $taken = fn() => User::where([['integration_id', $integration_id], ['email', $email]])
            ->where('external_id', '!=', $external['id'])->exists()
            || User::where([['integration_id', $integration_id], ['email', $email]])->whereNull('external_id')->exists();

        if (!$user) {
            $user = new User();
            $user->integration_id = $integration_id;
            $user->external_id = $external['id'];
            // no local password: this user can only sign in through the auth service
            $user->password = Hash::make(Str::random(48));
            $user->enabled = true;
            $user->email = $taken() ? 'u' . substr(sha1($external['id']), 0, 16) . '@users.invalid' : $email;
        } elseif ($user->email !== $email && !$taken()) {
            $user->email = $email;
        }

        $user->name = $external['name'];
        $user->admin = $admin;

        if ($user->isDirty()) {
            try {
                $user->save();
            } catch (QueryException $ex) {
                // two first requests at once: the other one created it
                $user = User::where([['integration_id', $integration_id], ['external_id', $external['id']]])->first();
            }
        }

        return $user && $user->enabled ? $user : null;
    }

    private static function unavailable(string $reason) : array
    {
        error_log("external auth: " . $reason);
        return ['status' => 'unavailable', 'reason' => $reason];
    }

    /**
     * @return string[] every IPv4 and IPv6 address the host resolves to
     */
    private static function resolve(string $host) : array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }
        $ips = [];
        foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            $ips[] = $record['ip'] ?? $record['ipv6'] ?? null;
        }
        if (!$ips) {
            $ips = @gethostbynamel($host) ?: [];
        }
        return array_values(array_filter($ips));
    }

    private static function isPublicIp(string $ip) : bool
    {
        // an IPv4 address written as IPv6 (::ffff:10.0.0.1) is judged as the IPv4 address
        $packed = @inet_pton($ip);
        if ($packed !== false && strlen($packed) === 16 && str_starts_with($packed, str_repeat("\0", 10) . "\xff\xff")) {
            $ip = inet_ntop(substr($packed, 12));
        }
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
