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
        <h1 class="aurebesh" data-content="Le mie intro"> </h1>
        <ul>
            <?php foreach ($intros as $intro) { ?>
                <li id="intro-row-<?= $intro->ID ?>">
                    <a href="./view.php?id=<?= $intro->ID ?>" 
                        target="_blank" title="Apri in un'altra scheda" class="link">
                        <i class="icon <?= $intro->Language->value ?>"></i>
                        <?= htmlspecialchars($intro->Title) ?>
                    </a>
                    &nbsp;-&nbsp;
                    <a href="./create.php?id=<?= $intro->ID ?>"
                        target="_self" title="Modifica" class="link">
                        Modifica <i class="edit"></i>
                    </a>
                    &nbsp;-&nbsp;
                    <a href="javascript:Delete(<?= $intro->ID ?>)"
                        title="Elimina" class="link">
                        Elimina <i class="delete"></i>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </main>
    <?php include "./parts/stars.php" ?>
    <?php include "./parts/footer.php"; ?>

    <script type="text/javascript">
        async function Delete(id)
        {
            if (!id)
                return;
            const res = await post('./delete.php', {
                'id': id
            });
            if (!('esit' in res))
            {
                console.log(`Delete of #${id}: Unknown esit`);
                return;
            }
            console.log(`Delete of #${id}: ${res.esit}`);
            if (res.esit !== 'ok')
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
