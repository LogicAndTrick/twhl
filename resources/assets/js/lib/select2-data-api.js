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

        if (typeof options.unpagedUrl === 'function') options.url = options.unpagedUrl({});
        else if (!!options.unpagedUrl) options.url = options.unpagedUrl;

        options.data.id = ids.join(',');
        options.data.count = 100;

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