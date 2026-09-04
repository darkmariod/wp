<?php
// Panel de configuración del theme: todo lo que cambia de un colegio a
// otro vive acá (colores, logo, favicon). El código del theme nunca
// tiene el verde de Pestalozzi ni el de ningún cliente escrito a mano
// — por eso es reutilizable sin tocar una línea al entregar a otro
// colegio.
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings = new theme_boost_admin_settingspage_tabs('themesettingmonkeyedu', get_string('configtitle', 'theme_monkeyedu'));

    // ============ Pestaña: Identidad ============
    $page = new admin_settingpage('theme_monkeyedu_identity', get_string('identitysettings', 'theme_monkeyedu'));

    $page->add(new admin_setting_heading(
        'theme_monkeyedu_identity_heading',
        get_string('identitysettings', 'theme_monkeyedu'),
        get_string('identitysettings_desc', 'theme_monkeyedu')
    ));

    $name = 'theme_monkeyedu/institutionname';
    $page->add(new admin_setting_configtext(
        $name,
        get_string('institutionname', 'theme_monkeyedu'),
        get_string('institutionname_desc', 'theme_monkeyedu'),
        '',
        PARAM_TEXT
    ));

    $name = 'theme_monkeyedu/logo';
    $page->add(new admin_setting_configstoredfile(
        $name,
        get_string('logo', 'theme_monkeyedu'),
        get_string('logo_desc', 'theme_monkeyedu'),
        'logo',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.png', '.svg', '.jpg', '.jpeg']]
    ));

    $name = 'theme_monkeyedu/favicon';
    $page->add(new admin_setting_configstoredfile(
        $name,
        get_string('favicon', 'theme_monkeyedu'),
        get_string('favicon_desc', 'theme_monkeyedu'),
        'favicon',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.ico', '.png']]
    ));

    $name = 'theme_monkeyedu/loginbackgroundimage';
    $page->add(new admin_setting_configstoredfile(
        $name,
        get_string('loginbackgroundimage', 'theme_monkeyedu'),
        get_string('loginbackgroundimage_desc', 'theme_monkeyedu'),
        'loginbackgroundimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.png', '.jpg', '.jpeg', '.webp']]
    ));

    $settings->add($page);

    // ============ Pestaña: Colores ============
    $page = new admin_settingpage('theme_monkeyedu_colors', get_string('colorsettings', 'theme_monkeyedu'));

    $page->add(new admin_setting_heading(
        'theme_monkeyedu_colors_heading',
        get_string('colorsettings', 'theme_monkeyedu'),
        get_string('colorsettings_desc', 'theme_monkeyedu')
    ));

    $name = 'theme_monkeyedu/primarycolor';
    $page->add(new admin_setting_configcolourpicker(
        $name,
        get_string('primarycolor', 'theme_monkeyedu'),
        get_string('primarycolor_desc', 'theme_monkeyedu'),
        '#0B4A26'
    ));

    $name = 'theme_monkeyedu/secondarycolor';
    $page->add(new admin_setting_configcolourpicker(
        $name,
        get_string('secondarycolor', 'theme_monkeyedu'),
        get_string('secondarycolor_desc', 'theme_monkeyedu'),
        '#E8A33D'
    ));

    $name = 'theme_monkeyedu/textcolor';
    $page->add(new admin_setting_configcolourpicker(
        $name,
        get_string('textcolor', 'theme_monkeyedu'),
        get_string('textcolor_desc', 'theme_monkeyedu'),
        '#14261C'
    ));

    $name = 'theme_monkeyedu/navbarcolor';
    $page->add(new admin_setting_configcolourpicker(
        $name,
        get_string('navbarcolor', 'theme_monkeyedu'),
        get_string('navbarcolor_desc', 'theme_monkeyedu'),
        '#FFFFFF'
    ));

    $settings->add($page);

    // ============ Pestaña: Tipografía ============
    $page = new admin_settingpage('theme_monkeyedu_typography', get_string('typographysettings', 'theme_monkeyedu'));

    $page->add(new admin_setting_heading(
        'theme_monkeyedu_typography_heading',
        get_string('typographysettings', 'theme_monkeyedu'),
        get_string('typographysettings_desc', 'theme_monkeyedu')
    ));

    $name = 'theme_monkeyedu/googlefont';
    $page->add(new admin_setting_configtext(
        $name,
        get_string('googlefont', 'theme_monkeyedu'),
        get_string('googlefont_desc', 'theme_monkeyedu'),
        'DM Sans:400,500,600,700',
        PARAM_TEXT
    ));

    $settings->add($page);

    // ============ Pestaña: Pie de página ============
    $page = new admin_settingpage('theme_monkeyedu_footer', get_string('footersettings', 'theme_monkeyedu'));

    $page->add(new admin_setting_heading(
        'theme_monkeyedu_footer_heading',
        get_string('footersettings', 'theme_monkeyedu'),
        ''
    ));

    $name = 'theme_monkeyedu/footnote';
    $page->add(new admin_setting_confightmleditor(
        $name,
        get_string('footnote', 'theme_monkeyedu'),
        get_string('footnote_desc', 'theme_monkeyedu'),
        ''
    ));

    $name = 'theme_monkeyedu/hidepoweredbymoodle';
    $page->add(new admin_setting_configcheckbox(
        $name,
        get_string('hidepoweredbymoodle', 'theme_monkeyedu'),
        get_string('hidepoweredbymoodle_desc', 'theme_monkeyedu'),
        1
    ));

    $settings->add($page);

    // ============ Pestaña: SCSS avanzado ============
    // Vía de escape para ajustes puntuales que no ameritan un campo
    // propio en el panel — mismo patrón que usa theme_boost.
    $page = new admin_settingpage('theme_monkeyedu_advanced', get_string('advancedsettings', 'theme_monkeyedu'));

    $page->add(new admin_setting_heading(
        'theme_monkeyedu_advanced_heading',
        get_string('advancedsettings', 'theme_monkeyedu'),
        ''
    ));

    $setting = new admin_setting_scsscode(
        'theme_monkeyedu/rawscss',
        get_string('rawscss', 'theme_monkeyedu'),
        get_string('rawscss_desc', 'theme_monkeyedu'),
        '',
        PARAM_RAW
    );
    $page->add($setting);

    $settings->add($page);
}
