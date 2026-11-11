<?php use StarWars\Support\Random; ?>
<style>
    .star {
        width: 1.5px;
        height: 1.5px;
        background-color: rgba(249, 251, 242, 0.8);
        z-index: -1;
    }
    .star.star-big {
        width: 4px;
        height: 4px;
        border: none;
        border-radius: 2px;
    }
</style>
<?php for ($i = 0; $i < ($starCount ?? 100); $i++) { ?>
    <div class="star<?= Random::float() > 0.94 ? ' star-big' : '' ?>"
        style="top: <?= Random::percentage() ?>; left: <?= Random::percentage() ?>"></div>
<?php } ?>
