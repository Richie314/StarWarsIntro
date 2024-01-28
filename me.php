<?php
    require_once "./utils/session.php";
    include_once "./utils/opening.php";
    $intros = Opening::LoadBriefOfUser($db, $USER_ID);
    $TITLE = "Le mie intro";
?>
<!DOCTYPE html>
<html lang="it">
<?php include "./parts/head.php"; ?>
<body>
    <?php include "./parts/nav.php"; ?>
    <main class="body">
        <div>
            <h2>
                Link utili:
            </h2>
            <ul>
                <li>
                    <a href="./change-password.php" class="link" target="_self" title="Cambia la tua password">
                        Cambia password
                    </a>
                </li>
                <?php if ($IS_ADMIN) { ?>
                    <li>
                        <a href="./admin.php#login-list" class="link" target="_self" title="Vai alla lista">
                            Lista dei login di tutti gli utenti
                        </a>
                    </li>
                    <li>
                        <a href="./admin.php#inactive-list" class="link" target="_self" title="Vai alla lista">
                            Lista utenti inattivi
                        </a>
                    </li>
                    <li>
                        <a href="./admin.php#report-list" class="link" target="_self" title="Vai alla lista">
                            Lista segnalazioni da vedere
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <h1 class="aurebesh" data-content="Le mie intro">
            <span class="hidden">
                Le mie intro
            </span>
        </h1>
        <ul class="check-empty">
            <?php foreach ($intros as $intro) { ?>
                <li id="intro-row-<?= $intro->ID ?>">
                    <a href="./view.php?id=<?= $intro->ID ?>" 
                        target="_blank" title="Apri in un'altra scheda" class="link">
                        <i class="icon <?= $intro->Language->value ?>"></i>
                        <?= htmlspecialchars($intro->Title) ?>
                    </a>
                    &nbsp; - 
                    <a href="./create.php?id=<?= $intro->ID ?>"
                        target="_self" title="Modifica" class="link">
                        Modifica <i class="edit"></i>
                    </a>
                    &nbsp; -
                    <a onclick="DeleteIntro(<?= $intro->ID ?>)"
                        title="Elimina" class="link">
                        Elimina <i class="delete"></i>
                    </a>
                </li>
            <?php } ?>
            <li class="show-if-list-empty">
                Non hai nessuna intro,
                <a href="./create.php" class="link" target="_self" title="Vai a pagina creazione">
                    <strong>creane una</strong>
                </a>
            </li>
        </ul>
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>

    <script>
        async function DeleteIntro(id)
        {
            if (!id)
                return;
            const esit = await ajax({
                'id': id,
                'action': 'delete-intro'
            });
            console.log(`Delete of #${id}: ${esit}`);
            if (esit !== 'ok')
            {
                return;
            }
            const li = document.getElementById('intro-row-' + id);
            if (li) {
                li.classList.add('fade-out');
                setTimeout(() => {
                    li.parentElement.removeChild(li);
                }, 1000);
            }
        }
    </script>
</body>
</html>
