'use strict';

$(function() {
  $(".myselect").select2();

  $(window).scroll(function () {
    var sticky = $('.sidebar'),
      scroll = $(window).scrollTop();
    if (scroll >= 500) sticky.addClass('fixed');
    else sticky.removeClass('fixed');
  });
});


