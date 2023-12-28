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
    } elseif (isset($_GET["original"]) && is_int($_GET["original"]))
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

    <style type="text/css">
        * {
            speak: none;
        }
        body {
            margin: 0;
            background-color: black;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            min-width: 400px;
            min-height: 225px;
        }
        .hidden {
            display: none !important;
        }
        #loader {
            display: none;
        }
            body.wait #loader {
                display: flex;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                justify-content: center;
                align-items: center;
            }
            body.wait *:not(#loader) {
                animation-name: none !important;
                animation-delay: unset !important;
            }
        .loader {
            font-size: 25px;
            width: 1em;
            height: 1em;
            border-radius: 50%;
            position: relative;
            text-indent: -9999em;
            -webkit-animation: Loader 1.1s infinite ease;
            animation: Loader 1.1s infinite ease;
            -webkit-transform: translateZ(0);
            -ms-transform: translateZ(0);
            transform: translateZ(0);
        }
        @-webkit-keyframes Loader {
          0%,
          100% {
            box-shadow: 0em -2.6em 0em 0em #f5f906, 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.5), -1.8em -1.8em 0 0em rgba(245,249,6, 0.7);
          }
          12.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.7), 1.8em -1.8em 0 0em #f5f906, 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.5);
          }
          25% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.5), 1.8em -1.8em 0 0em rgba(245,249,6, 0.7), 2.5em 0em 0 0em #f5f906, 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          37.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.5), 2.5em 0em 0 0em rgba(245,249,6, 0.7), 1.75em 1.75em 0 0em #f5f906, 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          50% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.5), 1.75em 1.75em 0 0em rgba(245,249,6, 0.7), 0em 2.5em 0 0em #f5f906, -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          62.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.5), 0em 2.5em 0 0em rgba(245,249,6, 0.7), -1.8em 1.8em 0 0em #f5f906, -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          75% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.5), -1.8em 1.8em 0 0em rgba(245,249,6, 0.7), -2.6em 0em 0 0em #f5f906, -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          87.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.5), -2.6em 0em 0 0em rgba(245,249,6, 0.7), -1.8em -1.8em 0 0em #f5f906;
          }
        }
        @keyframes Loader {
          0%,
          100% {
            box-shadow: 0em -2.6em 0em 0em #f5f906, 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.5), -1.8em -1.8em 0 0em rgba(245,249,6, 0.7);
          }
          12.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.7), 1.8em -1.8em 0 0em #f5f906, 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.5);
          }
          25% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.5), 1.8em -1.8em 0 0em rgba(245,249,6, 0.7), 2.5em 0em 0 0em #f5f906, 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          37.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.5), 2.5em 0em 0 0em rgba(245,249,6, 0.7), 1.75em 1.75em 0 0em #f5f906, 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          50% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.5), 1.75em 1.75em 0 0em rgba(245,249,6, 0.7), 0em 2.5em 0 0em #f5f906, -1.8em 1.8em 0 0em rgba(245,249,6, 0.2), -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          62.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.5), 0em 2.5em 0 0em rgba(245,249,6, 0.7), -1.8em 1.8em 0 0em #f5f906, -2.6em 0em 0 0em rgba(245,249,6, 0.2), -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          75% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.5), -1.8em 1.8em 0 0em rgba(245,249,6, 0.7), -2.6em 0em 0 0em #f5f906, -1.8em -1.8em 0 0em rgba(245,249,6, 0.2);
          }
          87.5% {
            box-shadow: 0em -2.6em 0em 0em rgba(245,249,6, 0.2), 1.8em -1.8em 0 0em rgba(245,249,6, 0.2), 2.5em 0em 0 0em rgba(245,249,6, 0.2), 1.75em 1.75em 0 0em rgba(245,249,6, 0.2), 0em 2.5em 0 0em rgba(245,249,6, 0.2), -1.8em 1.8em 0 0em rgba(245,249,6, 0.5), -2.6em 0em 0 0em rgba(245,249,6, 0.7), -1.8em -1.8em 0 0em #f5f906;
          }
        }

        #screen {
            padding: 0;
            overflow: hidden;
            aspect-ratio: 16 / 9 !important;
            position: relative;
            width: 100vw;
            height: auto;
            font-size: 1.15vw;
            background-color: transparent;
            --percentageOfHeight: calc(100vw / 1600 * 9);
            margin: calc(50vh - 50 * var(--percentageOfHeight)) 0 auto 0;
        }
        @media screen and (min-aspect-ratio: 16 / 9) {
            #screen {
                width: auto;
                height: 100vh;
                margin: 0 auto 0 auto;
                font-size: 2.3vh;
                --percentageOfHeight: 1vh;
            }
        }
        /*
            Dati per "Tanto tempo fa in una galassia lontana lontana..."
        */
        .intro {
            font-family: Century Gothic, CenturyGothic, AppleGothic, sans-serif;
            position: absolute;
            top: 40%;
            left: 15%;
            width: 70%;
            height: 20%;
            z-index: 1;
            text-align: left;
            animation: intro 5s ease-out 1s;
            -webkit-animation: intro 5s ease-out 1s;
            -moz-animation: intro 5s ease-out 1s;
            -o-animation: intro 5s ease-out 1s;
            color: rgb(75, 213, 238);
            font-weight: 400;
            font-size: 2.6em;
            opacity: 0;
        }

        @keyframes intro {
            0% {
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        @-webkit-keyframes intro {
            0% {
                opacity: 0;
            }

            20% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }
        /*
            Dati per logo STARWARS
        */

        .logo {
            margin: 0;
            padding: 0;
            position: absolute;
            top: 2.5%;
            left: 2.5%;
            width: 95%;
            height: fit-content;
            z-index: 1;
            overflow: hidden;
            animation: logo 13s linear 7s;
            -webkit-animation: logo 13s linear 7s;
            -moz-animation: logo 13s linear 7s;
            -o-animation: logo 13s linear 7s;
            opacity: 0;
            user-select: none;
        }

            .logo svg {
                width: 100%;
                height: auto;
                margin: 0;
                user-select: none;
            }

        @keyframes logo {
            0% {
                transform: scale(1.25);
                transform-origin: center;
                opacity: 1;
            }

            10% {
                transform: scale(1.1);
                transform-origin: center;
                opacity: 1;
            }

            20% {
                transform: scale(0.85);
                transform-origin: center;
                opacity: 1;
            }

            30% {
                transform: scale(0.7);
                transform-origin: center;
                opacity: 1;
            }

            40% {
                transform: scale(0.6);
                transform-origin: center;
                opacity: 1;
            }

            50% {
                transform: scale(0.5);
                transform-origin: center;
                opacity: 1;
            }

            60% {
                transform: scale(0.42);
                transform-origin: center;
                opacity: 1;
            }

            70% {
                transform: scale(0.34);
                transform-origin: center;
                opacity: 1;
            }

            80% {
                transform: scale(0.26);
                transform-origin: center;
                opacity: 1;
            }

            90% {
                transform: scale(0.18);
                transform-origin: center;
                opacity: 0.8;
            }

            100% {
                transform: scale(0.1);
                transform-origin: center;
                opacity: 0;
            }
        }

        @-webkit-keyframes logo {
            0% {
                transform: scale(1.25);
                transform-origin: center;
                opacity: 1;
            }

            10% {
                transform: scale(1.1);
                transform-origin: center;
                opacity: 1;
            }

            20% {
                transform: scale(0.85);
                transform-origin: center;
                opacity: 1;
            }

            30% {
                transform: scale(0.7);
                transform-origin: center;
                opacity: 1;
            }

            40% {
                transform: scale(0.6);
                transform-origin: center;
                opacity: 1;
            }

            50% {
                transform: scale(0.5);
                transform-origin: center;
                opacity: 1;
            }

            60% {
                transform: scale(0.42);
                transform-origin: center;
                opacity: 1;
            }

            70% {
                transform: scale(0.34);
                transform-origin: center;
                opacity: 1;
            }

            80% {
                transform: scale(0.26);
                transform-origin: center;
                opacity: 1;
            }

            90% {
                transform: scale(0.18);
                transform-origin: center;
                opacity: 0.8;
            }

            100% {
                transform: scale(0.1);
                transform-origin: center;
                opacity: 0;
            }
        }

        /*
            Dati per il corpo del testo
        */
        #board {
            color: #E2C64E;
            font-family: Century Gothic, CenturyGothic, AppleGothic, sans-serif;
            transform: perspective(calc(var(--percentageOfHeight) * 100)) rotateX(35deg);
            transform-origin: 50% 100%;
            position: absolute;
            margin: auto 2.5% 0 2.5%;
            font-weight: bold;
            overflow: hidden;
            height: calc(350 * var(--percentageOfHeight));
            width: 95%;
            bottom: 0;
            left: 0;
            user-select: none;
        }

        .break {
            font-size: 3.2em;
        }

        #board::after {
            position: absolute;
            content: ' ';
            bottom: 60%;
            left: 0;
            right: 0;
            top: 0;
        }

        #content {
            color: inherit;
            animation: scroll 140s linear 18s;
            -webkit-animation: scroll 140s linear 18s;
            -moz-animation: scroll 140s linear 18s;
            -o-animation: scroll 140s linear 18s;
            position: absolute;
            top: 100%;
            width: 100%;
        }

        #episode, #title {
            color: inherit;
            text-align: center;
            font-size: 6.2em;
            overflow-x: visible;
            overflow-y: hidden;
            word-break: keep-all;
        }
        #title {
            text-transform: uppercase;
        }
        .paragraph {
            color: inherit;
            font-size: 5.333em;
            /*white-space: pre-line;*/
            word-break: keep-all;
            text-align: justify;
            user-select: none;
        }
            .paragraph br {
                content: "";
                margin: 2em;
                display: block;
                font-size: 24%;
            }
        @keyframes scroll {
            0% {
                top: 100%;
            }
            100% {
                top: -200%;
            }
        }

        @-webkit-keyframes scroll {
            0% {
                top: 100%;
            }

            100% {
                top: -200%;
            }
        }
        #credits {
            position: absolute;
            top: 0;
            left: 0;
            display: none;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            text-align: center;
            word-break:break-word;
            user-select: text;
            background-color: transparent;
            z-index: 50;
        }
        .star {
            position: absolute;
        }
    </style>
