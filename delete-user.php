<?php
require_once "./utils/session.php";
require_once "./utils/user.php";
if (!$IS_ADMIN) {
    RedirectToError("Azione non permessa");
}
$esit = "Parametro 'username' mancante";
if (isset($_POST["username"]) && is_string($_POST["username"]))
{
    $esit = User::Delete($db, $_POST["username"]) ? "ok" : "errore";
}
header("Content-Type: application/json");
?>
{
    "esit": "<?= $esit ?>"
}
