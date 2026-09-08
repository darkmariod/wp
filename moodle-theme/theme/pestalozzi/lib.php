<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Bootstrap/Boost variable overrides. Runs before any parent theme SCSS,
 * so these values win over every later `!default` declaration.
 *
 * Values copied from pestalozzi/src/styles/tokens.css - keep both in sync
 * by hand, there is no shared source between the Astro site and Moodle.
 */
function theme_pestalozzi_get_pre_scss($theme) {
    return <<<'SCSS'
$primary:           #126333;
$secondary:         #35AD65;
$success:           #239450;
$warning:           #E8A33D;
$danger:            #C0402F;
$info:              #2F6FB0;
$body-bg:           #FAFAF7;
$body-color:        #14261C;
$link-color:        #126333;
$link-hover-color:  #0B4A26;
$border-color:      #E4E7E3;

$border-radius:      1rem;
$border-radius-sm:   0.5rem;
$border-radius-lg:   1.5rem;
$border-radius-xl:   1.5rem;

$font-family-sans-serif: "DM Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;

$brand-primary: #126333;
$secondary-menu-color: #126333;
SCSS;
}

/**
 * SCSS appended after every parent theme SCSS. Component-level tweaks
 * that don't have a matching Bootstrap variable.
 */
function theme_pestalozzi_get_extra_scss($theme) {
    return <<<'SCSS'
.card, .box, .generalbox, .list-group-item, .fp-content {
    box-shadow: 0 1px 2px rgba(20, 38, 28, .06);
}

// Firma del proveedor del tema base (conecti.me) - no aporta nada a las
// familias del colegio. Moove fuerza "#page-footer .copyright" a
// display:block!important (misma especificidad que un simple ".copyright"),
// así que hay que igualar el selector para ganar por orden de aparición.
#page-footer .copyright {
    display: none !important;
}
SCSS;
}
