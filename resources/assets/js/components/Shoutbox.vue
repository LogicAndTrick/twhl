<script setup lang="ts">
import { Autolinker } from 'autolinker';
import Cookies from 'js-cookie';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import { nanoTemplate } from '../lib/nano-templating';
import { b64EncodeUnicode, b64DecodeUnicode, escapeHtml, readableTime } from '../lib/utils';
/*
 * TWHL Shoutbox - Because there's not enough useless JavaScript in the world yet
 */

// Increment this to clear everyone's cached local storage
const TWHL_SHOUTBOX_DATA_FORMAT_VERSION = '1';

type User = {
    id: number;
    name: string;
    last_login_time: string;
    last_access_time: string;
    avatar_custom: number;
    avatar_file: string;
    title_custom: number;
    title_text: string;
    avatar_full: string;
    avatar_small: string;
    avatar_inline: string;
    url: string;
};

type Shout = {
    id: number;
    user_id: number;
    content: string;
    created_at: string;
    created: number;
    updated_at: string;
    updated: number;
    user: User;
    formatted_content: string;
    time: number;
    date: string;
};

// props
const props = defineProps<{
    url: string;
    userUrl: string;
    active: boolean;
    moderator: boolean;
}>();

// positioning
const state = ref<'default' | 'open' | 'closed'>('default');
const position = ref<'left' | 'right'>('right');

// interaction
const editing = ref<Shout | null>(null);
const deleting = ref<Shout | null>(null);
const text = ref('');
const errorMessage = ref(null);

let interval: number | undefined = undefined;

onMounted(() => {
    loadStorage();
    loadCookie();
    scrollToEnd();
    fetchUpdates(true);
    interval = setInterval(fetch, 60 * 1000);
});
onUnmounted(() => {
    clearInterval(interval);
});

const urls = computed(() => {
    return {
        get: nanoTemplate(props.url, { action: '/from' }),
        post: nanoTemplate(props.url, { action: '' }),
        edit: nanoTemplate(props.url, { action: '' }),
        delete: nanoTemplate(props.url, { action: '' }),
    };
});

// data store
const loading = ref(true);
const shouts = ref<Shout[]>([]);
const lastUpdate = ref(0);
const lastId = ref(0);
const lastSeen = ref(0);

async function fetchUpdates(full: boolean = false) {
    const timestamp = full === true ? 0 : Math.floor(lastUpdate.value / 1000);
    loading.value = true;
    const resp = await fetch(urls.value.get + '?timestamp=' + timestamp);
    const data = await resp.json();
    updateStore(data, full);
}

