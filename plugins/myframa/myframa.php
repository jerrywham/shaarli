<?php

function hook_myframa_render_footer($data)
{
    $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/myframa/myframa.js.php';

    return $data;
}