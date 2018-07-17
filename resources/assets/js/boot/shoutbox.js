/*
 * TWHL Shoutbox - Because there's not enough useless JavaScript in the world yet
 */
;

// Increment this to clear everyone's cached local storage
var TWHL_SHOUTBOX_DATA_FORMAT_VERSION = '1';

var probably_twhl = (/(?:\b|\/\/|^)twhl\.info(?:\b|\/|$)/i);
var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
};
var readableTime = window.readableTime;

var window_template =
        '<div :class="\'shoutbox \' + classes" @click="state = \'open\'">' +
            '<h1>' +
                '<span v-if="newMessage" class="new-message-icon fa fa-comment"></span> ' +
                'Shoutbox <span v-if="loading" class="refresh-icon fa fa-refresh"></span>' +
                '<a href="#" class="minimise-button" @click.prevent.stop="state = \'closed\'"><span class="fa fa-caret-down"></span></a>' +
                '<a href="#" class="expand-button" @click.prevent.stop="state = \'open\'"><span class="fa fa-caret-up"></span></a>' +
            '<a href="#" v-if="position !== \'left\'" class="minimise-button position-button" @click.prevent.stop="position = \'left\'"><span class="fa fa-caret-left"></span></a>' +
            '<a href="#" v-if="position !== \'right\'" class="minimise-button position-button" @click.prevent.stop="position = \'right\'"><span class="fa fa-caret-right"></span></a>' +
            '</h1>' +
            '<ul class="shouts">' +
                '<li v-if="!shouts.length" class="shout inactive">' +
                    'Loading...' +
                '</li>' +
                '<li v-else v-for="s in shouts" class="shout">' +
                    '<span class="avatar"><a :href="s.user.url"><img :src="s.user.avatar_small" :alt="s.user.name" /></a></span>' +
                    '<span class="message">' +
                        '<span class="time" :title="s.date">{{formatTime(s.created)}}</span>' +
                        '<button v-if="moderator" class="btn btn-secondary btn-xxs delete" @click="beginDelete(s)">D</button>' +
                        '<button v-if="moderator" class="btn btn-secondary btn-xxs edit" @click="beginEdit(s)">E</button>' +
                        '<span class="user"><a :href="s.user.url">{{s.user.name}}</a></span>' +
                        '<span class="text" v-html="s.formatted_content" /> ' +
                    '</span>' +
                '</li>' +
            '</ul>' +
            '<div class="error" v-if="errorMessage"">' +
                '<span class="fa fa-remove"></span><span class="message">{{errorMessage}}</span>' +
            '</div>' +
            '<form method="get" @submit.prevent="save()" v-if="active">' +
                '<div class="input-group">' +
                    '<input :disabled="deleting" type="text" maxlength="250" class="form-control input-sm" placeholder="Type here" v-model="text">' +
                    '<span class="input-group-btn">' +
                        '<button :disabled="loading" v-if="editing" class="btn btn-info btn-sm edit-button" type="submit" @click="save()">Edit</button>' +
                        '<button :disabled="loading" v-if="deleting" class="btn btn-danger btn-sm delete-button" type="submit" @click="save()">Delete</button>' +
                        '<button :disabled="loading" v-if="editing || deleting" class="btn btn-secondary btn-sm cancel-button" type="button" @click="cancelEdit()"><span class="fa fa-remove"></span></button>' +
                        '<button :disabled="loading" v-if="!editing && !deleting" class="btn btn-primary btn-sm shout-button" type="submit" @click="save()">Shout!</button>' +
                    '</span>' +
                '</div>' +
            '</form>' +
            '<form v-else class="inactive">Log in to add shouts of your own</form>' +
        '</div>';