function updateStore(arr: Shout[], full: boolean) {
    let obj, i;

    const scroll = getScroll();

    if (full === true) shouts.value = [];

    // Avoid duplicates
    const ids: Record<number, number> = {};
    shouts.value = shouts.value.concat(arr);
    const newStore = [];
    for (i = 0; i < shouts.value.length; i++) {
        obj = shouts.value[i];
        hydrateShout(obj);
        if (ids[obj.id] !== undefined) {
            const orig = newStore[ids[obj.id]];
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
    shouts.value = newStore;

    if (shouts.value.length > 50) shouts.value.splice(0, 50 - shouts.value.length);
    for (i = 0; i < shouts.value.length; i++) {
        obj = shouts.value[i];
        lastId.value = Math.max(lastId.value, obj.id);
        lastUpdate.value = Math.max(lastUpdate.value, obj.updated);
    }

    loading.value = false;
    saveStorage();

    if (scroll >= 0.98 || full) scrollToEnd();
}

function hydrateShout(shout: Shout) {
    if (!shout.updated) shout.updated = Date.parse(shout.updated_at);
    if (!shout.created) shout.created = Date.parse(shout.created_at);
    shout.formatted_content = format(shout.content);
    shout.time = Date.parse(shout.created_at);
    shout.date = new Date(shout.time).toLocaleString();
    hydrateUser(shout.user);
}

function hydrateUser(user: User) {
    user.url = nanoTemplate(props.userUrl, { id: user.id });
}

function loadStorage() {
    try {
        const version = localStorage.getItem('shoutbox.version') || '0';
        if (version === TWHL_SHOUTBOX_DATA_FORMAT_VERSION) {
            shouts.value = JSON.parse(localStorage.getItem('shoutbox.store')!) || [];
            lastUpdate.value = parseInt(localStorage.getItem('shoutbox.lastUpdate')!, 10) || 0;
            lastId.value = parseInt(localStorage.getItem('shoutbox.lastId')!, 10) || 0;
            lastSeen.value = parseInt(localStorage.getItem('shoutbox.lastSeen')!, 10) || 0;
            loading.value = false;
        }
    } catch (ex) {
        shouts.value = [];
        lastUpdate.value = lastId.value = lastSeen.value = 0;
    }
}

function saveStorage() {
    localStorage.setItem('shoutbox.version', TWHL_SHOUTBOX_DATA_FORMAT_VERSION);
    localStorage.setItem('shoutbox.store', JSON.stringify(shouts.value));
    localStorage.setItem('shoutbox.lastUpdate', lastUpdate.value.toString());
    localStorage.setItem('shoutbox.lastId', lastId.value.toString());
    localStorage.setItem('shoutbox.lastSeen', lastSeen.value.toString());
}

// cookies
function loadCookie() {
    try {
        const c = JSON.parse(Cookies.get('shoutbox.settings') || '');
        if (c.state === 'open' || c.state === 'closed') state.value = c.state;
        if (c.position === 'left' || c.position === 'right') position.value = c.position;
    } catch (ex) {
        //
    }
}

function saveCookie() {
    Cookies.set('shoutbox.settings', JSON.stringify({ state: state.value, position: position.value }), { expires: 365, path: '/', sameSite: 'lax' });
}

watch([state, position], saveCookie);

// display
const classes = computed(() => {
    const cls = [];
    if (editing.value) cls.push('editing');
    if (deleting.value) cls.push('deleting');
    if (props.moderator) cls.push('moderator');
    if (loading.value) cls.push('refreshing');
    cls.push('position-' + position.value);
    cls.push('state-' + state.value);
    return cls.join(' ');
});

const probably_twhl = /(?:\b|\/\/|^)twhl\.info(?:\b|\/|$)/i;

function format(content: string): string {
    // Linkify links but hide the html in base64 so they don't get encoded
    content = content.replace(/\0/g, ''); // Replace \0 with empty string, there's no reason for them to exist
    content = Autolinker.link(content, {
        replaceFn: function (match) {
            const tag = match.buildTag();
            tag.setInnerHtml(escapeHtml(tag.getInnerHtml())); // Escape the link text
            if (probably_twhl.test(tag.getAttr('href'))) tag.setAttr('target', '');
            const str = tag.toAnchorString();
            return '\0\u9998' + b64EncodeUnicode(str).replace(/\//gi, '-') + '\u9999\0'; // B64 encode the whole thing, replace slashes as they'll be encoded later
        },
    });

    // Escape any sneaky html
    content = escapeHtml(content);

    // Decode the base64 links so we're good again
    content = content.replace(/\0\u9998([\s\S]*?)\u9999\0/g, function (match, b64) {
        return b64DecodeUnicode(b64.replace(/-/gi, '/'));
    });

    return content;
}
function formatTime(time: number): string {
    return readableTime(time);
}

// dom
const shoutsList = ref<HTMLElement>();
const textInput = ref<HTMLInputElement>();

function focus() {
    nextTick(() => {
        textInput.value?.select();
        textInput.value?.focus();
    });
}

function scrollToEnd() {
    nextTick(() => setScroll(1));
}

function getScroll(): number {
    const el = shoutsList.value;
    if (!el) return 1;
    return el.scrollTop / (el.scrollHeight - el.offsetHeight);
}

function setScroll(s: number) {
    const el = shoutsList.value;
    if (!el) return;
    el.scrollTop = s * (el.scrollHeight - el.offsetHeight);
}

// editing

function beginEdit(shout: Shout) {
    deleting.value = null;
    editing.value = shout;
    text.value = shout.content;
    focus();
}

function beginDelete(shout: any) {
    deleting.value = shout;
    editing.value = null;
    text.value = shout.content;
    focus();
}

function cancelEdit() {
    editing.value = deleting.value = null;
    text.value = '';
}

async function save() {
    const content = text.value;
    if (!content || !props.active) return;

    text.value = '';
    loading.value = true;

    const url = editing.value ? urls.value.edit : deleting.value ? urls.value.delete : urls.value.post;
    const method = editing.value ? 'PUT' : deleting.value ? 'DELETE' : 'POST';
    const id = editing.value ? editing.value.id : deleting.value ? deleting.value.id : null;
    const full = !!deleting.value;

    if (!editing.value && !deleting.value) scrollToEnd();

    const resp = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ text: content, id }),
    });
    const data = await resp.json();
    if (!resp.ok) {
        errorMessage.value = data.text[0];
    } else {
        cancelEdit();
        fetchUpdates(full);
    }
}
</script>

