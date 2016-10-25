<?php
$ShaarliRoot = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));

require_once $ShaarliRoot . '/data/config.php';
// I18n
if(!in_array('i18n',$GLOBALS['config']['ENABLED_PLUGINS']) && !function_exists(t)) {
  function t($txt) { return $txt; }
} else {
  require_once $ShaarliRoot . '/plugins/i18n/i18n.php';
}
?>
var recovery = document.getElementById('recovery');
var rMsg = document.getElementById('rMsg');
var rForm = document.getElementById('rForm');
var rPwd = document.getElementById('rPwd');

if( recovery !== 'undefined' && rMsg !== 'undefined' && rForm !== 'undefined' && document.loginform !== 'undefined' ) {

    // Show/Hide rForm and loginform
    function recoveryForm() {
        document.loginform.style= 'display:none;';
        rForm.style= 'display:block;';
    }

    function recoveryCancel() {
        document.loginform.style= 'display:block;';
        rForm.style= 'display:none;';
    }

    // Show alert warning/danger/success
    rMsg.style= "display:block;";
    document.loginform.parentNode.insertBefore(recovery, document.loginform);

    // Recovery process = keep the rForm visible
    if(/login&recover/i.test(window.location.href)) {

        recoveryForm();

        // Check token send by email = if ok → show the change password form
        if(/recover\&token/i.test(window.location.href) && rPwd !== 'undefined') {
            rForm.style= 'display:none;';
            document.loginform.parentNode.insertBefore(rPwd, this.nextSibling);
        }

        // Password changed !
        if(/recover&password/i.test(window.location.href)) {
            recoveryCancel();
        }
    }

    var rLink = document.createElement('p');
        rLink.className = 'help-block text-right';
        rLink.id = 'rLink';
        rLink.innerHTML = '<a href="javascript:void(0);" onclick="recoveryForm();return false;" class="text-muted"><?php echo t('I lost my password'); ?></a>';

        document.loginform.password.parentNode.insertBefore(rLink, this.nextSibling)
}

