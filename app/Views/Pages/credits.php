<article class="credits-page">
    <p class="dashboard-eyebrow"><?= $t('An independent educational fan project') ?></p>
    <h1><?= $t('Credits and license') ?></h1>
    <section>
        <h2><?= $t('Original code and academic project') ?></h2>
        <p><?= $t('Application code by Riccardo Ciucci. Originally developed for the Web Programming examination at the University of Pisa, taught by Alessio Vecchio, academic year 2023–2024.') ?></p>
        <p><a class="link" href="https://ciucci.dev">Riccardo Ciucci</a> · <a class="link" href="https://www.unipi.it/">Università di Pisa</a> · <a class="link" href="https://vecchio.iet.unipi.it/vecchio/">Alessio Vecchio</a></p>
    </section>
    <section>
        <h2><?= $t('Noncommercial education and personal use only') ?></h2>
        <p><?= $t('You may study, modify, and share the original code for noncommercial education, personal projects, and occasional recreation under the license below.') ?></p>
        <p><?= $t('No resale, paid products or services, paid bundles, advertising, sponsorship, affiliate revenue, or related payments. Keep the author credit and license when sharing.') ?></p>
        <p><?= $t('This summary is explanatory. The full English license is authoritative and applies only to rights the author can grant.') ?></p>
    </section>
    <section class="rights-notice">
        <h2><?= $t('Unaffiliated fan project. Not endorsed by Disney or Lucasfilm.') ?></h2>
        <p><?= $t('Star Wars names, logos, characters, film text, music, and recordings remain subject to their respective rights holders. This project grants no rights to those materials.') ?></p>
        <p><?= $t('Bundled media permissions are not documented. Educational use and attribution alone do not establish permission or protection from claims.') ?></p>
    </section>
    <section>
        <h2><?= $t('Third-party material and credits') ?></h2>
        <ul>
            <li><?= $t('Music: Star Wars film scores by John Williams; the exact bundled recording and its permissions are unverified.') ?></li>
            <li><?= $t('Font: Aurebesh by Neale Davidson, 2013.') ?></li>
            <li><?= $t('Icons: SVG Repo and original vector work; individual licenses vary. Italian flag: Twemoji, Twitter and contributors, CC BY 4.0. Figma was used as a design tool.') ?></li>
            <li><?= $t('Libraries: Simple MVC and jQuery retain their independent upstream terms. See the full inventory for license details and unresolved provenance.') ?></li>
        </ul>
        <p><a class="link" href="https://www.svgrepo.com/page/licensing/">SVG Repo</a> · <a class="link" href="https://github.com/twitter/twemoji">Twemoji</a> · <a class="link" href="https://creativecommons.org/licenses/by/4.0/">CC BY 4.0</a> · <a class="link" href="https://jquery.com/">jQuery</a> · <a class="link" href="https://github.com/Richie314/Php-Simple-MVC">Simple MVC</a></p>
    </section>
    <?php foreach (['Full license (English)' => $licenseText, 'Project rights notice (English)' => $rightsNotice, 'Third-party inventory (English)' => $thirdPartyNotices] as $label => $text) { ?>
        <details>
            <summary><?= $t($label) ?></summary>
            <pre lang="en"><?= htmlspecialchars($text) ?></pre>
        </details>
    <?php } ?>
    <section>
        <h2><?= $t('Rights inquiries') ?></h2>
        <p><?= $t('Contact the author through the project issue tracker to identify material for review or removal. Do not post confidential documents publicly.') ?></p>
        <a class="link" href="https://github.com/Richie314/StarWarsIntro/issues">GitHub Issues</a>
    </section>
</article>
