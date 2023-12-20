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
    $error_msg = "Username o password errate.";
}