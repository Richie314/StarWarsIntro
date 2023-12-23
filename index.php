<?php
    $DO_NOT_CHECK_LOGIN = true;
    require_once "./utils/session.php";
    if (IsLoggedIn())
    {
        RedirectToHome();
    }
?>
<!DOCTYPE html>
<html>
<?php include "./parts/head.php" ?>
<body>
    <?php include "./parts/nav.php" ?>
    <div class="body">
        
    </div>
    <?php include "./parts/cookie.php" ?>
    <?php include "./parts/footer.php" ?>
</body>
</html>