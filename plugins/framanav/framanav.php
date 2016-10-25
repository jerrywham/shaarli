<?php
function hook_framanav_render_footer($data)
{
    $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/framanav/framanav.js';

    return $data;
}