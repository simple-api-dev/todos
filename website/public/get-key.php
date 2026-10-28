<?php
// Keys are issued at keys.simpleapi.dev: email, then the emailed code, then the key is shown.
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower(explode('.', (string)($_SERVER['HTTP_HOST'] ?? 'todos'))[0])) ?: 'todos';
header('Location: https://keys.simpleapi.dev/?product=' . $slug, true, 302);
exit;
