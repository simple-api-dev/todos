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
    <title>Legal Notice</title>
</head>

<body>
    <div>
        <?php
        include $project_root . "/src/components/nav.php";
        ?>
        <main class="pl-10 pr-10 space-y-4 mt-3">
            <h1 class="title2">Legal Notice</h1>
            <h3>Liability for Contents</h3>
            <p>Legal obligations to removing information or to blocking the use of information remain unchallenged. In this case, liability is only possible at the time of knowledge about a specific violation of law. Illegal contents will be removed immediately at the time we get knowledge of them.</p>
            <h3>Liability for Links</h3>
            <p>Our offer includes links to external third-party websites. We have no influence on the contents of those websites, therefore we cannot guarantee for those contents. Providers or administrators of linked websites are always responsible for their own contents.</p>
            <p>The linked websites had been checked for possible violations of law at the time of the establishment of the link. Illegal contents were not detected at the time of the linking. A permanent monitoring of the contents of linked websites cannot be imposed without reasonable indications that there has been a violation of law. Illegal links will be removed immediately at the time we get knowledge of them.</p>
            <h3>Copyright</h3>
            <p>Contents and compilations published on these websites by the providers are subject to Canadian copyright laws. Reproduction, editing, distribution as well as the use of any kind outside the scope of the copyright law require a written permission of the author or originator. Downloads and copies of these websites are permitted for private use only.<br>The commercial use of our contents without permission of the originator is prohibited.</p>
            <p>Copyright laws of third parties are respected as long as the contents on these websites do not originate from the provider. Contributions of third parties on this site are indicated as such. However, if you notice any violations of copyright law, please inform us. Such contents will be removed immediately.</p>
        </main>
    </div>
    <div class="absolute bottom-0 w-full">
        <?php
        include $project_root . "/src/components/footer.php";
        ?>
    </div>
</body>

</html>