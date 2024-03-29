<?php
$DO_NOT_CHECK_LOGIN = true;
require_once "./utils/session.php";
if (IsLoggedIn())
{
    RedirectToHome();
}

if (
    isset($_POST["username"]) && is_string($_POST["username"]) &&
    isset($_POST["email"]) && is_string($_POST["email"]))
{
    try {
        $user = User::Load($db, $_POST["username"]);
        if (!isset($user) || isEmpty($user->Email) ||
            $user->Email !== $_POST["email"] || 
            !$user->ResetPassword($db))
        {  
            throw new Exception("Qualcosa non ha funzionato.");
        }
        $email = htmlspecialchars($user->Email);
        $error_msg = "Email inviata a <a href=\"mailto:$email\" class=\"link\">$email</a>";
    } catch (Exception $e)
    {
        $error_msg = $e->getMessage();
    }
}

$TITLE = "Resetta password";
$DESCRIPTION = "Resetta la tua password";
$HIDE_PASSWORD_FIELD = true;
$SHOW_EMAIL_FIELD = true;
$FORM_BUTTON_LABEL = "Inviami nuova password";

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