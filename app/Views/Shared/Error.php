<?php if ($status_code === 404) { ?>
    <h1>Questa non &egrave; la pagina che stai cercando!</h1>
    <div class="img no-ctx" style="max-width: 350px">
        <img src="<?= htmlspecialchars($p) ?>/assets/img/benkenobi.svg" alt="Obi Wan Kenobi">
    </div>
<?php } else { ?>
    <h1>&Egrave; avvenuto un errore.</h1>
<?php } ?>
<p><?= htmlspecialchars($status_code >= 500 ? 'Errore interno del server.' : ($main_banner ?? 'Errore')) ?></p>
<a class="link" href="<?= htmlspecialchars($p . '/') ?>">Torna alla pagina principale</a>
