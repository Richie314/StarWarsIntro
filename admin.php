<?php
    require_once "./utils/session.php";
    include_once "./utils/user.php";
    if (!$IS_ADMIN)
    {
        RedirectToHome();
    }
    $TITLE = "Admin";
    $logins = Login::RecentLogs($db);
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <div class="body">
        <h1>
            Login recenti
        </h1>
        <ul>
            <?php foreach ($logins as $login) { ?>
                <li>
                    <a href="" class="link"><?= $login->User->ID ?></a>@<a href="<?= $login->Ip ?>" class="link" traget="_blank"><?= $login->Ip ?></a>
                    <br>
                    il <?= $login->When->format('Y-m-d alle H:i:s') ?>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>