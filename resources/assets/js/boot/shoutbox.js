/*
 * TWHL Shoutbox - Because there's not enough useless JavaScript in the world yet
 */

;(function($, template) {
    var opts = {
        url: '',
        userUrl: '',
        active: 'false',
        moderator: 'false',
        getAction: '/from',
        postAction: '',
        editAction: '',
        deleteAction: ''
    };

    var window_template =
            '<div class="shoutbox">' +
                '<h1>' +
                    'Shoutbox <span class="refresh-icon fa fa-refresh"></span>' +
                    '<a href="#" class="pin-button"><span class="fa fa-thumb-tack"></span></a>' +
                    '<a href="#" class="close-button"><span class="fa fa-remove"></span></a>' +
                    '<a href="#" class="minimise-button"><span class="fa fa-caret-up"></span></a>' +
                '</h1>' +
                '<ul class="shouts">' +
                    '<li class="shout inactive">Loading...</li>' +
                '</ul>' +
                '<div class="error"><span class="fa fa-remove"></span><span class="message"></span></div>' +
                '<form method="get">' +
                    '<div class="input-group">' +
                        '<input type="text" maxlength="250" class="form-control input-sm" placeholder="Type here">' +
                        '<span class="input-group-btn">' +
                            '<button class="btn btn-info btn-sm edit-button" type="submit">Edit</button>' +
                            '<button class="btn btn-danger btn-sm delete-button" type="submit">Delete</button>' +
                            '<button class="btn btn-secondary btn-sm cancel-button" type="button"><span class="fa fa-remove"></span></button>' +
                            '<button class="btn btn-primary btn-sm shout-button" type="submit">Shout!</button>' +
                        '</span>' +
                    '</div>' +
                '</form>' +
            '</div>';
    var shout_template =
            '<li class="shout">' +
                '<span class="avatar"><a href="{user.url}"><img src="{user.avatar}" alt="{user.name}" /></a></span>' +
                '<span class="message">' +
                    '<span class="time" data-stamp="{time}" title="{date}"></span>' +
                    '<button data-id="{id}" class="btn btn-secondary btn-xxs delete">D</button>' +
                    '<button data-id="{id}" class="btn btn-secondary btn-xxs edit">E</button>' +
                    '<span class="user"><a href="{user.url}">{user.name}</a></span>' +
                    '<span class="text">{formatted_content}</span> ' +
                '</span>' +
            '</li>';

    // If n = 1, return 'unit', else return 'units'
    var pl = function(num, unit) {
        num = Math.floor(num); return num + ' ' + unit + (num == 1 ? '' : 's');
    };
    // Print a readable time (e.g. '20 seconds ago', '2 days ago')
    var readableTime = function(ts) {
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
    };

    // Increment this to clear everyone's cached local storage
    var TWHL_SHOUTBOX_DATA_FORMAT_VERSION = '1';

    var Shoutbox = function(parent, options) {

        this.options = $.extend(opts, options);
        this.options.get = template(this.options.url, { action: this.options.getAction });
        this.options.post = template(this.options.url, { action: this.options.postAction });
        this.options.edit = template(this.options.url, { action: this.options.editAction });
        this.options.delete = template(this.options.url, { action: this.options.deleteAction });
        this.options.pinned = $.cookie('shoutbox.pinned') == 'true';
        this.el = $(parent);
        var self = this;

        this.store = [];
        this.lastUpdate = this.lastId = this.lastSeen = 0;

        try {
            var version = localStorage.getItem('shoutbox.version') || '0';
            if (version == TWHL_SHOUTBOX_DATA_FORMAT_VERSION) {
                this.store = JSON.parse(localStorage.getItem('shoutbox.store')) || [];
                this.lastUpdate = parseInt(localStorage.getItem('shoutbox.lastUpdate'), 10) || 0;
                this.lastId = parseInt(localStorage.getItem('shoutbox.lastId'), 10) || 0;
                this.lastSeen = parseInt(localStorage.getItem('shoutbox.lastSeen'), 10) || 0;
            }
        } catch (ex) {
            this.store = [];
            this.lastUpdate = this.lastId = this.lastSeen = 0;
        }

        var entityMap = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': '&quot;',
            "'": '&#39;',
            "/": '&#x2F;'
        };

        function escapeHtml(string) {
            return String(string).replace(/[&<>"'\/]/g, function (s) {
                return entityMap[s];
            });
        }

        var probably_twhl = (/(?:\b|\/\/|^)twhl\.info(?:\b|\/|$)/i);

        this.format = function(content)
        {
            // Linkify links but hide the html in base64 so they don't get encoded
            content = Autolinker.link(content, {
                replaceFn : function( autolinker, match ) {
                    var tag = window.tag = autolinker.getTagBuilder().build(match);
                    tag.setInnerHtml(escapeHtml(tag.getInnerHtml())); // Escape the link text
                    if (probably_twhl.test(tag.getAttr('href'))) delete tag.attrs['target'];
                    var str = tag.toAnchorString();
                    return '\0\u9998'+window.btoa(str).replace(/\//ig,'-')+'\u9999\0'; // B64 encode the whole thing, replace slashes as they'll be encoded later
                }
            });

            // Escape any sneaky html
            content = escapeHtml(content);

            // Decode the base64 links so we're good again
            content = content.replace(/\u0000\u9998([\s\S]*?)\u9999\u0000/g, function(match, b64) {
                return window.atob(b64.replace(/-/ig,'/'));
            });

            return content;
        };

        this.updateStore = function(arr, full) {
            var obj, i;

            if (full === true) this.store = [];

            // Avoid duplicates
            var ids = {};
            this.store = this.store.concat(arr);
            var newStore = [];
            for (i = 0; i < this.store.length; i++) {
                obj = this.store[i];
                if (!obj.updated) obj.updated = Date.parse(obj.updated_at.replace(/ /ig, 'T') + 'Z');
                if (!obj.created) obj.created = Date.parse(obj.created_at.replace(/ /ig, 'T') + 'Z');
                if (ids[obj.id] !== undefined) {
                    var orig = newStore[ids[obj.id]];
                    if (orig.updated < obj.updated) newStore[ids[obj.id]] = obj;
                } else {
                    ids[obj.id] = newStore.length;
                    newStore.push(obj);
                }
            }
            newStore.sort(function (a, b) {
                if (a.created > b.created) return 1;
                if (a.created < b.created) return -1;
                return 0;
            });
            this.store = newStore;

            if (this.store.length > 50) this.store.splice(0, 50 - this.store.length);
            for (i = 0; i < this.store.length; i++) {
                obj = this.store[i];
                this.lastId = Math.max(this.lastId, obj.id);
                this.lastUpdate = Math.max(this.lastUpdate, obj.updated);
            }

            localStorage.setItem('shoutbox.version', TWHL_SHOUTBOX_DATA_FORMAT_VERSION);
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
                obj['user.avatar'] = obj.user.avatar_small;
                obj['time'] = Date.parse(obj.created_at.replace(/ /ig, 'T') + 'Z');
                obj['date'] = new Date(obj['time']).toLocaleString();
                obj['formatted_content'] = this.format(obj.content);
                this.shoutsContainer.append(template(shout_template, obj));
            }
            var sc = this.shoutsContainer[0];
            setTimeout(function() { sc.scrollTop = sc.scrollHeight; }, 0);
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

        this.refresh = function(full) {
            var timestamp = full === true ? 0 : Math.floor(this.lastUpdate / 1000);
            this.container.addClass('refreshing');
            $.get(this.options.get, { timestamp: timestamp }, function(data) {
                self.container.removeClass('refreshing');
                self.updateStore(data, full);
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

            var url = this.editing ? this.options.edit
                    : this.deleting ? this.options.delete
                    : this.options.post;
            var method = this.editing ? 'PUT'
                    : this.deleting ? 'DELETE'
                    : 'POST';
            var full = this.deleting;

            // $.post(url, { text: content, id: this.editing || this.deleting || null })
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ text: content, id: this.editing || this.deleting || null })
            }).fail(function(req) {
                self.container.find('form').removeClass('loading');
                self.container.find('input,button').prop('disabled', false);
                self.container.removeClass('refreshing');
                self.container.find('.error').addClass('show').find('.message').text(req.responseJSON.text[0]);
                input.val(content).focus();
            }).done(function() {
                self.cancelEdit();
                self.refresh(!!full);
            });
        };

        this.beginEdit = function(id, data) {
            this.cancelEdit();
            this.editing = id;
            this.container.addClass('editing');
            this.container.find('input').val(data.content).focus();
        };

        this.beginDelete = function(id, data) {
            this.cancelEdit();
            this.deleting = id;
            this.container.addClass('deleting');
            this.container.find('input').val(data.content).focus();
        };

        this.cancelEdit = function() {
            this.editing = this.deleting = false;
            this.container.removeClass('editing deleting');
            this.container.find('input').val('');
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
            this.container.find('.error .fa').on('click', function(event) {
                self.container.find('.error').removeClass('show');
            });
            this.container.on('click', '.shout .edit', function(event) {
                var id = $(event.currentTarget).data('id');
                for (var i = 0; i < self.store.length; i++) {
                    var s = self.store[i];
                    if (s.id == id) {
                        self.beginEdit(id, s);
                        break;
                    }
                }
            });
            this.container.on('click', '.shout .delete', function(event) {
                var id = $(event.currentTarget).data('id');
                for (var i = 0; i < self.store.length; i++) {
                    var s = self.store[i];
                    if (s.id == id) {
                        self.beginDelete(id, s);
                        break;
                    }
                }
            });
            this.container.find('.cancel-button').on('click', function(event) {
                event.preventDefault();
                self.cancelEdit();
            });
        };

        this.container = $(window_template).toggleClass('open pinned', this.options.pinned).appendTo(this.el);
        this.shoutsContainer = this.container.find('.shouts');
        if (this.options.active != 'true') this.container.find('input, button').prop('disabled', true);
        if (this.options.moderator == 'true') this.container.addClass('moderator');

        this.interval = setInterval(function() {
            self.refresh();
        }, 60 * 1000);

        this.bind();
        this.render();
        this.refresh(true);
    };

    $.fn.shoutbox = function(options) {
        if ($.cookie('shoutbox.removed')) return;
        $(this).data('shoutbox', new Shoutbox(this, options));
    };
})(jQuery, template);