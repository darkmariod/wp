<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/moove/lib.php');
require_once(__DIR__ . '/lib.php');

$THEME->name = 'pestalozzi';
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->parents = ['moove', 'boost'];
$THEME->enable_dock = false;
$THEME->usefallback = true;
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->requiredblocks = '';
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;
$THEME->activityheaderconfig = [
    'notitle' => true,
];

$THEME->scss = function ($theme) {
    return theme_moove_get_main_scss_content($theme);
};
$THEME->prescsscallback = 'theme_pestalozzi_get_pre_scss';
$THEME->extrascsscallback = 'theme_pestalozzi_get_extra_scss';
