<?php
$ShaarliRoot = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
// I18n
if(!in_array('i18n',$GLOBALS['config']['ENABLED_PLUGINS']) && !function_exists(t)) {
  function t($txt) { return $txt; }
}

function hook_recovery_render_header($data)
{
    if ($data['_PAGE_'] == Router::$PAGE_LOGIN) {
        $data['login'] =
            '<div id="recovery"><div class="row" id="rMsg" style="display:none;"><div class="container">';

        if ( isset($_GET['recover'])) {
            /* Step 1 : check email */
            if( isset($_POST['email']) && isset($_POST['token']) && tokenOk($_POST['token']) ) {

                unset($_SESSION['tokens'][$_POST['token']]);

                $ShaarliURL = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://'.$_SERVER['HTTP_HOST'].str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME'])).'/';
                $c = $GLOBALS;

                if($GLOBALS['config']['RECOVERY']['attempt'] >= 10) {

                    $data['login'] .= '<p class="alert alert-danger">'.t('Too many password recovery attempts. Please contact an administrator.').'</p>';

                } elseif (!strstr( $_POST['email'], '@')) {

                    $data['login'] .= '<p class="alert alert-warning">'.t('The email address is incorrectly formatted. Try Again.').'</p>';

                } elseif ( $_POST['email'] != $GLOBALS['config']['RECOVERY']['email'] ) {

                    $c['config']['RECOVERY']['attempt']++;
                    $data['login'] .= '<p class="alert alert-warning">'.t('The email address does not match what was stored. Please try again or contact an administrator.').'</p>';

                } else {

                    $data['login'] .= '<p class="alert alert-success">'.t('An email has been sent to your address. It contains a link that will allow you to change your password.').'</p>';

                    // → create token
                    $c['config']['RECOVERY']['token'] = sha1(uniqid('',true).'_'.mt_rand().$GLOBALS['salt']);
                    // → reinit attempt
                    $c['config']['RECOVERY']['attempt'] = 0;

                    // → send email with link and token param
                    $headers  = "From: ".$c['title']." <".$c['config']['RECOVERY']['noreply'].">\n";
                    $headers .= "Reply-To: ".$c['config']['RECOVERY']['noreply']."\n";
                    $headers .= "MIME-Version: 1.0\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\n";
                    $headers .= "Content-Transfer-Encoding: 8bit\n";
                    $headers .= "Auto-Submitted:auto-generated\n";
                    $headers .= "Return-Path: <>";

                    $body = '<html>
                      <body>
                        <div>
                          <p>'.t('Hello').' '.$c['login'].',</p>
                          <p>
                            '.t('You asked to reset your password on ').'<a href="'.$ShaarliURL.'">'.$c['title'].'</a>.<br>
                            '.t('Please click on the link below in order to complete the change password form:').'<br/>
                            <a href="'.$ShaarliURL.'?do=login&recover&token='.$c['config']['RECOVERY']['token'].'">'.$ShaarliURL.'?do=login&recover&token='.$c['config']['RECOVERY']['token'].'</a><br>
                          </p>
                          <p>
                            '.t('Regards,').'<br>
                            '.t('The team Framasoft').'
                          </p>
                        </div>
                      </body>
                    </html>';

                    mail($c['config']['RECOVERY']['email'], t('New password').' - '.$c['title'], $body, $headers, '');
                }
                // → write token and/or attempt in data/config.php
                writeConfig($c, true);

            /* Step 2 : check token */
            } elseif (isset($_GET['token'])) {

                if( empty($_GET['token'])
                    || empty($GLOBALS['config']['RECOVERY']['token'])
                    || $_GET['token'] != $GLOBALS['config']['RECOVERY']['token']
                  ) {

                    $data['login'] .= '<p class="alert alert-warning">Le lien pour la récupération du mot de passe n’est pas valide ou a expiré.</p>';

                } else {

                    $data['login'] .= '</div></div>
        <div id="rPwd"><form method="POST" class="col-md-6 col-md-offset-3 ombre clearfix" action="?do=login&recover&password" name="changepasswordform" id="changepasswordform">
            <input type="hidden" name="token" value="'.getToken().'">
            <h2>'. t('Change password') .'</h2>
            <div class="form-group clearfix">
                <label  class="col-sm-4" for="setpassword">'.t('New password:').'</label>
                <div class="col-sm-8">
                    <input type="password" id="setpassword" name="setpassword" class="form-control">
                </div>
            </div>
            <button type="submit" value="Recover" class="btn btn-success pull-right">'. t('Save password') .'</button>
        </form></div><div><div>';

                }

            /* Step 3 : check passwords */
            } else {

                if( isset($_GET['password']) && isset($_POST['setpassword']) && isset($_POST['token']) ) {
                    unset($_SESSION['tokens'][$_POST['token']]);
                    $c = $GLOBALS;
                    $data['login'] .= '<p class="alert alert-success">Le nouveau mot de passe a bien été enregistré. Vous pouvez à présent vous connecter.</p>';

                    // on écrit le hash et réinit le token dans la config
                    $c['hash'] = sha1($_POST['setpassword'].$c['login'].$c['salt']);
                    $c['config']['RECOVERY']['token'] = '';
                    writeConfig($c, true);
                }
            }
        }
        $data['login'] .= '</div></div>
        <div id="rForm" style="display:none;"><form method="POST" class="col-md-6 col-md-offset-3 ombre clearfix" action="?do=login&recover'.
            ((isset($_GET['source']) && $_GET['source']=='bookmarklet') ? '&source=bookmarklet' : '').'" name="recoverform" id="recoverform">
            <input type="hidden" name="token" value="'.getToken().'">
            <h2>'. t('Password recovery') .'</h2>
            <div class="form-group">
                <label for="email">'. t('Email') .'</label>
                <input type="email" id="email" name="email" placeholder="@" class="form-control">
            </div>
            <a href="javascript:void(0);" class="btn btn-default pull-left" onclick="recoveryCancel();return false;">'. t('Cancel') .'</a>
            <button type="submit" value="Recover" class="btn btn-warning pull-right">'. t('Recover') .'</button>
        </form></div></div>';
    }
    return $data;
}

