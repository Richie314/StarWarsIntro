<?php
    include_once "../utils/link.php";
    $links = array(
        new Link("/index.php", "", "Star Wars Intro"),
    );
    if (IsLoggedIn())
    {
        $links[] = new Link("/create.php", "", "Crea intro");
        $links[] = new Link("/me.php", "", $USER_ID);
        $links[] = new Link("/logout.php", "", "Logout");
    } else {
        $links[] = new Link("/register.php", "", "Registrati");
        $links[] = new Link("/login.php", "", "Login");
    }

?>

<nav class="h-menu">
    <?php foreach ($links as $link) { ?>
    <a href="<?= $link->Url ?>" target="_self" title=" Vai a <?= $link->Text ?>">
        <div class="icon">
            <img src="<?= $link->Img ?>" alt="<?= $link->Text ?>">
        </div>
        <div class="text">
            <span>
                <?= $link->Text ?>
            </span>
        </div>
    </a>
    <?php } ?>
</nav>