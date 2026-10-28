<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        $project_root = __DIR__ . "/..";
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        include $project_root . "/src/components/layout.php";

        //load environment variables
        include $project_root . "/src/envParser.php";
        LoadEnv();
    ?>
    <title>Get Key</title>
</head>
<body>
    <div class="flex flex-col justify-between min-h-screen">
        <div class="row-start-1 md:row-start-auto">
            <?php
                include $project_root . "/src/components/nav.php";
            ?>
        </div>

        <main class="flex items-center justify-center">
            <div class="mx-auto w-full max-w-2xl p-5 mx-3 border rounded-lg shadow-lg">
                <script>window.PRODUCT_URL = "<?php echo $_ENV['APP_URL'] ?>";</script>
                <?php $api_name = ucfirst(explode('.', $_ENV['APP_NAME'])[0]); ?>

                <!-- step 1: email + captcha -->
                <form id="step1" action="" method="POST" onsubmit="return requestCode('<?php echo $_ENV['API_URL'] ?>', 'request')"
                    class="grid w-full grid-cols-1 gap-2 md:grid-cols-4 md:gap-7">
                    <div class="mb-10 col-span-1 md:col-span-4 text-theme-green text-2xl text-center">Get your <?php echo $api_name ?> API key</div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="someone@example.com" class="p-1 border-b outline-none md:col-span-3" required />
                    <p class="text-sm italic md:col-start-2 md:col-span-3">We email you a 6 digit code. Enter it on the next step and your key is shown right here. Already have a key and lost it? Do the same thing and it is shown again.</p>

                    <!--recaptcha div-->
                    <div class="pt-3 mx-auto g-recaptcha md:col-start-2" data-sitekey="<?php echo $_ENV['CAPTCHA'] ?>"></div>

                    <div class="flex justify-end w-full md:col-start-2 md:col-span-3">
                        <input id="Submit1" name="button" type="submit" value="Email me a code" class="btn"/>
                    </div>
                </form>

                <!-- step 2: the code -->
                <form id="step2" action="" method="POST" onsubmit="return submitCode('<?php echo $_ENV['API_URL'] ?>', 'verify')"
                    class="grid w-full grid-cols-1 gap-2 md:grid-cols-4 md:gap-7" style="display:none">
                    <div class="mb-10 col-span-1 md:col-span-4 text-theme-green text-2xl text-center">Enter your code</div>
                    <p class="md:col-span-4 text-center">We sent a 6 digit code to <strong id="sent_to"></strong>. It is valid for 15 minutes. Check your spam folder if it does not show up.</p>
                    <label for="code">Code</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456"
                        class="p-1 border-b outline-none md:col-span-3 tracking-widest" required />
                    <div class="flex justify-end w-full space-x-3 md:col-start-2 md:col-span-3">
                        <a href="#" onclick="return startOver()" class="self-center link">Use a different email</a>
                        <input id="Submit2" name="button" type="submit" value="Show my key" class="btn"/>
                    </div>
                </form>

                <!-- step 3: the key -->
                <div id="step3" style="display:none" class="w-full text-center">
                    <div class="mb-6 text-theme-green text-2xl">Your <?php echo $api_name ?> API key</div>
                    <p id="key_note" class="mb-4"></p>
                    <pre id="apikey" class="p-3 mb-4 bg-gray-100 rounded select-all overflow-x-auto"></pre>
                    <button class="btn" onclick="return copyKey()">Copy</button>
                    <p class="mt-6 text-sm">Keep it somewhere safe. It is shown only here, we never email it. This key only works with the <?php echo $api_name ?> API. Add it to every call as <code>?apikey=your-key</code>.
                        Lost it later? Come back and request a new code and it is shown again.</p>
                    <p class="mt-4"><a class="link" href="/docs.php">Read the docs</a></p>
                </div>
            </div>
        </main>
        <div>
            <?php
                include $project_root ."/src/components/footer.php";
            ?>    
        </div>
    </div>

</body>

<!--recaptcha script-->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</html>