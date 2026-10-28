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
    <title>Delete Account</title>
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

                <form id="step1" action="" method="POST" onsubmit="return requestCode('<?php echo $_ENV['API_URL'] ?>', 'delete-request')"
                    class="grid w-full grid-cols-1 gap-2 md:grid-cols-4 md:gap-7">
                    <div class="mb-10 col-span-1 md:col-span-4 text-theme-green text-2xl text-center">Delete your <?php echo $api_name ?> API account</div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="someone@example.com" class="p-1 border-b outline-none md:col-span-3" required />
                    <p class="text-sm italic md:col-start-2 md:col-span-3">We email you a 6 digit code to confirm. Your key stops working. Users and data stored under it are left alone.</p>

                    <!--recaptcha div-->
                    <div class="pt-3 mx-auto g-recaptcha md:col-start-2" data-sitekey="<?php echo $_ENV['CAPTCHA'] ?>"></div>

                    <div class="flex justify-end w-full md:col-start-2 md:col-span-3">
                        <input id="Submit1" name="button" type="submit" value="Email me a code" class="btn"/>
                    </div>
                </form>

                <form id="step2" action="" method="POST" onsubmit="return submitCode('<?php echo $_ENV['API_URL'] ?>', 'delete')"
                    class="grid w-full grid-cols-1 gap-2 md:grid-cols-4 md:gap-7" style="display:none">
                    <div class="mb-10 col-span-1 md:col-span-4 text-theme-green text-2xl text-center">Confirm deletion</div>
                    <p class="md:col-span-4 text-center">If <strong id="sent_to"></strong> has a key, we sent it a 6 digit code. It is valid for 15 minutes.</p>
                    <label for="code">Code</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="123456"
                        class="p-1 border-b outline-none md:col-span-3 tracking-widest" required />
                    <div class="flex justify-end w-full space-x-3 md:col-start-2 md:col-span-3">
                        <a href="#" onclick="return startOver()" class="self-center link">Start over</a>
                        <input id="Submit2" name="button" type="submit" value="Delete my account" class="btn"/>
                    </div>
                </form>

                <div id="step3" style="display:none" class="w-full text-center">
                    <p id="done" class="text-lg"></p>
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