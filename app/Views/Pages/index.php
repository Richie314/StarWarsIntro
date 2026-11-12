<?php use StarWars\Support\Random; ?>
<h1><?= $t('Star Wars Intro Creator') ?></h1>
<div class="img no-ctx">
    <img src="<?= htmlspecialchars($p) ?>/assets/sitelogo.svg" alt="Star Wars Intro">
</div>
<article style="max-width: 500px; padding: 1em; margin-inline: auto;">
    <h2><?= $t('Ever wanted to recreate the Star Wars opening crawl?') ?></h2>
    <p><?= $t('Write your own adventure and share it with your friends.') ?></p>
    <p><a href="<?= htmlspecialchars($p . ($user !== null ? '/opening/create' : '/auth/register')) ?>" class="link">
        <strong><?= $t($user !== null ? 'Create a new intro' : 'Create an account') ?></strong>
    </a></p>
    <h2><?= $t('How does it work?') ?></h2>
    <p class="justify"><?= $t('Write an episode, a title, and your story. Preview the crawl as you type, then save it and share its link.') ?></p>
    <p><a class="link" href="<?= htmlspecialchars($p . '/opening/view') ?>?original=<?= Random::int(1, 9) ?>&amp;lang=<?= $locale ?>"><?= $t('Watch an original intro') ?></a></p>
    <p class="justify"><?= $t('Some headings use Aurebesh. Hover over them to reveal the familiar alphabet.') ?></p>
    <p class="justify"><?= $t('The yellow text and starry background take their inspiration from the opening titles of Star Wars.') ?></p>
    <h2><?= $t('About this project') ?></h2>
    <p class="justify"><?= $t('Created by Riccardo Ciucci for the Web Design exam of the Computer Engineering degree at the University of Pisa, academic year 2023–24.') ?></p>
    <p><a class="link" href="https://ciucci.dev" target="_blank" rel="noopener">Riccardo Ciucci</a> · <a class="link" href="https://www.unipi.it/">Università di Pisa</a></p>
    <p class="justify"><?= $t('This is a non-commercial, open-source demo. No copyright infringement is intended.') ?></p>
    <p><?= $t('Icons from') ?> <a href="https://svgrepo.com" class="link" target="_blank" rel="noopener">SVG Repo<i class="icon svgrepo"></i></a>
        <?= $t('and created with') ?> <a href="https://www.figma.com/" class="link" target="_blank" rel="noopener">Figma<i class="icon figma"></i></a>.</p>
</article>
