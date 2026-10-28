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
                <form id="contact_form" action="" method="POST" onsubmit="return register('<?php echo $_ENV['API_URL'] ?>')"
                    class="grid w-full grid-cols-1 gap-2 md:grid-cols-4 md:gap-7">
                    <div class="mb-10 col-span-1 md:col-span-4 text-theme-green text-2xl text-center">Register to receive your API key.</div>
                    <label for="email">Email</label>
                    <input id="email" name="email" pattern="^[a-zA-Z0-9.!#$%&'+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)$" 
                        placeholder="someone@example.com" class="p-1 border-b outline-none md:col-span-3" required />
                   <!--recaptcha div-->
                    <div class="pt-3 mx-auto g-recaptcha md:col-start-2" data-sitekey="<?php echo $_ENV['CAPTCHA'] ?>"></div>
                    <div class="flex justify-end w-full space-y-2 md:row-start-5 md:col-start-2 md:col-span-3 md:flex-row md:space-y-0 md:space-x-3">
                        <input id="Submit" name="button" type="submit"
                        class="btn"/>
                    </div>
                </form>
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