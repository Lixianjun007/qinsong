'use strict';
jQuery(function($) {
  $('#list').click(function() {
    $(this).toggleClass('list');
    $('.products').toggleClass('across');
    return false;
  });

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