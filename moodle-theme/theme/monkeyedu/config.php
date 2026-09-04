<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'monkeyedu';
$THEME->parents = ['boost'];

$THEME->sheets = [];
$THEME->editor_sheets = [];

$THEME->scss = function($theme) {
    return theme_monkeyedu_get_main_scss_content($theme);
};

$THEME->extrascsscallback = 'theme_monkeyedu_get_extra_scss';
$THEME->prescsscallback = 'theme_monkeyedu_get_pre_scss';

$THEME->layouts = [];

$THEME->enable_dock = false;
$THEME->haseditswitch = true;
$THEME->usescourseindex = true;

$THEME->requiredblocks = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
