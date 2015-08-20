<?php
/**
 * Constants which can be set independantly of normal isDev etc, by default track it though.
 * Can be used e.g. in config with constantdefined.
 */
if (!defined('CRACKERJACK_DEV')) {
    if (Director::isDev()) {
        define('CRACKERJACK_DEV', 'auto');
    }
}
if (!defined('CRACKERJACK_TEST')) {
    if (Director::isTest()) {
        define('CRACKERJACK_TEST', 'auto');
    }
}
if (!defined('CRACKERJACK_LIVE')) {
    if (Director::isLive()) {
        define('CRACKERJACK_LIVE', 'auto');
    }
}
