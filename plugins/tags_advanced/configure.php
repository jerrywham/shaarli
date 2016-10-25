<?php
$ShaarliRoot = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
$protocol = (!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == 'on') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https' ) || $proto == 'https' ? 'https://' : "http://";
$servername = $_SERVER['HTTP_HOST'];
$serverport = (preg_match('/:[0-9]+/', $servername) OR $_SERVER['SERVER_PORT'])=='80' ? '' : ':'.$_SERVER['SERVER_PORT'];
$dirname = preg_replace('/\/(core|plugins)\/(.*)/', '', dirname($_SERVER['SCRIPT_NAME']));
$racine = rtrim($protocol.$servername.$serverport.$dirname, '/\\').'/';

session_name('shaarli');
if (session_id() == '') {
    session_start();
}

if( isset($_POST['saveTags']) && isset($_POST['token']) ) {
    $token = $_POST['token'];

    $taFile = $ShaarliRoot . '/data/tagsadvanced.php';

    // = tokenOk() from shaarli/index.php
    if (isset($_SESSION['tokens'][$token])) {
        unset($_SESSION['tokens'][$token]);

        // Prepare data/tags_advanced.php content
        $taData = '<?php'. PHP_EOL;
        $taData .= '    $taData = array('. PHP_EOL;

        for( $i=1;$i<=$_POST['ta-count'];$i++ ) {
            if(isset($_POST['ta-name'.$i]) && !empty($_POST['ta-name'.$i])) {
                $pined = (!empty($_POST['ta-pined'.$i])) ? 'true' : 'false';
                $private = (!empty($_POST['ta-private'.$i])) ? 'true' : 'false';
                if($_POST['ta-icon'.$i] == 'fa-map-pin') {
                    $color = array('fc_g7','fc_g9','fc_b8','fc_b9','fc_v7','fc_v9',
                       'fc_o8','fc_r5','fc_m5','fc_j6','fc_f5');
                    $_POST['ta-icon'.$i] .= ' ' . $color[array_rand($color)];
                }
                $taData .= "        ".($i-1)." => array(". var_export($_POST['ta-name'.$i], true).", ".var_export($_POST['ta-icon'.$i], true).", ".var_export($_POST['ta-filter'.$i], true).", ".$pined.", ".$private."),". PHP_EOL;
            }
        }

        $taData .= '    );'. PHP_EOL;
        $taData .= '?>';

        // Save data
        if (!file_put_contents($taFile, $taData)
            || strcmp(file_get_contents($taFile), $taData) != 0
        ) {
            throw new Exception(
                'Shaarli could not create the data file.
                Please make sure Shaarli has the right to write in the folder is it installed in.'
            );
        } else {
            // Then redirect on the page
            if(isset($_SERVER['HTTP_REFERER'])) {
                header('Location: '.$_SERVER['HTTP_REFERER']);
            } else {
                header('Location: '.$racine);
            }
        }
    }
}
?>