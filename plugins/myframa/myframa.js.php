<?php
$ShaarliRoot = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));

require_once $ShaarliRoot . '/data/config.php';
// I18n
if(!in_array('i18n',$GLOBALS['config']['ENABLED_PLUGINS']) && !function_exists(t)) {
  function t($txt) { return $txt; }
} else {
  require_once $ShaarliRoot . '/plugins/i18n/i18n.php';
}

// Pré-remplissage Login
$account = explode('/', $ShaarliRoot);
?>
// Read/Write cookies (copy from Framanav)
function f$Cookie(set, name, value, time) {

  if (set == 'w') {

    time = typeof time !== 'undefined' ? time : 365*24*60*60*1000;
    var today = new Date(), expires = new Date();
    expires.setTime(today.getTime() + time);
    document.cookie = name + "=" + encodeURIComponent(value) + ";expires=" + expires.toGMTString();

  } else {

    var oRegex = new RegExp("(?:; )?" + name + "=([^;]*);?");
    if (oRegex.test(document.cookie)) {
      return decodeURIComponent(RegExp["$1"]);
    } else {
      return null;
    }

  }

}


jQuery(document).ready(function(){
  // Button Show/Hide link description
  if(/do=daily|login|searchterm|searchtag/i.test(window.location.href) || !(/=/i.test(window.location.href))) {
    var descHide = f$Cookie('r','descHide');
    var divider = (jQuery('.dropdown.paging_linksperpage ul li').length > 0) ? '<li class="divider"></li>' : '';

    jQuery('.dropdown.paging_linksperpage ul.dropdown-menu').append(
      divider+
      '<li><a id="descToggle" href="javascript:void(0);">'+
        '<i class="fa fa-fw fa-lg fa-toggle-on"></i> <?php echo t('Show links descriptions'); ?><span class="sr-only"><?php echo t('actually disabled'); ?></span>'+
      '</a></li>'
    );
    jQuery('#descToggle').click(
      function(){
        if(jQuery(this).children('i').hasClass('fa-toggle-on')) {
          jQuery('.link-description').hide();
          jQuery('#descToggle').children('i').addClass('fa-toggle-off').removeClass('fa-toggle-on');
          jQuery('#descToggle').children('.sr-only').text('<?php echo t('actually enabled'); ?>');
          f$Cookie('w','descHide',true);
        } else {
          jQuery('.link-description').show();
          jQuery('#descToggle').children('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
          jQuery('#descToggle').children('.sr-only').text('<?php echo t('actually disabled'); ?>');
          f$Cookie('w','descHide',false);
        }
      }
    );
    if(descHide) {
      jQuery('#descToggle').trigger('click');
    }

  } else {
    jQuery('.paging_linksperpage').hide();
  }

  jQuery('#configform #titleLink,#configform #updateCheck').parent().parent().hide();
  jQuery('#footer').css('visibility','hidden');

  // Styling addlink_toolbar
  jQuery('#addlink_toolbar form')
    .wrapInner('<div class="input-group"></div>')
    .addClass('col-sm-8 col-sm-offset-2');
  jQuery('#addlink_toolbar input[type="submit"]')
    .after(
    '<span class="input-group-btn"><button class="btn btn-default" value="'+jQuery('.navbar a[href="?do=addlink"]').text()+'" type="submit">'+
      '<i class="fa fa-fw fa-lg fa-plus text-success"></i> '+jQuery('.navbar a[href="?do=addlink"]').text()+
    '</button></span>')
    .remove();
  jQuery('#addlink_toolbar input[type="text"]')
    .addClass('form-control')
    .attr('placeholder','http(s)://…');

  if(/do=addlink|login/i.test(window.location.href) || !(/=/i.test(window.location.href))) {
    jQuery('.navbar a[href="?do=addlink"]').parent().hide();
  }

  // Locked plugins
  if(/do=pluginadmin/i.test(window.location.href)) {
    jQuery('#i18n, #myframa, #recovery, #tags_advanced<?php if (in_array('framanav',$GLOBALS['config']['ENABLED_PLUGINS'])) { echo ', #framanav'; } ?>')
      .attr('readonly', 'true')
      .parent().parent().find('a:contains("▲"),a:contains("▼")').remove();
      jQuery('#plugin_table a:contains("▲"):visible:first').css('visibility','hidden');
      jQuery('#plugin_table a:contains("▼"):visible:last').css('visibility','hidden');
      jQuery('input[type="checkbox"][readonly]').on("click.readonly", function(event){event.preventDefault();});
  }

  if(/\&source\=bookmarklet/i.test(window.location.href) && (jQuery('#editlinkform').length == 1) ) {
    jQuery('#editlinkform').hide();

    var editbtn  = (jQuery('[name="delete_link"]').length == 1)
                 ? '<?php echo t('Edit this link');?>'
                 : '<?php echo t('Add this link');?>';
    var editlink = (jQuery('#lf_url').val().length > 40)
                 ? jQuery('#lf_url').val().substring(0, 40)+'…'
                 : jQuery('#lf_url').val();

    jQuery('#editlinkform').before(
      '<div class="row" id="choose">'+
        '<div class="col-md-12 text-center">'+
          '<p class="text-center"><a href="javascript:void(0)" class="btn btn-warning btn-lg btn-block">'+
              '<i class="fa fa-fw fa-star"></i> '+editbtn+
              '<br><small>'+editlink+'</small>'+
          '</a></p>'+
          '<p><?php echo t('or');?></p>'+
          '<p><a href="'+window.location.href.split('?')[0]+'" class="btn btn-primary btn-lg btn-block">'+
              '<i class="fa fa-fw fa-link"></i> <?php echo t('See all my links');?>'+
          '</a></p>'+
        '</div>'+
      '</div>'
    );

    jQuery('#choose .btn-warning').on('click', function() {
      jQuery('#choose').hide();
      jQuery('#editlinkform').show();
    });
    jQuery('#choose .btn-primary').on('click', function() {
      window.open(window.location.href.split('?')[0]);
      self.close();
      return false;
    });
  }

  // Login MyFrama
  if(/do=login/i.test(window.location.href)) {
    // After registration
    if(/do=login\&first/i.test(window.location.href)) {
      jQuery('#loginform').prepend('<div class="alert alert-info text-center"><button type="button" class="close" data-dismiss="alert" aria-label="<?php echo t('Close'); ?>"><span aria-hidden="true">&times;</span></button><b><?php echo t('Your account has been successfully created. You can sign in now.'); ?></b></div>');
      jQuery('[name="returnurl"]').val('');
    }
    if(/\&source\=bookmarklet/i.test(window.location.href)) {
      jQuery('.btn-success').before(
        '<button class="btn btn-default pull-left" type="button" onclick="self.close();"><?php echo t('Cancel');?></button>'+
        '<a class="btn btn-primary pull-left" style="margin-left:5px" href="'+window.location.href.replace('u/<?php echo $account[count($account)-1] ?>/?do=login', '?')+'&cookie=unset"><?php echo t('Use an other account');?></a></p>'
      );
    }
<?php
  if($GLOBALS['login'] == $account[count($account)-1]) {
    echo "
      jQuery('#login').val('".$GLOBALS['login']."');";
  }
?>
  }
});
