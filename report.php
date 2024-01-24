<?php
    $DO_NOT_CHECK_LOGIN = true;
    require_once "./utils/session.php";
    include_once "./utils/opening.php";
    if (isset($_GET["id"]) && ctype_digit($_GET["id"]))
    {
        $id = (int)$_GET["id"];
    }
    if (isset($_POST["id"]) && ctype_digit($_POST["id"]))
    {
        $id = (int)$_POST["id"];
    }
    if (!isset($id))
    {
        RedirectToHome();
    }
    $text = "";
    if (!isEmpty($_POST["text"]) && is_string($_POST["text"])) {
        $text = $_POST["text"];
        if (Report::MakeNew($db, $id, $text))
        {
            header("Location: ./report.php?id=$id&esit=success");
            exit;
        }
        $error_mess = "Qualcosa è andato storto";
    }
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php" ?>
<body>
    <?php include "./parts/nav.php" ?>
    <main class="body">
        <h1>
            Segnala testo inappropriato
        </h1>
        <h3>
            Questo form è anonimo
        </h3>
        <form class="intro-form" method="post">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label for="text" class="center" style="font-size: larger">
                Spiega cosa c'è che non va:
            </label>
            <br>
            <textarea style="width: calc(100% - 2em); margin-inline-start: auto; margin-inline-end: auto;"
                name="text" id="text" 
                cols="15" rows="17" required
                placeholder="Esempio: linguaggio scurrile"
                spellcheck="true" tabindex="1"><?= htmlentities($text) ?></textarea>
            <br>
            <?php if (isset($error_mess)) 
                echo "<p>" . htmlspecialchars($error_mess) . "</p>";
            ?>
            <div class="grid" style="grid-template-columns: 1fr 1fr">
                <button type="reset" title="Cancella" id="clear" tabindex="2"></button>
                <button type="submit" id="submit" tabindex="3"
                    title="Invia"></button>
            </div>
        </form>  
        <script>
            (() => {
                const url = new URL(location.href);
                if (url.searchParams.has('esit', 'success'))
                {
                    alert('Segnalazione effettuata con successo!');
                    window.location.replace('./index.php');
                }
            })();
        </script>
    </main>
    <?php include "./parts/cookie.php" ?>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php" ?>
</body>
</html>
