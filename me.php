<?php
require_once "./utils/session.php";
$intros = Opening::LoadBriefOfUser($db, $USER_ID);
$TITLE = "Le mie intro";
?>
<!DOCTYPE html>
<html>
<?php include "./parts/head.php" ?>
<body>
    <?php include "./parts/nav.php" ?>
    <div class="body">
        <h1>
            Le mie intro
        </h1>
        <ul>
            <?php foreach ($intros as $intro) { ?>
                <li>
                    <a href="/view.php?id=<?= $intro->ID ?>" 
                        target="_blank" title="Apri in un'altra scheda" class="link">
                        <?= htmlspecialchars($intro->Title) ?>
                    </a>
                </li>
            <? } ?>
        </ul>
    </div>
    <?php include "./parts/footer.php" ?>
</body>
</html>
