<?php
    include_once "./utils/no-warning.php";
    include_once "./utils/opening.php";
    require_once "./utils/db.php";
    $opening = new Opening(
        0, 
        "Titolo qui",
        "Nome episodio qui",
        "Contenuto qui",
        "it",
        null, new DateTime(), null);
    if (isset($_GET["id"]) && ctype_digit($_GET["id"]))
    {
        $opening = Opening::Load($db, (int)$_GET["id"]);
        if (!isset($opening))
        {
            http_response_code(404);
            throw new Exception("Risorsa non trovata");
        }
    } elseif (isset($_GET["original"]) && ctype_digit($_GET["original"]))
    {
        $original = (int)$_GET["original"];
        if ($original < 1 || $original > 9)
        {
            http_response_code(404);
            throw new Exception("Risorsa non trovata");
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

    if (isset($_SERVER['HTTPS']) &&
        ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
        $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        
            $protocol = 'https://';
    } else {
      $protocol = 'http://';
    }
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
    <meta name="robots" content="noindex,nofollow">
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

    <!-- Styling is inline to reduce load latency to zero -->
    <style type="text/css">
        <?php include "./assets/view/view.css" ?>
        
        <?php include "./assets/view/animations.css" ?>
    </style>
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
        <article id="credits">
            <?php if (!isEmpty($opening->Author)) { ?>
                <h3>
                    Creato da utente
                </h3>
                <h2>
                    <?= htmlspecialchars($opening->Author) ?>
                </h2>
            <?php } ?>
            <p>
                La musica &egrave; di propriet&agrave; di LucasFilm e Disney, questa
                simulazione &egrave; a solo scopo dimostrativo delle capacit&agrave;
                del design web, e non pu&ograve; essere utilizzata a fini commerciali.
            </p>
        </article>

        <!-- Stars for the background -->
        <?php include "./parts/stars.php" ?>
    </main>
    <script type="text/javascript">
        'use strict';
        // Prevent contextmenu from getting in the way
        document.oncontextmenu = evt => evt.preventDefault();

        // Shorthand
        const Now = () => performance.now();

        // Delay for audio start after the start of the animations
        const AUDIO_START_DELAY = 7300;

        // Delay for credits to be shown
        const CREDITS_SHOW_DELAY = 70 * 1000;

        // When animations start
        var start = 0;

        // Generate the audio as we want it
        function CreateAudio() {
            const audio = new Audio('./assets/intro.mp3');
            audio.autoplay = false;
            audio.muted = false;
            return audio;
        };
        const audio = CreateAudio();
        
        // Function that starts the animations
        function StartPage() {
            start = Now();
            document.body.classList.remove('wait'); // Start animations
            StartAudio();
            const credits = document.getElementById('credits');
            if (credits) {
                setTimeout(() => {
                    credits.style.opacity = 1;
                    credits.style.userSelect = 'text';
                }, CREDITS_SHOW_DELAY);
            }
        }

        // Starts the audio.
        // automatically detects offsets
        function StartAudio() {
            if (!audio.paused)
                return; // Do nothing if the audio is playing

            // Time passed since animation start - the offset: put as currentTime of the audio
            const deltaT = Now() - start - AUDIO_START_DELAY;
            if (deltaT < 0) {
                // Schedule the start
                setTimeout(StartAudio, -deltaT);
                return;
            }
            // Audio should already been playing. Sync it to the desired currentTime

            // If the audio should have ended don't start it
            if (deltaT >= audio.duration * 1000)
            {
                return;
            }
            
            // Set the current time and play it
            audio.currentTime = deltaT / 1000;
            if (!audio.paused) {
                // Already playing
                return;
            }
            audio.play().then(
                () => console.log('Audio started')).catch(
                () => console.warn('Audio will start when the user clicks on the page'));
        }

        audio.addEventListener('loadeddata', () => StartPage());
        document.addEventListener('click', () => StartAudio());
    </script>
</body>
</html>