<template>
    <div :class="'shoutbox ' + classes" @click="state = 'open'">
        <h1>
            Shoutbox
            <span v-if="loading" class="refresh-icon fa fa-refresh"></span>
            <a href="#" class="minimise-button" @click.prevent.stop="state = 'closed'">
                <span class="fa fa-caret-down"></span>
            </a>
            <a href="#" class="expand-button" @click.prevent.stop="state = 'open'">
                <span class="fa fa-caret-up"></span>
            </a>
            <a href="#" v-if="position !== 'left'" class="minimise-button position-button" @click.prevent.stop="position = 'left'">
                <span class="fa fa-caret-left"></span>
            </a>
            <a href="#" v-if="position !== 'right'" class="minimise-button position-button" @click.prevent.stop="position = 'right'">
                <span class="fa fa-caret-right"></span>
            </a>
        </h1>
        <ul ref="shoutsList" class="shouts">
            <li v-if="!shouts.length" class="shout inactive">Loading...</li>
            <li v-else v-for="(s, i) in shouts" :key="i" class="shout">
                <span class="avatar">
                    <a :href="s.user.url"><img :src="s.user.avatar_small" :alt="s.user.name" /></a>
                </span>
                <span class="message">
                    <span class="time" :title="s.date">{{ formatTime(s.created) }}</span>
                    <button v-if="moderator" class="btn btn-outline-inverse btn-xxs delete" @click="beginDelete(s)">D</button>
                    <button v-if="moderator" class="btn btn-outline-inverse btn-xxs edit" @click="beginEdit(s)">E</button>
                    <span class="user"
                        ><a :href="s.user.url">{{ s.user.name }}</a></span
                    >
                    <span class="text" v-html="s.formatted_content" />
                </span>
            </li>
        </ul>
        <div class="error" v-if="errorMessage">
            <span class="fa fa-remove"></span><span class="message">{{ errorMessage }}</span>
        </div>
        <form method="get" @submit.prevent="save()" v-if="active">
            <div class="input-group">
                <input
                    ref="textInput"
                    :disabled="!!deleting"
                    type="text"
                    maxlength="250"
                    class="form-control input-sm"
                    placeholder="Type here"
                    v-model="text"
                />
                <button :disabled="loading" v-if="editing" class="btn btn-info btn-sm edit-button" type="submit" @click="save()">Edit</button>
                <button :disabled="loading" v-if="deleting" class="btn btn-danger btn-sm delete-button" type="submit" @click="save()">Delete</button>
                <button :disabled="loading" v-if="editing || deleting" class="btn btn-outline-inverse btn-sm cancel-button" type="button" @click="cancelEdit()">
                    <span class="fa fa-remove"></span>
                </button>
                <button :disabled="loading" v-if="!editing && !deleting" class="btn btn-primary btn-sm shout-button" type="submit" @click="save()">
                    Shout!
                </button>
            </div>
        </form>
        <form v-else class="inactive">Log in to add shouts of your own</form>
    </div>
</template>