</head>
<body>
    <h1 class="hidden">
        <?= $TITLE ?>
    </h1>
    <div id="loader">
        <div class="loader"></div>
    </div>
    <div id="screen">
        <section class="intro">
            <?= $opening->getIntro() ?>
        </section>
        <section class="logo">
            <?php include "./assets/img/starwarslogo.svg"; ?>
        </section>
        <div id="board">
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
        </div>
        <div id="credits">
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
        </div>
        <?php include "./parts/stars.php" ?>
    </div>
    <script type="text/javascript">
        'use strict';
        document.oncontextmenu = evt => evt.preventDefault();
        const Now = () => performance.now();
        const offSetAudio = 7500;
        var start = Now();
        const CreateAudio = () => {
            const audio = new Audio('./assets/intro.mp3');
            audio.autoplay = false;
            audio.muted = false;
            return audio;
        };
        const audio = CreateAudio();
        var loaded = false;//if the audio is loaded and the page running
        var clicked = false;//if the user has interacted
        var started = false;//if audio is started or scheduled
        //Page starts to animate
        const StartPage = () => {
            start = Now();
            document.body.classList.remove('wait');
        }

        // The audio is loaded
        const StartAudio = () => {
            function StartAudioRaw() {
                const t = Now() - start - offSetAudio;
                if (t >= 0) {
                    if (t < audio.duration * 1000)
                        audio.currentTime = t / 1000;
                    if (audio.paused)
                        audio.play();
                } else {
                    setTimeout(() => {
                        StartAudioRaw();
                    }, -t);
                }
                return t;
            }
            if (!started) {
                StartAudioRaw();
            }
            started = true;
        }
        audio.addEventListener('loadeddata', () => {
            loaded = true;
            StartPage();
            if (clicked) {//The user has already interacted
                StartAudio();
            }
        });
        document.addEventListener('click', () => {
            clicked = true;
            if (loaded) {//The user interacts, if the page is already running it starts from the proper time
                StartAudio();
            }
        });
    </script>
</body>
</html>