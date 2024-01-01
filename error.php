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
function IsLoggedIn()
{
    return false;
}
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <div class="body">
        <?php if ($code === 404) { ?>
            <h1>
                Questa non &egrave; la pagina che stai cercando!
            </h1>
            <div class="img no-ctx" style="max-width: 350px">
                <img src="./assets/img/benkenobi.svg" alt="Obi wan kenobi" title="Questa non è la pagina che stai cercando">
            </div>

        <?php } else { ?>
            <h1>
                &Egrave; avvenuto un errore.
            </h1>
            <p class="justify" style="max-width: 500px; margin-inline: auto;">
                Ci scusiamo per il disagio.
            </p>
        <?php } ?>
        <pre><?= trim($str) ?></pre>
        <?php if (isset($file)) { ?>
            <!-- <?= $file ?> -->
        <?php } ?>
        <?php if (isset($trace)) { ?>
            <!-- <?= $trace ?> -->
        <?php } ?>
        <script type="text/javascript">
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
    </div>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>