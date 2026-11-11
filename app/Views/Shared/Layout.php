<!DOCTYPE html>
<html lang="it">
<?php require __DIR__ . '/head.php'; ?>
<body data-view-url="<?= htmlspecialchars($p . '/opening/view') ?>">
    <?php require __DIR__ . '/nav.php'; ?>
    <main class="<?= htmlspecialchars($mainClass ?? 'body') ?>">
        <p id="action-feedback" role="status" aria-live="polite" hidden></p>
        <?php $RenderBody(); ?>
    </main>
    <?php require __DIR__ . '/cookie.php'; ?>
    <?php require __DIR__ . '/stars.php'; ?>
    <?php require __DIR__ . '/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha384-fgGyf7Mo7DURSOMnOy7ed+dkq5Job205Gnzu6QIg0BOHKaqt4D76Dt8VlDCzcMHV" crossorigin="anonymous" defer></script>
    <script src="<?= htmlspecialchars($p) ?>/assets/page.js" defer></script>
    <?php foreach ($scripts ?? [] as $script) { ?>
        <script src="<?= htmlspecialchars($p . '/assets/' . $script) ?>" defer></script>
    <?php } ?>
</body>
</html>
