<?php
// El puente entre el panel de configuración y el CSS real. Boost
// arma su hoja de estilos a partir de variables SCSS ($primary,
// $body-color, etc.) — acá se las pisamos con lo que cada colegio
// cargó en su panel, y si no cargó nada, caen los valores por
// defecto neutros de settings.php. Nunca hay un colegio hardcodeado.
defined('MOODLE_INTERNAL') || die();

function theme_monkeyedu_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;

    $fs = get_file_storage();
    $context = context_system::instance();

    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_monkeyedu', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    return $scss;
}

function theme_monkeyedu_get_pre_scss($theme) {
    $scss = '';
    $configurable = [
        'primarycolor'   => 'primary',
        'secondarycolor' => 'secondary',
        'textcolor'      => 'body-color',
        'navbarcolor'    => 'navbar-bg',
    ];

    foreach ($configurable as $setting => $scssvar) {
        $value = $theme->settings->$setting ?? '';
        if (!empty($value)) {
            $scss .= '$' . $scssvar . ': ' . $value . ";\n";
        }
    }

    if (!empty($theme->settings->googlefont)) {
        $family = explode(':', $theme->settings->googlefont)[0];
        $scss .= '$font-family-sans-serif: "' . $family . '", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;' . "\n";
    }

    return $scss;
}

function theme_monkeyedu_get_extra_scss($theme) {
    global $CFG;

    $content = '';

    // Tipografía de Google Fonts: se pide como cualquier sitio la
    // pediría, sin depender de que el servidor tenga internet para
    // "instalarla" — el navegador del usuario la baja.
    if (!empty($theme->settings->googlefont)) {
        $font = urlencode($theme->settings->googlefont);
        $content .= "@import url('https://fonts.googleapis.com/css2?family={$font}&display=swap');\n";
    }

    // Fondo de la portada de login, si el colegio cargó uno.
    $loginbg = $theme->setting_file_url('loginbackgroundimage', 'loginbackgroundimage');
    if (!empty($loginbg)) {
        $content .= "
            #page-login-index .login-page {
                background-image: url('{$loginbg}');
                background-size: cover;
                background-position: center;
            }
            #page-login-index .login-page .card {
                background: rgba(255,255,255,.95);
            }
        ";
    }

    if (!empty($theme->settings->hidepoweredbymoodle)) {
        $content .= "
            .homelink { display: none !important; }
        ";
    }

    if (!empty($theme->settings->rawscss)) {
        $content .= $theme->settings->rawscss;
    }

    return $content;
}

/**
 * Sirve los archivos subidos en el panel (logo, favicon, fondo de login).
 */
function theme_monkeyedu_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    $theme = theme_config::load('monkeyedu');

    if (in_array($filearea, ['logo', 'favicon', 'loginbackgroundimage', 'preset'])) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}
