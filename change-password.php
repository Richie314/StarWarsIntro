<?php
require_once "./utils/session.php";

if (
    isset($_POST["new_password"]) && is_string($_POST["new_password"]) &&
    isset($_POST["password"]) && is_string($_POST["password"]))
{
    try {
        $user = User::Load($db, $USER_ID);
        if (!$user)
        {
            throw new LogicException("Database e server non sono sincronizzati.");
        }
        if (!$user->checkPassword($_POST["password"]))
        {
            throw new Exception("Password non valida!");
        }
        $user->Password = password_hash($_POST["new_password"], PASSWORD_BCRYPT);
        if (!$user->Update($db))
        {
            throw new Exception("Impossibile aggiornare la password");
        }
        // Everything ok, redirect to home
        RedirectToHome();
    } catch (Exception $e)
    {
        $error_msg = $e->getMessage();
    }
}

$TITLE = "Cambia password";
$DESCRIPTION = "Cambia la tua password";

$HIDE_USERNAME_FILED = true;
$SHOW_NEW_PASSWORD_FIELD = true;
$SHOW_EMAIL_FIELD = false;

$FORM_BUTTON_LABEL = "Cambia password";

?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <main class="body center">
        <?php include "./parts/form.php"; ?>
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>