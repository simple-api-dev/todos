<nav class="flex justify-center flex-col sm:flex-row sm:justify-between w-full text-white px-3 py-1 text-sm md:text-base bg-black items-center">
    <!-- For mobile only -->
    <div class="w-full bg-black sm:hidden my-2 flex justify-between space-x-2 items-center">
        <a href="/" class="text-white whitespace-nowrap"><?php echo $_ENV['APP_NAME']; ?></a>
        <a href="https://github.com/simple-api-dev/todos"><img src="images/github.png" alt="github" class="h-4 w-4"></a>
    </div>

    <div class="text-xl">
        <a href="/" class="hidden sm:block text-white whitespace-nowrap"><?php echo $_ENV['APP_NAME']; ?></a>
    </div>
    <ul class="flex-wrap flex space-x-2 whitespace-nowrap">
        <li><a href="/get-key.php"
        <?php 
                echo ($_SERVER['PHP_SELF'] == "/get-key.php") ? 'class="underline"' : "";
            ?>
        >Get API Key</a></li> 
        <span>|</span>
        <li><a href="/delete-account.php"
            <?php 
                echo ($_SERVER['PHP_SELF'] == "/delete-account.php") ? 'class="underline"' : "";
            ?>
        >Manage keys</a></li>
        <span>|</span>
        <li><a href="/docs.php"
                <?php
                echo ($_SERVER['PHP_SELF'] == "/docs.php") ? 'class="underline"' : "";
                ?>
            >Docs</a></li>

        <span class="hidden sm:block">|</span>
        <li class="hidden sm:block"><a href="https://github.com/simple-api-dev/todos"><img src="images/github.png" alt="github" class="h-6 w-6"></a></li>
    </ul>
</nav>