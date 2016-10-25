//if(/do=tools/i.test(window.location.href)) {
  jQuery(document).ready(function() {

    // Button Add Tag
    var tagsField = jQuery('#tagsAdvanced table tbody tr:last').html();
    jQuery('#addTag').on('click', function() {
      var nbTags = jQuery('#tagsAdvanced table tbody tr').length;
      jQuery('#tagsAdvanced table tbody').append( '<tr>'+tagsField.replace(/([0-9]+)/g,nbTags+1)+'</tr>' );
      jQuery('#ta-name'+nbTags+1).focus();
      // Reset values
      jQuery('#ta-name'+nbTags+1+',#ta-icon'+nbTags+1+',#ta-filter'+nbTags+1).val('');
      jQuery('#ta-pined').prop( "checked", true);
      jQuery('#ta-private').prop( "checked", false);
      jQuery('#ta_count').val(nbTags+1);
    });

    // Buttons Move Up/Down
    // CSS
    jQuery('head').append(
       '<style>'+
          '#tagsAdvanced .moveup,#tagsAdvanced .movedown {'+
            'background-color:transparent;'+
          '}'+
          '#tagsAdvanced tr td:first-child {'+
            'vertical-align:middle;'+
          '}'+
          '#tagsAdvanced tr:first-child .moveup,#tagsAdvanced tr:last-child .movedown {'+
            'visibility:hidden'+
          '}'+
        '</style>'
    );

  });

function tagsSwitch(tr,direction) {
  if(direction < 0) { // up
    a = parseInt(jQuery(tr).attr('data-order'));
    b = a-1;
  } else {
    b = parseInt(jQuery(tr).attr('data-order'));
    a = b+1;
  }

  // Switch numbers in attributes ta-name, ta-icon, ta-filter, ta-pined, ta-private
  re = new RegExp('(ta[-_]tag|name|icon|filter|pined|private)([0-9]+)', 'g');

  jQuery('#tagsAdvanced tr[data-order="'+b+'"]').html(function(){
    return jQuery(this).html().replace(re, '$1'+a);
  });

  jQuery('#tagsAdvanced tr[data-order="'+a+'"]').html(function(){
    return jQuery(this).html().replace(re, '$1'+b);
  });

  // Switch rows
  jQuery('#tagsAdvanced tr:eq('+a+')').after(jQuery('#tagsAdvanced tr:eq('+b+')'));
  // Clean data-order
  jQuery('#tagsAdvanced tr').each(function(index){
    jQuery(this).attr({
      'data-order': index,
      'id': 'ta_tag'+index
    });
  });

  return false;
}
//