<?php
    require_once "./utils/session.php";
    include_once "./utils/opening.php";
    if (!$IS_ADMIN)
    {
        RedirectToHome();
    }
    $TITLE = "Admin";
    $logins = Login::RecentLogs($db);
    $inactive = User::InactiveUsers($db);
    $reports = Report::LoadUnViewed($db);
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <main class="body">
        <details>
            <summary>
                <h1>
                    Login recenti
                </h1>
            </summary>
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
                        <a href="http://<?= $login->Ip ?>" class="link" target="_blank">
                            <?= $login->Ip ?>
                        </a>
                        <br>
                        il <?= $login->When->format('d/m/Y H:i:s') ?>
                    </li>
                <?php } ?>
            </ul>
        </details>

        <details>
            <summary>
                <h1>
                    Segnalazioni
                </h1>
            </summary>
            <ul>
                <?php foreach ($reports as $report) { ?>
                    <li id="report-<?= $report->ID ?>">
                        <details>
                            <summary class="no-marker">
                                Report N°<?= $report->ID ?>
                            </summary>
                            <p>
                                <?php if (!$report->IsProblematic) { ?>
                                    <a href="javascript:SetProblematic(<?= $report->ID ?>)" class="link">
                                        Segna come problematica
                                    </a>
                                <?php } ?>
                                &nbsp;
                                <?php if (!$report->WasViewedByAdmin) { ?>
                                    <a href="javascript:SetViewed(<?= $report->ID ?>)" class="link">
                                        Ignora
                                    </a>
                                <?php } ?>
                                &nbsp;
                                <a href="./view.php?id=<?= $report->Opening ?>" target="_blank" class="link">
                                    Ispeziona #<?= $report->Opening ?>
                                </a>
                                <hr>
                                <?= htmlspecialchars($report->Text) ?>
                            </p>
                        </details>
                    </li>
                <?php } ?>
            </ul>
        </details>

        <details>
            <summary>
                <h1>
                    Utenti inattivi
                </h1>
            </summary>
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
                        <a href="javascript:DeleteUser('<?= $user->SafeID() ?>')" class="link">
                            Cancella <i class="icon delete"></i>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </details>
        
        
        
        <script>
            async function DeleteUser(user)
            {
                if (!user)
                    return;
                const esit = await ajax({
                    'username': user,
                    'action': 'delete-user'
                });
                console.log(`Delete of #${user}: ${esit}`);
                if (esit !== 'ok')
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
            async function SetProblematic(id)
            {
                if (!id)
                    return;
                const esit = await ajax({
                    'action': 'set-problematic-report',
                    'id': id
                });
                console.log(`Set problematic of #${id}: ${esit}`);
                if (esit !== 'ok')
                {
                    return;
                }
                const li = document.getElementById('report-' + id);
                if (li) {
                    li.classList.add('fade-out');
                    setTimeout(() => {
                        li.parentElement.removeChild(li);
                    }, 1000);
                }
            }
            async function SetViewed(id)
            {
                if (!id)
                    return;
                const esit = await ajax({
                    'action': 'set-viewed-report',
                    'id': id
                });
                console.log(`Set viewed of #${id}: ${esit}`);
                if (esit !== 'ok')
                {
                    return;
                }
                const li = document.getElementById('report-' + id);
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