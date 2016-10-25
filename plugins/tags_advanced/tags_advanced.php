<?php
$ShaarliRoot = dirname($_SERVER['SCRIPT_FILENAME']).'/';
define('SHAARLIROOT',$ShaarliRoot);
// I18n
if(!in_array('i18n',$GLOBALS['config']['ENABLED_PLUGINS']) && !function_exists('t')) {
  function t($txt) { return $txt; }
}


function tags_advanced_config($taData)
{
    $html = file_get_contents(PluginManager::$PLUGINS_PATH .'/tags_advanced/tools.html');
    $html = str_replace('{$token}', getToken() , $html);

    if(!function_exists('translate')) {
        function translate($matches) { return t($matches[1]); }
    }
    $html = preg_replace_callback('/\{\'(.+)\'\|t\}/i', 'translate', $html );

    if(!isset($taData) || empty($taData)) {
        $taData = array(
            0 => array('', 'fa-map-pin', '', true, false)
        );
    } else {
        array_push($taData, array('', 'fa-map-pin', '', true, false));
    }

    $listForm = '';

    foreach ($taData as $k => $v) {
        $pined     = ($v[3] === true) ? 'checked' : '';
        $private   = ($v[4] === true) ? 'checked' : '';
        $listForm .= '
    <tr id="ta_tag'.($k+1).'" data-order='.($k+1).'>
      <td>
        <div class="pull-left btn-group btn-group-xs" role="group">
          <button type="button" class="btn moveup" onclick="return tagsSwitch(\'#ta_tag'.($k+1).'\',-1);">▲<span class="sr-only"> '.t('Move up').'</span></button>
          <button type="button" class="btn movedown" onclick="return tagsSwitch(\'#ta_tag'.($k+1).'\',1);">▼<span class="sr-only"> '.t('Move down').'</span></button>
        </div>
      </td>
      <td>
        <label for="ta-name'.($k+1).'" class="sr-only">'.t('Tag name').'</label>
        <input type="text" class="form-control" name="ta-name'.($k+1).'" id="ta_name'.($k+1).'" value="'.htmlentities($v[0]).'" />
      </td>
      <td class="hidden">
        <label for="ta-icon'.($k+1).'" class="sr-only">'.t('Icon').'</label>
        <input type="text" class="form-control" name="ta-icon'.($k+1).'" id="ta_icon'.($k+1).'" value="'.htmlentities($v[1]).'" />
      </td>
      <td>
        <label for="ta-filter'.($k+1).'" class="sr-only">'.t('Filter').'</label>
        <input type="text" class="form-control" name="ta-filter'.($k+1).'" id="ta_filter'.($k+1).'" value="'.htmlentities($v[2]).'" />
      </td>
      <td>
        <label for="ta-pined'.($k+1).'" class="sr-only">'.t('Pined').'</label>
        <input type="checkbox" name="ta-pined'.($k+1).'" id="ta_pined'.($k+1).'" '.$pined.' />
      </td>
      <td>
        <label for="ta-private'.($k+1).'" class="sr-only">'.t('Private').'</label>
        <input type="checkbox" name="ta-private'.($k+1).'" id="ta_private'.($k+1).'" '.$private.' />
      </td>
    </tr>';
    }

    return str_replace('{taListForm}', $listForm, str_replace('{$ta-count}', count($taData)+1 , $html));
}

/**
 * Display special tags in the sidebar
 * + Config tags data in the modal
 */
function hook_tags_advanced_render_linklist($data)
{
    require_once SHAARLIROOT.'data/tagsadvanced.php';

    if(isset($taData) && !empty($taData)) {

        $data['plugin_end_zone'][] = '<br>
        <ul style="list-group" id="taList">';

        foreach ($taData as $k => $v) {
            // Display special tag in the list
            if( $v[3] === true ) { // If tag pined
                if( $v[4] === false // If tag public
                || ($v[4] === true && $data['_LOGGEDIN_'] === true) ) { // or private but user loggedin
                    $private = $v[4] ? ' private' : '';
                    $data['plugin_end_zone'][] .= '
            <li class="list-group-item text-capitalize'.$private.'"><i class="fa fa-fw fa-lg '.htmlentities($v[1]).'"></i> <a href="?searchtags='.htmlentities($v[0]).'">'.htmlentities($v[0]).'</a></li>';
                }
            }
        }
        $data['plugin_end_zone'][] .= '
        </ul>';
    }

    if ($data['_LOGGEDIN_'] === true) {
      $html = '<p class="pull-left"><a href="#tagsAdvanced" data-toggle="modal" class="btn btn-default btn-xs" ><i class="fa fa-fw fa-lg fa-cog"></i><span class="sr-only"> '.t('Configure tags').'</span></a></p>';
      $data['plugin_end_zone'][] .= $html . tags_advanced_config($taData);
    }

    // TODO: add tags icons
    return $data;
}

/**
 * Config tags data from tools page
 */
function hook_tags_advanced_render_tools($data)
{
    $html = '
<a href="#tagsAdvanced" data-toggle="modal" class="list-group-item">
  <h2 class="h4 list-group-item-heading"><i class="fa fa-fw fa-2x fa-tags pull-left"></i>'.t('Tags advanced').'</h2>
  <p class="list-group-item-text">'.t('Keep visible some important tags and define filters to automatically add tags to your links').'</p>
</a>';

    require_once SHAARLIROOT.'data/tagsadvanced.php';

    $data['tools_plugin'][] = $html . tags_advanced_config($taData);

    return $data;
}


/**
 * Tagcloud.
 */
function hook_tags_advanced_render_tagcloud($data)
{
    require_once SHAARLIROOT.'data/tagsadvanced.php';

    foreach($taData as $k => $v) {
        // add icon
        if(isset($data['tags'][$v[0]]) && $data['tags'][$v[0]]['count']>0) {
            $data['tags'][$v[0]]['icon'] = '<i class="fa fa-fw fa-lg '.htmlentities($v[1]).'"></i>';
        }
        // remove private tags
        if( $v[4] === true && $data['_LOGGEDIN_'] === false ) { // If tag private
            unset($data['tags'][$v[0]]);
        }
    }
    return $data;
}


/*
 * Apply filters on saving
 */
function hook_tags_advanced_save_link($data)
{
    require_once SHAARLIROOT.'data/tagsadvanced.php';

    $tagsList = '';
    foreach($taData as $k => $v) {
        if(!empty($v[2])                                    // there is a filter
            && preg_match("/".$v[2]."/i", $data['url'])   // url need to be tagged
            && !preg_match("/".$v[0]."/i", $data['tags']))  // tag is not already set
        {
            $tagsList .= ' '.$v[0];
        }
    }

    $data['tags'] .= (empty($data['tags'])) ? ltrim($tagsList, ' ') : $tagsList;

    return $data;
}

/* Script FOOTER */
function hook_tags_advanced_render_footer($data)
{
    if ($data['_LOGGEDIN_'] === true ) {
        $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/tags_advanced/tags_advanced.js';
    }

    return $data;
}