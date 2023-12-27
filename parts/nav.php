<?php
    include_once "./utils/link.php";
    $links = array(
        new Link("./index.php", "", "Home"),
    );
    
    if (IsLoggedIn())
    {
        $links[] = new Link("./create.php", "./assets/img/create.svg", "Crea intro");
        $links[] = new Link("./me.php", "./assets/img/user.svg", $USER_ID);
        $links[] = new Link("./logout.php", "./assets/img/logout.svg", "Logout");
    } else {
        $links[] = new Link("./register.php", "./assets/img/signup.svg", "Registrati");
        $links[] = new Link("./login.php", "./assets/img/login.svg", "Login");
    }

?>

<nav class="h-menu items-<?= sizeof($links) ?>">
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