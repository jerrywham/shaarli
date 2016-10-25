<?php

$langs = [
    'fr' => 'Français',
    'en' => 'English',
    //'es' => 'Español',
];

$paramLang = '';

// Default language fr
if ( !isset($_SESSION['lang'])
            || !in_array($_SESSION['lang'], array_keys($langs)) ) {
    $_SESSION['lang'] = 'fr';
}

// Manual set by URL
if (isset($_GET['l'])
    && is_string($_GET['l'])
    && in_array($_GET['l'], array_keys($langs)) ) {

    $_SESSION['lang'] = $_GET['l'];
    $paramLang = '?l='.$_GET['l'];

// Manual set by form
} elseif (  isset($_POST['lang']) && is_string($_POST['lang'])
            && in_array($_POST['lang'], array_keys($langs)) ) {

    $_SESSION['lang'] = $_POST['lang'];

// Check available languages
} else {

    $browserLangs = array();

    if (isset($_SERVER) && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
        preg_match_all("/([[:alpha:]]{1,8}(?:-[[:alpha:]|-]{1,8})?)" .
                       "(?:\\s*;\\s*q\\s*=\\s*(?:1\\.0{0,3}|0\\.\\d{0,3}))?\\s*(?:,|$)/i",
                       $_SERVER['HTTP_ACCEPT_LANGUAGE'], $hits);
        foreach ($hits[1] as $hit) {
            $browserLangs[] = strtolower(substr($hit,0,2));
        }
    }

    if(in_array('fr', $browserLangs)) {
        // french is better
        $_SESSION['lang'] = 'fr';
    } else {
        // 1st default language
        foreach($browserLangs as $lang) {
            if(in_array($lang, array_keys($langs))) {
                $_SESSION['lang'] = $lang;
                break;
            }
        }
    }
}

// Function t() is called in the template like this : {'String to translate'|t}
function t($txt){
    // Import $t variable from locale/lg.php files in each plugin
    // The 'i18n' plugin is one of them and contains the main file
    foreach($GLOBALS['config']['ENABLED_PLUGINS'] as $plugin) {

        if(class_exists( 'PluginManager' )) {
            // Call from Shaarli's index.php
            $locale = PluginManager::$PLUGINS_PATH . '/' . $plugin . '/locale/'.$_SESSION['lang'].'.php';
        } elseif (defined('PATH_SHAARLI')) {
            // Call from out of Shaarli
            $locale = PATH_SHAARLI.'/plugins/' . $plugin . '/locale/'.$_SESSION['lang'].'.php';
        } else {
            // Call from a plugin file
            $locale = '../' . $plugin . '/locale/'.$_SESSION['lang'].'.php';
        }
        if (is_file( $locale ) ) {
            require( $locale );
        }
    }
    $txt = (isset($t[$txt]) && !empty($t[$txt])) ? $t[$txt] : $txt;
    return $txt;
}
/*
// TODO: Add a select form to switch between available langs
function hook_i18n_render_header($data)
{
    return $data;
}

function hook_i18n_render_footer($data)
{
    return $data;
}
*/