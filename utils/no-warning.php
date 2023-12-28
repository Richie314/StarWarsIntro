<?php

error_reporting(E_ERROR | E_PARSE);
function ErrorToErrorPage($errno, $errstr, $errfile, $errline)
{
    if (isset($errstr) && isset($errno))
    {
        $str = urldecode($errstr);
        header("Location: ./error.php?err=$str&code=$errno");
    } else {
        header("Location: ./error.php");
    }
    exit;
}
set_error_handler("ErrorToErrorPage", E_STRICT);
function ExceptionToErrorPage(Exception $ex)
{
    if (!isset($ex))
    {   
        header("Location: ./error.php");
        exit;
    }
    $err = urldecode($ex->getMessage());
    $code = $ex->getCode();
    if (is_int($code))
    {
        header("Location: ./error.php?err=$err&code=$code");
    } else {
        header("Location: ./error.php?err=$err");
    }
    exit;
}
set_exception_handler("ExceptionToErrorPage");