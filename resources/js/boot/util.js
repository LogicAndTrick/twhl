
function set_select2(element, value) {
    var e =$(element); //.empty().append('<option value="2"></option>').val(2).trigger('change')
    if (value) {
        var opt = e.find('option[value="' + value + '"]');
        if (!opt || !opt.length) e.append('<option value="' + value + '"></option>');
    }
    e.val(value).trigger('change');
}

$(function() {
    $(document).on('click', '.stop-close', function (e) {
        e.stopPropagation();
    });
    $(document).on('click', '.nice-date', function (e) {
        e.stopPropagation();
        $(e.currentTarget).toggleClass('on');
    });
});