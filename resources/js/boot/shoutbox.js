/*
 * TWHL Shoutbox - Because there's not enough useless JavaScript in the world yet
 */

(function($, template) {
    var opts = {
        url: '',
        userUrl: '',
        active: 'false',
        getAction: 'shouts/{id}',
        postAction: 'add',
        editAction: 'edit',
        deleteAction: 'delete'
    };

    var window_template =
            '<div class="shoutbox">' +
                '<h1>' +
                    'Shoutbox <span class="refresh-icon glyphicon glyphicon-refresh"></span>' +
                    '<a href="#" class="pin-button"><span class="glyphicon glyphicon-pushpin"></span></a>' +
                    '<a href="#" class="close-button"><span class="glyphicon glyphicon-remove"></span></a>' +
                    '<a href="#" class="minimise-button"><span class="glyphicon glyphicon-chevron-up"></span></a>' +
                '</h1>' +
                '<ul class="shouts">' +
                    '<li class="shout inactive">Loading...</li>' +
                '</ul>' +
                '<form method="get">' +
                    '<div class="input-group">' +
                        '<input type="text" size="250" class="form-control input-sm" placeholder="Type here">' +
                        '<span class="input-group-btn"><button class="btn btn-primary btn-sm" type="submit">Shout!</button></span>' +
                    '</div>' +
                '</form>' +
            '</div>';
    var shout_template =
            '<li class="shout">' +
                '<span class="time" data-stamp="{time}" title="{date}"></span>' +
                '<a href="{user.url}" class="user">{user.name}</a>' + // todo avatar
                '<span class="text">{content}</span> ' +
            '</li>';

    var readableTime = function(ts) {
        var z = Date.now() - ts,
            s = z / 1000,
            i = s / 60,
            h = i / 60,
            d = h / 24,
            w = d / 7,
            m = d / 30,
            y = d / 365;
        var pl = function(num, unit) { num = Math.floor(num); return num + ' ' + unit + (num == 1 ? '' : 's'); };
        return (
                y >= 1 ? pl(y, 'year')
              : m >= 1 ? pl(m, 'month')
              : w >= 1 ? pl(w, 'week')
              : d >= 1 ? pl(d, 'day')
              : h >= 1 ? pl(h, 'hour')
              : i >= 1 ? pl(i, 'minute')
              :         pl(s, 'second')
        ) + ' ago';
    };

    var Shoutbox = function(parent, options) {
        this.options = $.extend(opts, options);
        this.options.get = template(this.options.url, { action: this.options.getAction });
        this.options.post = template(this.options.url, { action: this.options.postAction });
        this.options.edit = template(this.options.url, { action: this.options.editAction });
        this.options.delete = template(this.options.url, { action: this.options.deleteAction });
        this.options.pinned = $.cookie('shoutbox.pinned') == 'true';
        this.el = $(parent);
        var self = this;

        try {
            this.store = JSON.parse(localStorage.getItem('shoutbox.store')) || [];
            this.lastUpdate = parseInt(localStorage.getItem('shoutbox.lastUpdate'), 10) || 0;
            this.lastId = parseInt(localStorage.getItem('shoutbox.lastId'), 10) || 0;
            this.lastSeen = parseInt(localStorage.getItem('shoutbox.lastSeen'), 10) || 0;
        } catch (ex) {
            this.store = [];
            this.lastUpdate = this.lastId = this.lastSeen = 0;
        }

        this.updateStore = function(arr) {
            var i, count = this.store.length;
            this.lastUpdate = Date.now();

            // Avoid duplicates
            var ids = {};
            this.store = this.store.concat(arr);
            var newStore = [];
            for (i = 0; i < this.store.length; i++) {
                if (ids[this.store[i].id]) continue;
                newStore.push(this.store[i]);
                ids[this.store[i].id] = true;
            }
            this.store = newStore;

            if (this.store.length > 50) this.store.splice(0, 50 - this.store.length);
            for (i = 0; i < this.store.length; i++) {
                this.lastId = Math.max(this.lastId, this.store[i].id);
            }

            localStorage.setItem('shoutbox.store', JSON.stringify(this.store));
            localStorage.setItem('shoutbox.lastUpdate', this.lastUpdate);
            localStorage.setItem('shoutbox.lastId', this.lastId);
            localStorage.setItem('shoutbox.lastSeen', this.lastSeen);
        };

        this.render = function() {
            this.shoutsContainer.empty();
            for (var i = 0; i < this.store.length; i++) {
                var obj = this.store[i];
                obj['user.name'] = obj.user.name;
                obj['user.url'] = template(this.options.userUrl, { id: obj.user.id });
                obj['time'] = Date.parse(obj.created_at.replace(/ /ig, 'T') + 'Z');
                obj['date'] = obj.created_at + 'Z';
                this.shoutsContainer.append(template(shout_template, obj));
            }
            var sc = this.shoutsContainer[0];
            sc.scrollTop = sc.scrollHeight;
            this.updateTimes();
            this.container.find('form').removeClass('loading');
            if (this.options.active == 'true') this.container.find('input, button').prop('disabled', false);

            if (this.lastSeen != this.lastId) {
                if (this.container.is('.open')) {
                    this.lastSeen = this.lastId;
                    this.container.addClass('flash');
                    setTimeout(function() { self.container.removeClass('flash'); }, 10);
                } else {
                    this.container.addClass('new');
                }
            }
        };

        this.refresh = function() {
            var url = template(this.options.get, { id: this.lastId });
            this.container.addClass('refreshing');
            $.get(url, function(data) {
                self.container.removeClass('refreshing');
                self.updateStore(data);
                self.render();
            });
        };

        this.updateTimes = function() {
            this.shoutsContainer.find('[data-stamp]').each(function() {
                var $t = $(this), stamp = $t.data('stamp');
                $t.text(readableTime(parseInt(stamp, 10)));
            });
        };

        this.post = function() {
            var input = this.container.find('input');
            var content = input.val();
            if (!content || !this.options.active) return;

            input.val('');
            this.container.find('form').addClass('loading');
            this.container.find('input,button').prop('disabled', true);
            this.container.addClass('refreshing');
            $.post(this.options.post, { text: content }, function() {
                self.refresh();
            });
        };

        this.togglePin = function() {
            $.cookie('shoutbox.pinned', this.options.pinned = !this.options.pinned, { expires: 365, path: '/' });
            this.container.toggleClass('pinned', this.options.pinned);
        };

        this.toggle = function() {
            this.container.toggleClass('open');
            this.container.removeClass('new');
            localStorage.setItem('shoutbox.lastSeen', this.lastSeen = this.lastId);
        };

        this.destroy = function() {
            this.el.data('shoutbox', null);
            clearInterval(this.interval);
            this.container.remove();
        };

        this.bind = function() {
            this.container.find('.minimise-button').on('click', function(event) {
                event.preventDefault();
                self.toggle();
            });
            this.container.find('.pin-button').on('click', function(event) {
                event.preventDefault();
                self.togglePin();
            });
            this.container.find('.close-button').on('click', function(event) {
                event.preventDefault();
                self.destroy(); // todo confirm and cookie
            });
            this.container.find('form').on('submit', function(event) {
                event.preventDefault();
                self.post();
            });
        };

        this.container = $(window_template).toggleClass('open pinned', this.options.pinned).appendTo(this.el);
        this.shoutsContainer = this.container.find('.shouts');
        if (this.options.active != 'true') this.container.find('input, button').prop('disabled', true);

        this.interval = setInterval(function() {
            self.refresh();
        }, 60 * 1000);

        this.bind();
        this.render();
        this.refresh();
    };

    $.fn.shoutbox = function(options) {
        if ($.cookie('shoutbox.removed')) return;
        $(this).data('shoutbox', new Shoutbox(this, options));
    };
})(jQuery, template);