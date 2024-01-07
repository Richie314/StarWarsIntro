<?php
    include_once "./utils/link.php";
    $links = array(
        new Link("./index.php", "./assets/img/home.svg", "Home"),
    );
    
    if (IsLoggedIn())
    {
        $links[] = new Link("./create.php",     "./assets/img/create.svg",  "Crea");
        $links[] = new Link("./me.php",         "./assets/img/user.svg",    $USER_ID);
        if (isset($IS_ADMIN) && $IS_ADMIN)
        {
            $links[] = new Link("./admin.php",     "./assets/img/admin.svg",  "Admin");
        }
        $links[] = new Link("./logout.php",     "./assets/img/logout.svg",  "Logout");
    } else {
        $links[] = new Link("./register.php",   "./assets/img/signup.svg",  "Registrati");
        $links[] = new Link("./login.php",      "./assets/img/login.svg",   "Login");
    }

?>

<nav class="h-menu items-<?= sizeof($links) ?>">
    <div class="hamburger">
        <input type="checkbox" id="hide-show-menu">
        <label for="hide-show-menu">
            <img src="./assets/img/hamburger.svg" alt="Hamburger" title="Mostra il menù" class="hamburger">
            <img src="./assets/img/close.svg" alt="Chiudi" title="Chiudi il menù" class="close">
        </label>
    </div>
    <?php foreach ($links as $link) { ?>
        <a href="<?= $link->Url ?>" target="_self" title=" Vai a <?= $link->Text ?>">
            <div class="icon">
                <img src="<?= $link->Img ?>" alt="<?= $link->Text ?>" loading="eager">
            </div>
            <div class="text">
                <span>
                    <?= $link->Text ?>
                </span>
            </div>
        </a>
    <?php } ?>
</nav>
