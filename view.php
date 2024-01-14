<?php
    include_once "./utils/no-warning.php";
    include_once "./utils/opening.php";
    require_once "./utils/db.php";
    $opening = new Opening(
        0, 
        "Titolo qui",
        "Nome episodio qui",
        null,
        "it",
        null, new DateTime(), null);
    if (isset($_GET["id"]) && ctype_digit($_GET["id"]))
    {
        $id = (int)$_GET["id"];
        $opening = Opening::Load($db, $id);
        if (!isset($opening))
        {
            throw new Exception("Risorsa non trovata", 404);
        }
    } elseif (isset($_GET["original"]) && ctype_digit($_GET["original"])) {
        $original = (int)$_GET["original"];
        if ($original < 1 || $original > 9)
        {
            throw new Exception("Risorsa non trovata", 404);
        }
        $lang = OpeningLanguage::Italian;
        if (!isEmpty($_GET["lang"]) && is_string($_GET["lang"]))
        {
            $lang = Opening::StringToLanguage($_GET["lang"]);
        }
        $opening = Opening::LoadOriginal($original, $lang);
    } else {
        if (!isEmpty($_GET["title"]) && is_string($_GET["title"]))
        {
            $opening->Title = $_GET["title"];
        }
        if (!isEmpty($_GET["episode"]) && is_string($_GET["episode"]))
        {
            $opening->Episode = $_GET["episode"];
        }
        if (!isEmpty($_GET["content"]) && is_string($_GET["content"]))
        {
            $opening->Content = $_GET["content"];
        }
        
        if (!isEmpty($_GET["lang"]) && is_string($_GET["lang"]))
        {
            $opening->Language = Opening::StringToLanguage($_GET["lang"]);
        }
    }

    $opening->Title = htmlspecialchars($opening->Title);
    $opening->Episode = htmlspecialchars($opening->Episode);

    $MAX_STARS = 100;
    $TITLE = "$opening->Episode - $opening->Title";
    $DESCRIPTION = "Guarda l'opening personalizzata di Star Wars";

    $protocol = getCurrentProtocol();
    $URL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="<?= $opening->Language->value ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <?php if (!isEmpty($opening->Author)) { ?>
        <meta name="author" content="<?= htmlspecialchars($opening->Author) ?>">
    <?php } ?>
    
    <title>
        <?= $TITLE ?>
    </title>

    <meta name="title" content="<?= $TITLE ?>">
    <meta name="description" content="<?= $DESCRIPTION ?>">
    <meta name="robots" content="index,follow">
    <meta name="google" content="notranslate">
    <link rel="icon" href="./assets/sitelogo.svg">
    <link rel="shortcut icon" href="./assets/sitelogo.svg">

    <!-- Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $URL ?>">
    <meta property="og:title" content="<?= $TITLE ?>">
    <meta property="og:description" content="<?= $DESCRIPTION ?>">
    <meta property="og:image" content="./assets/sitelogo.svg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $URL ?>">
    <meta property="twitter:title" content="<?= $TITLE ?>">
    <meta property="twitter:description" content="<?= $DESCRIPTION ?>">
    <meta property="twitter:image" content="./assets/sitelogo.svg">

    <!-- Main script that handles timing: must be loaded before body -->
    <script src="./assets/view/view.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="./assets/view/view.css" onload="PageStyleLoaded()">
    <link rel="stylesheet" type="text/css" href="./assets/view/animations.css" onload="PageAnimationsLoaded()">
</head>
<body class="wait">
    <!--
        Included an h1 for better search ranking
    -->
    <h1 class="hidden">
        <?= $TITLE ?>
    </h1>

    <!-- Loader shown on start -->
    <div id="loader">
        <div class="loader"></div>
    </div>

    <!-- Most important element -->
    <main id="screen">

        <!-- Intro part -->
        <section class="intro">
            <?= $opening->getIntro() ?>
        </section>

        <!-- Star wars logo svg. Do not copy -->
        <section class="logo">
            <?php include "./assets/img/starwarslogo.svg"; ?>
        </section>

        <!-- Text parts -->
        <article id="board">
            <div id="content">
                <p id="episode">
                    <?= $opening->Episode ?>
                </p>
                <p id="title">
                    <?= $opening->Title ?>
                </p>
                <br>
                <?php foreach ($opening->Paragraphs() as $part) { ?>
                    <p class="paragraph">
                        <?= $part ?>
                    </p>
                <?php } ?>
            </div>
        </article>

        <!-- Who created the opening -->
        <article id="credits" class="hidden">
            <?php if (!isEmpty($opening->Author)) { ?>
                <h2>
                    Creato da utente <?= htmlspecialchars($opening->Author) ?>
                </h2>
            <?php } ?>
            
            <p>
                La musica è di proprietà di LucasFilm e Disney.
                <br>
                Questa simulazione è a solo scopo dimostrativo delle capacità
                del design web; è pubblica e non può essere utilizzata per fini commerciali.
                <br>
                Per maggiori informazioni vai alla <a href="./index.php">Pagina principale</a>
            </p>

            <div class="row">
                <button type="button" id="restart">
                    Riavvia
                </button>
                <button type="button" id="share">
                    Condividi
                </button>
                <?php if (isset($id)) { ?>
                    <button type="button" id="report">
                        Segnala
                    </button>
                <?php } ?>
            </div>
        </article>

        <!-- Stars for the background -->
        <?php include "./parts/stars.php" ?>
    </main>
    <script type="text/javascript">
        'use strict';
        // Restart the page (reload) and share
        const reloadBtn = document.getElementById('restart');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', () => window.location.reload());
        }
        const shareBtn = document.getElementById('share');
        if (!navigator.share || ! navigator.canShare({
            title: document.title,
            text: 'Guarda anche tu la mia intro di Star Wars',
            url: window.location.href
        })) {
            shareBtn.classList.add('hidden');
        } else {
            shareBtn.addEventListener('click', () => {
                navigator.share({
                    title: document.title,
                    text: 'Guarda anche tu la mia intro di Star Wars',
                    url: window.location.href
                })
            });
        }
        const reportBtn = document.getElementById('report');
        if (reportBtn) {
            reportBtn.addEventListener('click', () => {
                const a =document.createElement('a');
                a.href = './report.php?id=' + new URL(window.location.href).searchParams.get('id');
                a.target = '_self';
                document.body.appendChild(a);
                a.click();
            });
        }
    </script>
</body>
</html>