'use strict';
jQuery(function($) {
  $(".menu").sticky({ "topSpacing": 0, "responsiveWidth": true });

  $('.more').click(function() {
    if ($(this).hasClass('success')) {
      $(this).removeClass('success');
      return false;
    }
    else {
      $(this).addClass('success');
    }
  });
});
