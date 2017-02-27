
// If n = 1, return 'unit', else return 'units'
var pl = function(num, unit) {
    num = Math.floor(num); return num + ' ' + unit + (num == 1 ? '' : 's');
};

// Print a readable time (e.g. '20 seconds ago', '2 days ago')
function readableTime(ts) {
    var z = Date.now() - ts,
        s = z / 1000,
        i = s / 60,
        h = i / 60,
        d = h / 24,
        w = d / 7,
        m = d / 30,
        y = d / 365;
    if (s < 10) return 'just now';
    return (
            y >= 1 ? pl(y, 'year')
          : m >= 1 ? pl(m, 'month')
          : w >= 1 ? pl(w, 'week')
          : d >= 1 ? pl(d, 'day')
          : h >= 1 ? pl(h, 'hour')
          : i >= 1 ? pl(i, 'minute')
          :          pl(s, 'second')
    ) + ' ago';
}

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
    $(document).on('click', '.spoiler', function (e) {
        e.stopPropagation();
        $(e.currentTarget).toggleClass('on');
    });
});