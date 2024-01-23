<?php
    error_reporting(E_ERROR | E_PARSE);
    include_once "./utils/string.php";
    $code = 0;
    if (isset($_GET["code"]) && ctype_digit($_GET["code"]))
    {
        $code = (int)$_GET["code"];
        if ($code >= 400 && $code < 600)
            http_response_code($code);
    }
    $str = "";
    if (isset($_GET["err"]) && is_string($_GET["err"]))
    {
        $str = htmlspecialchars($_GET["err"]);
    }
    if (isset($_GET["file"]) && is_string($_GET["file"]))
    {
        $file = htmlspecialchars($_GET["file"]);
    }
    if (isset($_GET["trace"]) && is_string($_GET["trace"]))
    {
        $trace = htmlspecialchars($_GET["trace"]);
    }
    $TITLE = "Errore";
    if ($code !== 0)
    {
        $TITLE = "$TITLE $code";
    }
    $DESCRIPTION = $str;
    $SHOW_ONLY_HOME_LINK = true;
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <main class="body">
        <?php if ($code === 404) { ?>
            <h1>
                Questa non è la pagina che stai cercando!
            </h1>
            <div class="img no-ctx" style="max-width: 350px">
                <img src="./assets/img/benkenobi.svg" alt="Obi wan kenobi" title="Questa non è la pagina che stai cercando">
            </div>
            <p class="justify" style="max-width: 500px; margin-inline: auto;">
                Prova a controllare l'url digitato. Se stavi cercando un'intro è possibile che questa sia stata cancellata 
                dopo una segnalazione perché conteneva linguaggio scurrile o altro testo non permesso.
                <br><br>
                Non aspettare qui, prova ad <a href="./" class="link" target="_self">andare alla pagina principale</a>
            </p>
            <script>
                /* Automatically reload the page every 15s */
                setTimeout(() => window.location.reload(), 15 * 1000);
            </script>
        <?php } else { ?>
            <h1>
                È avvenuto un errore.
            </h1>
            <?php if ($code !== 0) { ?>
                <h3>
                    Codice errore: <?= $code ?>
                </h3>
            <?php } ?>
            <p class="justify" style="max-width: 500px; margin-inline: auto;">
                Ci scusiamo per il disagio.
                <br><br>
                Non aspettare qui, prova ad <a href="./" class="link" target="_self">andare alla pagina principale</a>
            </p>
            <script>
                /* Obscure params from the url */
                (() => {
                    const url = new URL(location.href);
                    if (url.searchParams.size === 0)
                        return;
                    url.searchParams.delete('file');
                    url.searchParams.delete('trace');
                    url.searchParams.delete('err');
                    history.replaceState({ canonical: window.location.href }, '', url.pathname + url.search);
                })();
            </script>
        <?php } ?>

        <pre><?= trim($str) ?></pre>
        <?php if (isset($file)) { ?>
            <!-- <?= $file ?> -->
        <?php } ?>
        <?php if (isset($trace)) { ?>
            <!-- <?= $trace ?> -->
        <?php } ?>        
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>