var shoutbox = new Vue({
    template: window_template,
    data: {
        // options
        url: '',
        userUrl: '',
        active: false,
        moderator: false,

        // store
        loading: true,
        store: null,
        lastUpdate: 0,
        lastId: 0,
        lastSeen: 0,

        // positioning
        state: 'default',
        position: 'right',

        // interaction
        editing: false,
        deleting: false,
        text: '',
        interval: null,
        errorMessage: null
    },
    mounted: function() {
        this.loadStorage();
        this.loadCookie();
        this.scrollToEnd();
        this.fetch(true);
        this.interval = setInterval(this.fetch, 60 * 1000);
    },
    destroyed: function() {
        clearInterval(this.interval);
    },
    computed: {
        urls: function() {
            return {
                get: template(this.url, { action: '/from' }),
                post: template(this.url, { action: '' }),
                edit: template(this.url, { action: '' }),
                delete: template(this.url, { action: '' })
            };
        },
        shouts: function() {
            return this.store || [];
        },
        classes: function () {
            var cls = [];
            if (this.editing) cls.push('editing');
            if (this.deleting) cls.push('deleting');
            if (this.moderator) cls.push('moderator');
            if (this.loading) cls.push('refreshing');
            if (this.newMessage) cls.push('new');
            cls.push('position-' + this.position);
            cls.push('state-' + this.state);
            return cls.join(' ');
        },
        newMessage: function() {
            return false;
        }
    },
    methods: {
        loadStorage: function() {
            try {
                var version = localStorage.getItem('shoutbox.version') || '0';
                if (version === TWHL_SHOUTBOX_DATA_FORMAT_VERSION) {
                    this.store = JSON.parse(localStorage.getItem('shoutbox.store')) || [];
                    this.lastUpdate = parseInt(localStorage.getItem('shoutbox.lastUpdate'), 10) || 0;
                    this.lastId = parseInt(localStorage.getItem('shoutbox.lastId'), 10) || 0;
                    this.lastSeen = parseInt(localStorage.getItem('shoutbox.lastSeen'), 10) || 0;
                    this.loading = false;
                }
            } catch (ex) {
                this.store = [];
                this.lastUpdate = this.lastId = this.lastSeen = 0;
            }
        },
        saveStorage: function() {
            localStorage.setItem('shoutbox.version', TWHL_SHOUTBOX_DATA_FORMAT_VERSION);
            localStorage.setItem('shoutbox.store', JSON.stringify(this.store));
            localStorage.setItem('shoutbox.lastUpdate', this.lastUpdate);
            localStorage.setItem('shoutbox.lastId', this.lastId);
            localStorage.setItem('shoutbox.lastSeen', this.lastSeen);
        },
        loadCookie: function() {
            try {
                var c = JSON.parse($.cookie('shoutbox.settings'));
                if (c.state === 'open' || c.state === 'closed') this.state = c.state;
                if (c.position === 'left' || c.position === 'right') this.position = c.position;
            } catch (ex) {
                //
            }
        },
        saveCookie: function() {
            $.cookie('shoutbox.settings', JSON.stringify({ state: this.state, position: this.position }), { expires: 365, path: '/' });
        },
        fetch: function(full) {
            var self = this;
            var timestamp = full === true ? 0 : Math.floor(this.lastUpdate / 1000);
            this.refreshing = true;
            $.get(this.urls.get, { timestamp: timestamp }, function(data) {
                self.updateStore(data, full);
            });
        },
        updateStore: function(arr, full) {
            var obj, i;

            var scroll = this.getScroll();

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
                obj.formatted_content = this.format(obj.content);
                obj.time = Date.parse(obj.created_at.replace(/ /ig, 'T') + 'Z');
                obj.date = new Date(obj.time).toLocaleString();
                obj.user.url = template(this.userUrl, { id: obj.user.id });
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

            this.loading = this.refreshing = false;
            this.saveStorage();

            if (scroll >= 0.98 || full) this.scrollToEnd();
        },
        escapeHtml: function(string) {
            return String(string).replace(/[&<>"'\/]/g, function (s) {
                return entityMap[s];
            });
        },
        format: function(content) {
            var self = this;


            // Linkify links but hide the html in base64 so they don't get encoded
            content = Autolinker.link(content, {
                replaceFn : function(match) {
                    var tag = this.getTagBuilder().build(match);
                    tag.setInnerHtml(self.escapeHtml(tag.getInnerHtml())); // Escape the link text
                    if (probably_twhl.test(tag.getAttr('href'))) delete tag.attrs['target'];
                    var str = tag.toAnchorString();
                    return '\0\u9998'+b64EncodeUnicode(str).replace(/\//ig,'-')+'\u9999\0'; // B64 encode the whole thing, replace slashes as they'll be encoded later
                }
            });

            // Escape any sneaky html
            content = this.escapeHtml(content);

            // Decode the base64 links so we're good again
            content = content.replace(/\u0000\u9998([\s\S]*?)\u9999\u0000/g, function(match, b64) {
                return b64DecodeUnicode(b64.replace(/-/ig,'/'));
            });

            return content;
        },
        formatTime: function (time) {
            return readableTime(time);
        },
        save: function () {
            var content = this.text;
            if (!content || !this.active) return;

            this.text = '';
            this.loading = true;

            var url = this.editing ? this.urls.edit
                    : this.deleting ? this.urls.delete
                    : this.urls.post;
            var method = this.editing ? 'PUT'
                    : this.deleting ? 'DELETE'
                    : 'POST';
            var id = this.editing ? this.editing.id
                    : this.deleting ? this.deleting.id
                    : null;
            var full = this.deleting;

            if (!this.editing && !this.deleting) this.scrollToEnd();

            var self = this;
            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({ text: content, id: id })
            }).fail(function(req) {
                self.loading = false;
                self.errorMessage = req.responseJSON.text[0];
                self.focus();
            }).done(function() {
                self.cancelEdit();
                self.fetch(!!full);
            });
        },
        beginEdit: function (shout) {
            this.deleting = false;
            this.editing = shout;
            this.text = shout.content;
            this.focus();
        },
        beginDelete: function (shout) {
            this.deleting = shout;
            this.editing = false;
            this.text = shout.content;
            this.focus();
        },
        cancelEdit: function () {
            this.editing = this.deleting = false;
            this.text = '';
        },
        focus: function() {
            this.$nextTick(function () {
                $('input', this.$el).focus();
            });
        },
        scrollToEnd: function () {
            this.$nextTick(function () {
                this.setScroll(1);
            });
        },
        getScroll: function() {
            var el = $('.shouts', this.$el).get(0);
            if (!el) return 1;
            return el.scrollTop / (el.scrollHeight - el.offsetHeight);
        },
        setScroll: function(s) {
            var el = $('.shouts', this.$el).get(0);
            if (!el) return;
            el.scrollTop = s * (el.scrollHeight - el.offsetHeight);
        }
    },
    watch: {
        state: function () {
            this.saveCookie();
        },
        position: function () {
            this.saveCookie();
        }
    }

});

window.initShoutbox = function(options) {
    shoutbox.url = options.url;
    shoutbox.userUrl = options.userUrl;
    shoutbox.active = options.active;
    shoutbox.moderator = options.moderator;
    shoutbox.$mount(document.getElementById('shoutbox-container'));
};
