<?php
/**
 * Authorize web-triggered cron scripts.
 * CLI always allowed. Web requires ?key= matching ct_cron_secret (or ct_api_key if secret empty).
 *
 * @param cleanto_setting $setting
 * @param bool $isCli
 * @return bool
 */
function ct_cron_web_authorized($setting, $isCli) {
    if ($isCli) {
        return true;
    }
    $secret = trim((string)$setting->get_option('ct_cron_secret'));
    if ($secret === '') {
        $secret = trim((string)$setting->get_option('ct_api_key'));
    }
    $key = '';
    if (isset($_GET['key'])) {
        $key = (string)$_GET['key'];
    } elseif (isset($_POST['key'])) {
        $key = (string)$_POST['key'];
    }
    if ($secret === '' || $key === '') {
        return false;
    }
    return hash_equals($secret, $key);
}
