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
        params.page = parseInt(params.page, 10) + offset;
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