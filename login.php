<?php
$DO_NOT_CHECK_LOGIN = true;
require_once "./utils/session.php";
if (IsLoggedIn())
{
    RedirectToHome();
}

if (
    isset($_POST["username"]) && is_string($_POST["username"]) &&
    isset($_POST["password"]) && is_string($_POST["password"]))
{
    try {
        $user = User::Load($db, $_POST["username"]);
        if (isset($user) && $user->checkPassword($_POST["password"]))
        {
            // Login ok :)
    
            // Create a row in the db
            $user->Log($db);
    
            // Update the session
            LogIn($user);
    
            // Flow ends as "exit" is called
        }
        // Login not ok :/
        throw new LogicException("Username o password non valide.");
    } catch (Exception $e)
    {
        $error_msg = $e->getMessage();
    }
}

$TITLE = "Login";
$DESCRIPTION = "Fai il login";
$SHOW_FORGOT_PASSWORD_LINK = true;
$FORM_BUTTON_LABEL = "Login";

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