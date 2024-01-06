<?php
    $DO_NOT_CHECK_LOGIN = true;
    require_once "./utils/session.php";
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php" ?>
<body>
    <?php include "./parts/nav.php" ?>
    <main class="body">
        <h1>
            Creatore Intro di Star Wars
        </h1>
        <div class="img no-ctx">
            <?php include "./assets/sitelogo.svg"; ?>
        </div>
        <h3>
            Hai mai voluto ricreare le intro di Star Wars?
        </h3>
        <h3>
            Hai mai voluto crearne di tue e condividerle con i tuoi amici?
        </h3>
        <p class="justify" style="max-width: 500px; padding: 1em; margin-inline: auto;">
            Questo &egrave; il sito che fa al caso tuo!<br>
            <?php if (IsLoggedIn()) { ?>
                <a href="./create.php" target="_self" class="link">
                    <strong>Crea una nuova intro</strong>
                </a>.
            <?php } else { ?>
                Per iniziare, 
                <a href="./register.php" target="_self" class="link">
                    <strong>Crea un account</strong>
                </a>.
            <?php } ?>
        </p>
        <h3>
            Come funziona il sito?
        </h3>
        <p class="justify">
            La pagina <a href="./create.php" target="_self" class="link">create.php</a> permette 
            di creare/modificare le intro, che possono essere visualizzate all'endpoint
            <a href="./view.php?original=<?= random_int(1, 9)?>" target="_self" class="link">view.php</a>.
            <br>
            I titoli delle pagine private sono, talvolta, visualizzati col font <em>Aurebesh</em>
            all'interno delle stesse. Andando col cursore sopra tali titoli saranno convertiti in un font pi&ugrave;
            comune.
        </p>
        <h3>
            Perch&eacute; questo sito?
        </h3>
        <p class="justify">
            Il sito &egrave; stato realizzato da Riccardo Ciucci, come progetto per l'esame
            di Progettazione Web del corso di laurea triennale in Ingegneria Informatica
            dell'<a href="https://www.unipi.it/" class="link">Universit&agrave; di Pisa</a>.<br>
            Professore del corso: Vecchio Alessio.<br>
            Anno Accademico 2023-24.<br>
            Non vi sono fini di lucro n&eacute; infrangimenti di copyright intesi.
            Le icone sono state ottenute da <a href="https://svgrepo.com" class="link">svgrepo.com</a>
        </p>
    </main>
    <?php include "./parts/cookie.php" ?>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php" ?>
</body>
</html>