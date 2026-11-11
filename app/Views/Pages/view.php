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
    <article id="credits" class="hidden">
        <?php if ($opening->AuthorName !== null && $opening->AuthorName !== '') { ?>
            <h2>
                Creato da utente <?= htmlspecialchars(string: $opening->AuthorName) ?>
            </h2>
        <?php } ?>

        <p>
            La musica &egrave; di propriet&agrave; di LucasFilm e Disney.
            <br>
            Questa simulazione &egrave; a solo scopo dimostrativo delle capacit&agrave;
            del design web; &egrave; pubblica e non pu&ograve; essere utilizzata per fini commerciali.
            <br>
            Per maggiori informazioni vai alla <a href="<?= htmlspecialchars($p . '/') ?>">Pagina principale</a>
        </p>

        <div class="row">
            <button type="button" id="restart">
                Riavvia
            </button>
            <button type="button" id="share">
                Condividi
            </button>
        </div>
    </article>

    <!-- Stars for the background -->
    <?php include __DIR__ . "/../Shared/stars.php" ?>
</main>
<script type="text/javascript">
    'use strict';
    // Restart the page (reload) and share
    const reloadBtn = document.getElementById('restart');
    if (reloadBtn) {
        reloadBtn.addEventListener('click', () => window.location.reload());
    }
    const shareBtn =document.getElementById('share');
    if (!navigator.share || !navigator.canShare || !navigator.canShare({
        title: document.title,
        text: 'Guarda anche tu la mia intro di Star Wars',
        url: window.location.href
    })) {
        shareBtn.classList.add('hidden');
    } else {
        shareBtn.addEventListener('click', () => {
            navigator.share({
                title: document.title,
                text: 'Guarda anche tu la mia intro di Star Wars',
                url: window.location.href
            })
        });
    }
</script>
