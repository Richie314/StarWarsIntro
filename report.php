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
    if (!isEmpty($_POST["text"]) && is_string($_POST["text"])) {
        if (!Report::MakeNew($db, $id, $_POST["text"]))
        {

        }
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
        <p>
            Questo form &egrave; anonimo
        </p>
        <form method="post">

        </form>     
    </main>
    <?php include "./parts/cookie.php" ?>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php" ?>
</body>
</html>
