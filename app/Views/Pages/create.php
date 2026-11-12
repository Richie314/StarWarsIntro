<?php
use StarWars\Models\OpeningLanguage;
?>
<h1 class="aurebesh" data-content="<?= htmlspecialchars($title) ?>"> </h1>
<form class="intro-form" method="post" action="<?= htmlspecialchars($p . '/opening/create') ?>">
    <input type="hidden" name="id" value="<?= $opening->Id ?>" required>

    <div class="grid" style="grid-template-columns: 30% 70%">

        <label for="episode" class="center">
            <?= $t("Episode:") ?>
        </label>
        <input type="text" id="episode" name="episode" required
            placeholder="<?= $t("Episode X") ?>"
            value="<?= htmlspecialchars($opening->Episode) ?>"
            spellcheck="true" lang="<?= $opening->Language->value ?>">

        <label for="title" class="center">
            <?= $t("Title:") ?>
        </label>
        <input type="text" id="title" name="title" required
            placeholder="<?= $t("A new title") ?>"
            value="<?= htmlspecialchars($opening->Title) ?>"
            spellcheck="true" lang="<?= $opening->Language->value ?>">

        <label for="lang" class="center">
            <?= $t("Language:") ?>
        </label>
        <select id="lang" name="lang" required>
            <option value="<?= OpeningLanguage::Italian->value ?>"
                <?= $opening->Language === OpeningLanguage::Italian ? "selected" : "" ?>>
                <?= $t("Italian") ?>
            </option>
            <option value="<?= OpeningLanguage::English->value ?>"
            <?= $opening->Language === OpeningLanguage::English ? "selected" : "" ?>>
                <?= $t("English") ?>
            </option>
        </select>

        <div class="span-2">
            <br>
            <label for="content" class="center" style="font-size: larger">
                <?= $t("Your story:") ?>
            </label>
            <br>
            <textarea maxlength="4096"
                name="content" id="content"
                cols="30" rows="17"
                placeholder="<?= $t("Leave a blank line between paragraphs") ?>"
                spellcheck="true" lang="<?= $opening->Language->value ?>" ><?php
                    if (trim($opening->Content ?? '') !== '')
                        echo htmlentities($opening->Content);
                ?></textarea>
            <br>
            <?php
            if ($error !== null) {
                echo "<span>" . htmlspecialchars($error) . "</span>";
            }
            ?>
        </div>
        <div class="span-2">
            <h2>
                <?= $t("Preview your intro") ?>
            </h2>
        </div>
        <div class="tv span-2">
            <iframe src="<?= htmlspecialchars($p . '/opening/view') ?>" data-view-url="<?= htmlspecialchars($p . '/opening/view') ?>" allowfullscreen id="tv"
                width="1600" height="900" scrolling="no"
                title="<?= $t('Preview your intro') ?>" loading="lazy" sandbox="allow-scripts">
            </iframe>
            <button type="button" id="reload-tv" title="<?= $t("Restart") ?>" class="tv-control">

            </button>
        </div>
        <div class="span-2">
            <p>
                <?= $t('Need inspiration?') ?>
                <?= $t('Watch the') ?> <strong><a href="#show-originals-dialog" id="show-originals" class="link"><?= $t("original intros") ?></a></strong>
            </p>
        </div>

        <div class="span-2 grid" style="grid-template-columns: 1fr 1fr">
            <button type="reset" title="<?= $t("Reset") ?>" id="clear"></button>
            <button type="submit" id="submit"
                title="<?= $t($opening->Id !== 0 ? 'Edit' : 'Create') ?>"></button>
        </div>
    </div>
</form>
<dialog id="show-originals-dialog">
    <h3>
        <?= $t("Choose an original intro to preview") ?>
    </h3>
    <select id="dialog-select">
        <option value="0"><?= $t("Choose") ?></option>

        <optgroup label="<?= $t("In Italian") ?>">
            <option value="1-it">La minaccia fantasma</option>
            <option value="2-it">L'attacco dei cloni</option>
            <option value="3-it">La vendetta dei Sith</option>
            <option value="4-it">Una nuova Speranza</option>
            <option value="5-it">L'Impero colpisce ancora</option>
            <option value="6-it">Il ritorno dello Jedi</option>
            <option value="7-it">Il risveglio della Forza</option>
            <option value="8-it">Gli ultimi Jedi</option>
            <option value="9-it">L'ascesa di Skywalker</option>
        </optgroup>

        <optgroup label="<?= $t("In English") ?>">
            <option value="1-en">The Phantom Menace</option>
            <option value="2-en">Attack of the Clones</option>
            <option value="3-en">Revenge of the Sith</option>
            <option value="4-en">A New Hope</option>
            <option value="5-en">The Empire Strikes Back</option>
            <option value="6-en">Return of the Jedi</option>
            <option value="7-en">The Force Awakens</option>
            <option value="8-en">The Last Jedi</option>
            <option value="9-en">The Rise of Skywalker</option>
        </optgroup>
    </select>
    <br>
    <button role="button" type="button" id="dialog-close" data-close-dialog>
        <?= $t("Cancel") ?>
    </button>
</dialog>
<dialog id="share-dialog">
    <h3>
        <?= $t("Share your new intro!") ?>
    </h3>
    <button role="button" type="button" id="share-btn">
        <?= $t("Share") ?>
    </button>
    <button role="button" type="button" id="share-close-btn" data-close-dialog>
        <?= $t("Not now") ?>
    </button>
</dialog>
