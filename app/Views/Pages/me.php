<div>
    <h2>
        Link utili:
    </h2>
    <ul>
        <li>
            <a href="<?= htmlspecialchars($p . '/auth/changePassword') ?>" class="link" target="_self" title="Cambia la tua password">
                Cambia password
            </a>
        </li>
        <?php if ($user?->Admin) { ?>
            <li>
                <a href="<?= htmlspecialchars($p . '/admin') ?>#login-list" class="link" target="_self" title="Vai alla lista">
                    Lista dei login di tutti gli utenti
                </a>
            </li>
            <li>
                <a href="<?= htmlspecialchars($p . '/admin') ?>#inactive-list" class="link" target="_self" title="Vai alla lista">
                    Lista utenti inattivi
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
<h1 class="aurebesh" data-content="Le mie intro"> </h1>
<ul>
    <?php foreach ($intros as $intro) { ?>
        <li id="intro-row-<?= $intro->Id ?>">
            <a href="<?= htmlspecialchars($p . '/opening/view') ?>?id=<?= $intro->Id ?>"
                target="_blank" title="Apri in un'altra scheda" class="link">
                <i class="icon <?= $intro->Language->value ?>"></i>
                <?= htmlspecialchars($intro->Title) ?>
            </a>
            &nbsp;-&nbsp;
            <a href="<?= htmlspecialchars($p . '/opening/create') ?>?id=<?= $intro->Id ?>"
                target="_self" title="Modifica" class="link">
                Modifica <i class="edit"></i>
            </a>
            &nbsp;-&nbsp;
            <a href="#" data-delete-url="<?= htmlspecialchars($p . '/opening/delete') ?>" data-delete-id="<?= $intro->Id ?>" data-delete-field="id" data-delete-row="intro-row-<?= $intro->Id ?>"
                title="Elimina" class="link">
                Elimina <i class="delete"></i>
            </a>
        </li>
    <?php } ?>
</ul>
<p class="show-if-list-empty">
    Non hai nessuna intro,
    <a href="<?= htmlspecialchars($p . '/opening/create') ?>" class="link" target="_self" title="Vai a pagina creazione">
        <strong>creane una</strong>
    </a>
</p>
<style type="text/css">
    p.show-if-list-empty {
        opacity: 0;
        user-select: none;
        height: 0;
        transition: all 1s ease-in-out;
        transition-delay: .4s;
    }
    ul:not(:has(li:not(.fade-out))) + p.show-if-list-empty {
        opacity: 1;
        height: auto;
    }
</style>
