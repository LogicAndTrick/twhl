
$(function() {
    $('.comment-meta-rating').each(function() {
        var $t = $(this),
           sel = $t.find('select').css('display', 'none'),
           con = $t.find('.stars'),
            fs = con.data('full-star'),
            es = con.data('empty-star');
        for (var i = 1; i <= 5; i++) {
            con.append(
                $('<img/>').attr({ src: es, alt: 'star' }).data({ score: i }).click(function() {
                    sel.val($(this).data('score')).change();
                })
            );
        }
        con.append(
            $(' <button></button>').attr({ type: 'button' }).addClass('btn btn-outline-inverse btn-sm').text('Remove Rating').click(function() {
                sel.val(0).change();
            })
        );
        sel.on('change', function() {
            var val = $(this).val();
            con.find('img').each(function() {
                var st = $(this), idx = st.data('score');
                st.attr('src', idx <= val ? fs : es);
            });
        });
    });
});