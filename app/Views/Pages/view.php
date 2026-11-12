<h1 class="hidden">
    <?= htmlspecialchars($title) ?>
</h1>

<!-- Loader shown on start -->
<div id="loader">
    <div class="loader"></div>
</div>

<!-- Most important element -->
<main id="screen">

    <!-- Intro part -->
    <section class="intro">
        <?= $opening->getIntro() ?>
    </section>

    <!-- Star wars logo svg. Do not copy -->
    <section class="logo">
        <img src="<?= htmlspecialchars($p) ?>/assets/img/starwarslogo.svg" alt="Star Wars">
    </section>

    <!-- Text parts -->
    <article id="board">
        <div id="content">
            <p id="episode">
                <?= htmlspecialchars(string: $opening->Episode) ?>
            </p>
            <p id="title">
                <?= htmlspecialchars(string: $opening->Title) ?>
            </p>
            <br>
            <?php foreach ($opening->Paragraphs() as $part) { ?>
                <p class="paragraph">
                    <?= $part ?>
                </p>
            <?php } ?>
        </div>
    </article>

    <!-- Who created the opening -->
    <article id="credits" class="hidden" lang="<?= $locale ?>">
        <?php if ($opening->AuthorName !== null && $opening->AuthorName !== '') { ?>
            <h2>
                <?= $t('Created by {author}', ['author' => $opening->AuthorName]) ?>
            </h2>
        <?php } ?>

        <p>
            <?= $t('Unaffiliated fan project. Not endorsed by Disney or Lucasfilm.') ?>
            <br>
            <?= $t('Star Wars names, logos, characters, film text, music, and recordings remain subject to their respective rights holders. This project grants no rights to those materials.') ?>
            <br>
            <?= $t('For more information, visit the') ?> <a href="<?= htmlspecialchars($p . '/') ?>"><?= $t('home page') ?></a>
        </p>

        <p><a href="<?= htmlspecialchars($p . '/credits') ?>"><?= $t('Credits and license') ?></a></p>

        <div class="row">
            <button type="button" id="restart">
                <?= $t('Restart') ?>
            </button>
            <button type="button" id="share">
                <?= $t('Share') ?>
            </button>
        </div>
    </article>

    <!-- Stars for the background -->
    <?php include __DIR__ . "/../Shared/stars.php" ?>
</main>
<script type="text/javascript">
    'use strict';
    document.getElementById('restart').addEventListener('click', () => location.reload());
    const shareBtn = document.getElementById('share');
    const shareUrl = new URL(location.href);
    shareUrl.searchParams.set('lang', <?= json_encode($opening->Language->value) ?>);
    const shareData = { title: document.title,
        text: <?= json_encode(\StarWars\Support\I18n::text('Watch my custom Star Wars intro!'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
        url: shareUrl.href };
    if (!navigator.share || !navigator.canShare?.(shareData)) {
        shareBtn.classList.add('hidden');
    } else {
        shareBtn.addEventListener('click', async () => {
            try { await navigator.share(shareData); }
            catch (error) {
                if (error.name !== 'AbortError') shareBtn.textContent = <?= json_encode(\StarWars\Support\I18n::text('Sharing failed. Please try again.')) ?>;
            }
        });
    }
</script>
