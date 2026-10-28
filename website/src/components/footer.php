<div class="absolute top-10 right-1 w-80 space-y-1" id="toasts">
    <div class="flex items-center w-full p-4 text-white rounded-lg shadow toast hidden">
        <div class="ml-3 font-normal toast-message"></div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-white hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" \
            data-collapse-toggle="toast-default"
            onclick="fadeDelete(event.currentTarget.parentNode)">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
    </div>
</div>

<script type="text/javascript" src="js/functions.js"></script> 

<footer class="bg-black flex justify-between items-center w-full bottom-0 text-xs md:text-base px-2">
    <div class="p-2 space-x-2 text-white">
        ©2022 <?php echo $_ENV['APP_NAME']; ?> 
    </div>
    <ul class="flex text-white space-x-2">
        <li><a href="/privacy.php"
                <?php
                echo ($_SERVER['PHP_SELF'] == "/privacy.php") ? 'class="underline"' : "";
                ?>
            >Privacy</a></li>
        <span>|</span>
        <li><a href="/legal.php"
                <?php
                echo ($_SERVER['PHP_SELF'] == "/legal.php") ? 'class="underline"' : "";
                ?>
            >Legal</a></li>
    </ul>
</footer>