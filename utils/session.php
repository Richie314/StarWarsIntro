<?php
include_once "./utils/no-warning.php";
include_once "./utils/string.php";
include_once "./utils/user.php";
include_once "./utils/db.php";
session_start(); // Session automatic handling done in .htaccess

function LogOut()
{
    session_unset();
    if (session_destroy())
    {
        header("Location: ./index.php");
        exit;
    }
    RedirectToError('Impossibile effettuare il logout!');
}
function LogIn(User $user)
{
    $_SESSION['user_id'] = $user->ID;
    $_SESSION['admin'] = $user->Admin;
    
    RedirectToHome();
}

function IsLoggedIn()
{
    $user_id = $_SESSION['user_id'];
    return !isEmpty($user_id);
}

function RedirectToLogin()
{
    header("Location: ./login.php");
    exit;
}

function RedirectToError($err = NULL)
{
    if (isEmpty($err))
        header("Location: ./error.php");
    else
        header("Location: ./error.php?err=" . urlencode($err));
    exit;
}

function RedirectToHome()
{
    header('Location: ./me.php');
    exit;
}
if (!IsLoggedIn() && !isset($DO_NOT_CHECK_LOGIN))
{
    RedirectToLogin();
}

$USER_ID = $_SESSION['user_id'];
$IS_ADMIN = $_SESSION['admin'];