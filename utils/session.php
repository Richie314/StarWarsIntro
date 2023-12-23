<?php
include_once "./string.php";
include_once "./user.php";
include_once "./db.php";
session_start();

function LogOut()
{
    $unset = session_unset();
    $destroyed = session_destroy();
    if ($unset && $destroyed)
    {
        header("Location: /index.php");
        exit;
    }
    RedirectToError('Impossibile effettuare il logout!');
}
function LogIn(User $user)
{
    $_SESSION['id'] = $user->ID;
    $_SESSION['admin'] = $user->Admin;
    
    RedirectToHome();
}

function IsLoggedIn()
{
    $user_id = $_SESSION['id'];
    return !isEmpty($user_id);
}

function RedirectToLogin()
{
    header("Location: /login.php");
    exit;
}

function RedirectToError(string $err = "")
{
    if (isEmpty($err))
        header("Location: /error.php?err=");
    else
        header("Location: /error.php?err=" . urlencode($err));
    exit;
}

function RedirectToHome()
{
    header('Location: /me.php');
    exit;
}

if (!IsLoggedIn() && !isset($DO_NOT_CHECK_LOGIN))
{
    RedirectToLogin();
}

$USER_ID = $_SESSION['id'];