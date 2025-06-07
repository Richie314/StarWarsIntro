<?php
    require_once "./utils/session.php";
    include_once "./utils/user.php";
    if (!$IS_ADMIN)
    {
        RedirectToHome();
    }
    $TITLE = "Admin";
    $logins = Login::RecentLogs($db);
    $inactive = User::InactiveUsers($db);
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <main class="body">
        <h1>
            Login recenti
        </h1>
        <ul id="login-list">
            <?php foreach ($logins as $login) { ?>
                <li>
                    <?php if (!isEmpty($login->User->Email)) { ?>
                        <a href="mailto:<?= htmlspecialchars($login->User->Email) ?>" class="link">
                            <?= htmlspecialchars($login->User->ID) ?>
                        </a>
                    <?php } else { ?>
                        <?= htmlspecialchars($login->User->ID) ?>
                    <?php } ?>
                    @
                    <a href="http://<?= $login->Ip ?>" class="link" traget="_blank">
                        <?= $login->Ip ?>
                    </a>
                    &nbsp;
                    il <?= $login->When->format('d/m/Y H:i:s') ?>
                </li>
            <?php } ?>
        </ul>
        <h1>
            Utenti inattivi
        </h1>
        <ul id="inactive-list">
            <?php foreach ($inactive as $user) { ?>
                <li id="user-<?= $user->SafeID() ?>">
                    <?= $user->SafeID() ?>
                    <?php if (!isEmpty($user->Email)) { ?>
                        &rarr;
                        <a href="mailto:<?= htmlspecialchars($user->Email) ?>" 
                            class="link" title="Invia un'email">
                            <?= htmlspecialchars($user->Email) ?>
                        </a>
                    <?php } ?>
                    &rarr;
                    <a href="javascript:Delete('<?= $user->SafeID() ?>')" class="link">
                        Cancella <i class="icon delete"></i>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <script>
            async function Delete(user)
            {
                if (!user)
                    return;
                const res = await post('./delete-user.php', {
                    'username': user
                });
                if (!('esit' in res))
                {
                    console.log(`Delete of #${user}: Unknown esit`);
                    return;
                }
                console.log(`Delete of #${user}: ${res.esit}`);
                if (res.esit !== 'ok')
                {
                    return;
                }
                const li = document.getElementById('user-' + user);
                if (li) {
                    li.classList.add('fade-out');
                    setTimeout(() => {
                        li.parentElement.removeChild(li);
                    }, 1000);
                }
            }
        </script>
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>
</body>
</html>