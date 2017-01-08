<?php

/**
 * Constants which are set depending on SS_ENVIRONMENT if not already explicitly set (e.g in _ss_environment.php).
 * Can be used e.g. in config yaml files with constantdefined predicate.
 */
if (!defined('MODULAR_DEV')) {
    if (Director::isDev()) {
        define('MODULAR_DEV', 'auto');
    }
}
if (!defined('MODULAR_TEST')) {
    if (Director::isTest()) {
        define('MODULAR_TEST', 'auto');
    }
}
if (!defined('MODULAR_LIVE')) {
    if (Director::isLive()) {
        define('MODULAR_LIVE', 'auto');
    }
}
