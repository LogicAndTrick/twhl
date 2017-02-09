(function($) {
    $.fn.autocomplete = function(options) {

        var config = $.extend({
            multiple: false,
            paginated: true,
            count: 10,
            minimumInputLength: 0,
            id: 'id',
            text: 'name',
            clearable: false,
            placeholder: ''
        }, options);

        var require = $.fn.select2.amd.require;
        var utils = require('select2/utils');
        var results = require('select2/results');
        var pagination = require('select2/pagination');
        var dataApi = require('select2/data-api');
        var resultsAdapter = utils.Decorate(results, pagination);

        return this.each(function() {
            var $el = $(this);
            $el.select2({
                ajax: {
                    unpagedUrl: config.url,
                    url: config.url + (config.paginated ? '/paged' : ''),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            filter: params.term,
                            page: params.page,
                            count: config.count
                        };
                    },
                    processResults: function (data, page) {
                        if (config.id != 'id') {
                            for (var i = 0; i < data.items.length; i++) data.items[i].id = data.items[i][config.id];
                        }
                        return {
                            results: data.items,
                            page: data.page,
                            pages: data.pages,
                            total: data.total
                        };
                    },
                    cache: true
                },
                multiple: config.multiple,
                minimumInputLength: config.minimumInputLength,
                placeholder: config.placeholder,
                allowClear: config.clearable,
                templateResult: function(item) {
                    if (item.loading) return item.text
                    var text = item[config.text] || item.name || item.text || item[config.id];
                    var res = $('<span/>').text(' ' + text);
                    if (item.avatar_inline) $('<img/>').attr({ src: item.avatar_inline, alt: 'Avatar'}).prependTo(res);
                    return res;
                },
                templateSelection: function(item) {
                    var text = item[config.text] || item.name || item.text || item[config.id];
                    var res = $('<span/>').text(' ' + text);
                    if (item.avatar_inline) $('<img/>').attr({ src: item.avatar_inline, alt: 'Avatar'}).prependTo(res);
                    return res;
                },
                dataAdapter: dataApi,
                resultsAdapter: config.paginated ? resultsAdapter : null
            });
        });
    };
}(jQuery));