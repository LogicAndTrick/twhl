(function($) {
    $.fn.autocomplete = function(config) {

        config = $.extend({
            multiple: false,
            paginated: true,
            count: 10,
            minimumInputLength: 0,
            id: 'id',
            text: 'name',
            clearable: false,
            placeholder: ''
        }, config);

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
                    url: config.url,
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
                    return item[config.text] || item.name || item.text || item[config.id];
                },
                templateSelection: function(item) {
                    return item[config.text] || item.name || item.text || item[config.id];
                },
                dataAdapter: dataApi,
                resultsAdapter: config.paginated ? resultsAdapter : null
            });
        });
    };
}(jQuery));
$.fn.select2.amd.define('select2/data-api', [
  'jquery',
  'select2/utils',
  'select2/data/ajax'
], function ($, Utils, AjaxAdapter) {

    function ApiAdapter ($element, options) {
        this.cache = [];
        ApiAdapter.__super__.constructor.call(this, $element, options);
    }
    Utils.Extend(ApiAdapter, AjaxAdapter);

    ApiAdapter.prototype.current = function(callback) {
        var ids = [];
        var result = [];
        var self = this;
        this.$element.find(':selected').each(function () {
            var $option = $(this);
            var option = self.item($option);
            var id = parseInt(option.id, 10);
            if (id > 0) {
                if (self.cache[id]) result.push(self.cache[id]);
                else ids.push(id);
            }
        });

        if (!ids.length) {
            callback(result);
            return;
        }

        var options = $.extend({ type: 'GET' }, this.ajaxOptions);
        if (typeof options.url === 'function') options.url = options.url({});
        if (typeof options.data === 'function') options.data = options.data({});

        options.data.id = ids.join(',');
        options.data.all = true;

        options.transport(options, function (data) {
            var results = self.processResults({items:data}, {});
            for (var i = 0; i < data.length; i++) {
                self.cache[data[i].id] = data[i];
                result.push(data);
            }
            callback(data);
        });
    };

    ApiAdapter.prototype.query = function (params, callback) {
        var self = this;
        var wrapper = function(data) {
            for (var i = 0; i < data.results.length; i++) {
                self.cache[data.results[i].id] = data.results[i];
            }
            callback.apply(this, arguments);
        };
        ApiAdapter.__super__.query.call(this, params, wrapper);
    };

    return ApiAdapter;

});
$.fn.select2.amd.define('select2/pagination', [
  'jquery'
], function ($) {


    function Select2Pagination(decorated, $element, options, dataAdapter) {
        this.lastParams = {};
        this.lastResults = {};
        decorated.call(this, $element, options, dataAdapter);
        this.$pagination = this.createPaginationButtons();
        this.loading = false;
    }

    Select2Pagination.prototype.append = function (decorated, data) {
        this.$pagination.remove();
        this.loading = false;
        this.lastResults = data;

        decorated.call(this, data);

        this.updatePagination();
        this.$results.append(this.$pagination);
    };

    Select2Pagination.prototype.bind = function (decorated, container, $container) {
        var self = this;

        decorated.call(this, container, $container);

        container.on('query', function (params) {
            self.lastParams = params;
            self.loading = true;
        });

        container.on('query:append', function (params) {
            self.lastParams = params;
            self.loading = true;
        });

        // var self = this;
        this.$results.on('click', '.select2-pagination-prev', function () {
            self.changePage(-1);
        });
        this.$results.on('click', '.select2-pagination-next', function () {
            self.changePage(+1);
        });
    };

    Select2Pagination.prototype.changePage = function (decorated, offset) {
        if (this.loading) return;

        this.loading = true;
        var params = $.extend({}, {page: 1}, this.lastParams);
        if (this.lastResults && this.lastResults.page) params.page = this.lastResults.page;
        params.page += offset;
        this.trigger('query', params);
    };

    Select2Pagination.prototype.formatInformation = function(decorated, parameters) {
        return 'Page ' + parameters.page + ' of ' + parameters.pages;
    };

    Select2Pagination.prototype.updatePagination = function() {
        this.$pagination.find('.select2-pagination-prev').toggle(this.lastResults.page > 1);
        this.$pagination.find('.select2-pagination-next').toggle(this.lastResults.page < this.lastResults.pages);
        this.$pagination.find('.select2-pagination-info').html(this.formatInformation(this.lastResults));
    };

    Select2Pagination.prototype.createPaginationButtons = function() {
        var $item = $('<li class="select2-results__option select2-pagination" role="treeitem"></li>'),
            $prev = $('<span class="select2-pagination-prev">&lt;</span>'),
            $next = $('<span class="select2-pagination-next">&gt;</span>'),
            $info = $('<span class="select2-pagination-info"></span>');
        $item.append($prev);
        $item.append($next);
        $item.append($info);

        return $item;
    };

    return Select2Pagination;

});
$(document).on('click', '.video-content .uninitialised', function(event) {
    var $t = $(this),
      ytid = $t.data('youtube-id'),
       url = 'https://www.youtube.com/embed/' + ytid + '?autoplay=1&rel=0',
     frame = $('<iframe></iframe>').attr({ src: url, frameborder: 0, allowfullscreen: ''}).addClass('caption-body');
    $t.replaceWith(frame);
});
//# sourceMappingURL=all.js.map