<?php
require_once "./utils/session.php";
require_once "./utils/opening.php";
if (!isset($_POST["id"]))
{
    RedirectToError("Missing parameter `id`");
}
$esit = Opening::Delete($db, (int)$_POST["id"], $USER_ID, $IS_ADMIN) ? "ok" : "error";
header("Content-Type: application/json");
?>
{
    "esit": "<?= $esit ?>"
}