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
            <ul id="login-list" class="check-empty">
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
                        &nbsp;
                        il <?= $login->When->format('d/m/Y H:i:s') ?>
                    </li>
                <?php } ?>
                <li class="show-if-list-empty">
                    C'è qualcosa di strano: non sono stati trovati login. <br>
                    Qualcosa potrebbe essere andato storto nel databse
                </li>
            </ul>
        </details>

        <details>
            <summary>
                <h1>
                    Segnalazioni
                </h1>
            </summary>
            <ul id="report-list" class="check-empty">
                <?php foreach ($reports as $report) { ?>
                    <li id="report-<?= $report->ID ?>">
                        <details>
                            <summary class="no-marker">
                                Report N°<?= $report->ID ?>
                            </summary>
                            <div>
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
                                <p class="justify">
                                    <?= htmlspecialchars($report->Text) ?>
                                </p>
                            </div>
                        </details>
                    </li>
                <?php } ?>
                <li class="show-if-list-empty">
                    Nessuna segnalazione da vedere. <br>
                    Per controllare se ce ne sono di nuove
                    <a href="javascript:window.location.reload()" class="link">ricarica la pagina</a>
                </li>
            </ul>
        </details>

        <details>
            <summary>
                <h1>
                    Utenti inattivi
                </h1>
            </summary>
            <ul id="inactive-list" class="check-empty">
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
                <li class="show-if-list-empty">
                    Nessun utente inattivo
                </li>
            </ul>
        </details>
        
        <hr>
        <details>
            <summary>
                <h2>
                    Come cancellare le intro dannose
                </h2>
            </summary>
            <div>
                <p class="justify">
                    Non è necessario cancellare le intro dannose a mano, e nemmeno
                    gli utenti che le hanno create.
                    <br>
                    Una volta che le segnalazioni vengono confermate da un admin,
                    il database rimuoverà in automatico il contenuto inappropriato entro 10 gg.
                    <br>
                    Gli utenti che sono stati riportati più volte negli ultimi 10 gg
                    (almeno 3 volte in generale o per almeno due risorse diverse) 
                    vengono invece cancellati con la frequenza di una volta al giorno.
                </p>
            </div>
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