function hook_recovery_render_footer($data)
{
    if ($data['_LOGGEDIN_'] === false && ($data['_PAGE_'] == Router::$PAGE_LOGIN) && !empty($GLOBALS['config']['RECOVERY']['email'])) {
        $data['js_files'][] = PluginManager::$PLUGINS_PATH . '/recovery/recovery.js.php';
    }
    return $data;
}

function hook_recovery_render_tools($data)
{
    // Save new email in config
    if( isset($_POST['saveEmail']) && isset($_POST['token']) && tokenOk($_POST['token']) ) {
        unset($_SESSION['tokens'][$_POST['token']]);

        if (!empty($_POST['email']) && strstr($_POST['email'], '@')) {

            $c = $GLOBALS;
            $c['config']['RECOVERY']['email'] = $GLOBALS['config']['RECOVERY']['email'] = $_POST['email'];
            $c['config']['RECOVERY']['token'] = '';
            // Save new config
            try {
                writeConfig($c, true);
                // Then redirect on the page
                if(isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], 'do=tools')) {
                    header('Location: '.$_SERVER['HTTP_REFERER']);
                } else {
                    header('Location: '.$ShaarliRoot);
                }
            }
            catch(Exception $e) {
                error_log(
                    'ERROR while writing config file after configuration update.' . PHP_EOL .
                    $e->getMessage()
                );
                exit;
            }
            exit;
        }
        exit;
    } else {

        // Display menu and modal
        $html = file_get_contents(PluginManager::$PLUGINS_PATH .'/recovery/tools.html');

        if(!function_exists('translate')) {
            function translate($matches) { return t($matches[1]); }
        }
        $html = preg_replace_callback('/\{\'(.+)\'\|t\}/i', 'translate', $html );
        if (isset ($GLOBALS['config']['RECOVERY']['email'])) {
            $html = str_replace('value=""', 'value="' . htmlentities($GLOBALS['config']['RECOVERY']['email']) . '"', $html);
            $html = str_replace('{$token}', getToken(), $html);
        }

        $data['tools_plugin'][] = $html;

        return $data;

    }
}







