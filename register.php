<?php
$DO_NOT_CHECK_LOGIN = true;
require_once "./utils/session.php";
if (IsLoggedIn())
{
    RedirectToHome();
}

if (
    isset($_POST["username"]) && is_string($_POST["username"]) &&
    isset($_POST["email"])    && is_string($_POST["email"]) &&
    isset($_POST["password"]) && is_string($_POST["password"]))
{
    try {
        $id = $_POST["username"];
        if (!User::Create($db, $id, $_POST["password"], $_POST["email"]))
        {
            throw new Exception("Impossibile creare l'utente.\nUna possibilità è che ne esista già uno con lo username \"$id\".");
        }
        $user = User::Load($db, $id);
        if (!isset($user))
        {
            throw new LogicException("DB out of sync!");
        }

        // Create a row in the db
        $user->Log($db);

        // Update the session
        LogIn($user);

        // Flow ends as "exit" is called
    } catch (Exception $e) {
        // An error happened
        $error_msg = "Impossibile creare l'utente.\nUna possibilità è che ne esista già uno con lo username \"$id\".";
    }
}

$TITLE = "Registrati";
$DESCRIPTION = "Crea un account";
$SHOW_EMAIL_FIELD = true;
$FORM_BUTTON_LABEL = "Registrati";

?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <div class="body center">
        <?php include "./parts/form.php"; ?>
    </div>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>
