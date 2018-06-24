
$(function() {

    $('.vault-items').each(function() {
        var con = $(this);
        var hs = con.find('.horizontal-scroll');

        hs.on('scroll', $.throttle(100, function () {
            var scroll = hs[0].scrollLeft, max = hs[0].scrollWidth - hs[0].offsetWidth;
            var pct = (scroll / max) * 100;

            if (pct > 5) con.addClass('scroll-left');
            else con.removeClass('scroll-left');

            if (pct < 95) con.addClass('scroll-right');
            else con.removeClass('scroll-right');
        })).scroll();
    });

});