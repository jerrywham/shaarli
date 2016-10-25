<?php

/**
 * Plugin addsearch_toolbar.
 * Adds the addsearch input on the linklist page.
 */

/**
 * When linklist is displayed, add play videos to header's toolbar.
 *
 * @param array $data - header data.
 *
 * @return mixed - header data with addsearch toolbar item.
 */
function hook_addsearch_toolbar_render_header($data)
{
    if ($data['_PAGE_'] == Router::$PAGE_LINKLIST) {
        $data['fields_toolbar'][] = file_get_contents(PluginManager::$PLUGINS_PATH . '/addsearch_toolbar/addsearch_toolbar.html');
    }

    return $data;
}

/**
 * When search list is displayed, include addsearch CSS.
 *
 * @param array $data - includes data.
 *
 * @return mixed - includes data with addsearch CSS file added.
 */
function hook_addsearch_toolbar_render_includes($data)
{
    if ($data['_PAGE_'] == Router::$PAGE_LINKLIST) {
        $data['css_files'][] = PluginManager::$PLUGINS_PATH . '/addsearch_toolbar/addsearch_toolbar.css';
    }

    return $data;
}
