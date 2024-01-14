<?php

require_once "./utils/session.php";
require_once "./utils/opening.php";

if (!isset($_POST["action"]) || !is_string($_POST["action"]))
{
    throw new Exception("Richiesta mal formata", 400);
}
$esit = "";

switch ($_POST["action"])
{
    case 'delete-user':
    {
        if (!$IS_ADMIN)
        {
            throw new Exception("Azione non permessa", 401);
        }
        if (!isset($_POST["username"]) || !is_string($_POST["username"]))
        {
            throw new Exception("Parametro 'username' mancante o malformato", 400);
        }
        $esit = User::Delete($db, $_POST["username"]) ? "ok" : "errore";
        break;
    }
    case 'delete-intro':
    {
        if (!isset($_POST["id"]) || !ctype_digit($_POST["id"]))
        {
            throw new Exception("Parametro 'id' mancante o malformato", 400);
        }
        $esit = Opening::Delete($db, (int)$_POST["id"], $USER_ID, $IS_ADMIN) ? "ok" : "error";
        break;
    }
    case 'set-problematic-report':
    {
        if (!$IS_ADMIN)
        {
            throw new Exception("Azione non permessa", 401);
        }
        if (!isset($_POST["id"]) || !ctype_digit($_POST["id"]))
        {
            throw new Exception("Parametro 'id' mancante o malformato", 400);
        }
        $esit = Report::SetProblematic($db, (int)$_POST["id"]) ? "ok" : "error";
        break;
    }
    case 'set-viewed-report':
    {
        if (!$IS_ADMIN)
        {
            throw new Exception("Azione non permessa", 401);
        }
        if (!isset($_POST["id"]) || !ctype_digit($_POST["id"]))
        {
            throw new Exception("Parametro 'id' mancante o malformato", 400);
        }
        $esit = Report::SetViewed($db, (int)$_POST["id"]) ? "ok" : "error";
        break;
    }
    default:
    {
        throw new Exception("Azione richiesta sconosciuta", 400);
    }
}
header("Content-Type: application/json");
?>
{
    "esit": "<?= $esit ?>"
}