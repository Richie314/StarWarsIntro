<?php

setlocale(LC_TIME, 'ita', 'it_IT.utf8');

error_reporting(E_ERROR | E_PARSE);
function ErrorToErrorPage($errno, $errstr, $errfile, $errline)
{
    $file = urlencode($errfile);
    if (isset($errstr) && isset($errno))
    {
        $str = urlencode($errstr);
        header("Location: ./error.php?err=$str&code=$errno&file=$file");
    } else {
        header("Location: ./error.php?file=$file");
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
    $err = urlencode($ex->getMessage());
    $code = $ex->getCode();
    $file = urlencode($ex->getFile());
    $trace = urlencode($ex->getTraceAsString());
    if (is_int($code))
    {
        header("Location: ./error.php?err=$err&code=$code&file=$file&trace=$trace");
    } else {
        header("Location: ./error.php?err=$err&file=$file&trace=$trace");
    }
    exit;
}
set_exception_handler("ExceptionToErrorPage");