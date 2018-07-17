
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

// https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding#The_Unicode_Problem
function b64EncodeUnicode(str) {
    // first we use encodeURIComponent to get percent-encoded UTF-8,
    // then we convert the percent encodings into raw bytes which
    // can be fed into btoa.
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
        function toSolidBytes(match, p1) {
            return String.fromCharCode('0x' + p1);
    }));
}

function b64DecodeUnicode(str) {
    // Going backwards: from bytestream, to percent-encoding, to original string.
    return decodeURIComponent(atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
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