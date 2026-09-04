<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'theme_monkeyedu';
$plugin->version   = 2026090300;
$plugin->requires  = 2022041900; // Moodle 4.0+
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0.0';
$plugin->dependencies = [
    'theme_boost' => 2022041900,
];
