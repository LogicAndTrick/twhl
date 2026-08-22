import Cookies from 'js-cookie';

import { filteredEventListener } from '../lib/utils';
import { ReactionType } from '../types';
import { openTooltip, closeTooltip } from './tooltip';

type ReactionUser = {
    avatar_inline: string;
    name: string;
};
type Reaction = {
    entity_type: string;
    entity_id: number;
    user_id: number;
    reaction_type_id: number;
    user: ReactionUser;
};

type ReactionGroup = {
    reactions: Reaction[];
    type: ReactionType;
    active: boolean;
};

const plusReactionType: ReactionType = {
    id: -1,
    is_image: false,
    name: 'Add reaction',
    orderindex: -999,
    value: '☺',
};

const MAX_TOOLTIP_USERNAMES = 5;

class ReactionHandler {
    element: HTMLElement;
    entityType: string;
    entityId: number;
    reactions: Reaction[];
    userId: number | null;
    expanded: boolean = false;
    loading: number | null = null;

    public get reactionGroups(): Array<ReactionGroup> {
        const groups: Record<number, ReactionGroup> = {};
        for (const r of this.reactions) {
            if (!groups[r.reaction_type_id]) {
                const type = window.all_reaction_types.find((x) => x.id == r.reaction_type_id);
                if (!type) continue;

                groups[r.reaction_type_id] = {
                    active: false,
                    reactions: [],
                    type,
                };
            }
            groups[r.reaction_type_id].reactions.push(r);
            if (r.user_id == this.userId) groups[r.reaction_type_id].active = true;
        }

        const list = Object.values(groups);
        list.sort((a, b) => a.type.orderindex - b.type.orderindex);
        return list;
    }

    public get unusedReactions(): Array<ReactionType> {
        return window.all_reaction_types.filter((x) => {
            return !this.reactions.some((y) => y.reaction_type_id == x.id);
        });
    }

    constructor(element: HTMLElement, data: Reaction[]) {
        this.element = element;
        this.reactions = data;
        this.entityType = element.dataset.reactionType!;
        this.entityId = parseInt(element.dataset.reactionEntityId!, 10);
        this.userId = element.dataset.userId ? parseInt(element.dataset.userId, 10) : null;
        this.setup();
    }

    public destroy() {
        closeTooltip();
    }

    private _addEventListener(event: keyof HTMLElementEventMap, selector: string, callback: (event: Event, element: HTMLElement) => void) {
        filteredEventListener(this.element, event, selector, (event, el) => {
            if (!(el instanceof HTMLElement)) return;
            callback.call(this, event, el);
        });
    }

    private async _clickReactionHandler(event: Event, el: HTMLElement) {
        if (!el.dataset.reactionTypeId || this.loading) return;
        const id = parseInt(el.dataset.reactionTypeId, 10);
        await this.react(id);
    }

    private _clickExpandHandler() {
        if (this.loading) return;
        this.expanded = !this.expanded;
        this.element.classList.toggle('expanded', this.expanded);
    }

    private _openTooltip(event: Event, el: HTMLElement) {
        const content = this._getTooltipContent(el);
        openTooltip(el, content);
    }

    private _getTooltipContent(el: HTMLElement): string | Element {
        if (el.classList.contains('expand-button')) {
            return 'Add reaction...';
        } else if (el.classList.contains('react-button')) {
            if (!el.dataset.reactionTypeId) return '';

            const id = parseInt(el.dataset.reactionTypeId, 10);
            const type = window.all_reaction_types.find((x) => x.id === id);
            if (!type) return '';

            const els: HTMLElement[] = [];

            const nameEl = document.createElement('div');
            if (type.is_image) {
                const imageEl = document.createElement('img');
                imageEl.src = window.urls.images.smiley_folder + '/' + type.value;
                imageEl.style.maxHeight = '1rem';
                imageEl.style.maxWidth = '1rem';
                nameEl.append(imageEl, ' ' + type.name);
            } else {
                nameEl.textContent = type.value + ' ' + type.name;
            }
            els.push(nameEl);

            let reactions = this.reactions.filter((x) => x.reaction_type_id === id);
            if (reactions.length) {
                const hrEl = document.createElement('hr');
                hrEl.classList.add('my-2');
                els.push(hrEl);

                const namesEl = document.createElement('div');
                els.push(namesEl);

                // put the current user first, if they are in the list
                const me = reactions.find((x) => x.user_id == this.userId);
                reactions = (me ? [me] : []).concat(reactions.filter((x) => x.user_id != this.userId));

                let i: number;
                for (i = 0; i < reactions.length && i < MAX_TOOLTIP_USERNAMES; i++) {
                    const r = reactions[i];

                    const imgEl = document.createElement('img');
                    imgEl.src = r.user.avatar_inline;

                    const spanEl = document.createElement('span');
                    spanEl.classList.add('name');
                    if (r.user_id == this.userId) spanEl.classList.add('you');
                    spanEl.textContent = r.user.name;

                    const avatarEl = document.createElement('div');
                    avatarEl.classList.add('avatar', 'inline', 'd-block', 'text-start');
                    avatarEl.append(imgEl, spanEl);

                    namesEl.append(avatarEl);
                }
                if (reactions.length > MAX_TOOLTIP_USERNAMES) {
                    const num = reactions.length - MAX_TOOLTIP_USERNAMES;
                    const moreEl = document.createElement('div');
                    moreEl.textContent = `+ ${num} more`;
                    namesEl.append(moreEl);
                }
            }

            const tipEl = document.createElement('div');
            tipEl.replaceChildren(...els);
            return tipEl;
        } else {
            return '';
        }
    }

    private setup() {
        this._addEventListener('click', '.react-button', this._clickReactionHandler);
        this._addEventListener('click', '.expand-button', this._clickExpandHandler);
        this._addEventListener('mouseover', '.has-tooltip', this._openTooltip);
        this._addEventListener('focusin', '.has-tooltip', this._openTooltip);
        this._addEventListener('mouseout', '.has-tooltip', closeTooltip);
        this._addEventListener('focusout', '.has-tooltip', closeTooltip);

        this.render();
    }

    private async react(reaction_type_id: number) {
        if (this.loading !== null || !this.userId) return;

        const existing = this.reactions.find(
            (x) => x.entity_id == this.entityId && x.entity_type == this.entityType && x.reaction_type_id == reaction_type_id && x.user_id == this.userId,
        );
        const url = existing ? window.urls.reaction.remove : window.urls.reaction.add;

        // replace the reaction icon with a loading spinner
        this.loading = reaction_type_id;
        this.element.classList.remove('interactive');
        const btnImg = this.element.querySelector(`.reaction[data-reaction-type-id="${reaction_type_id}"] .img`);
        if (btnImg) btnImg.innerHTML = '<span class="fa fa-circle-o-notch fa-spin"></span>';

        try {
            // post to the server
            const resp = await fetch(url, {
                method: 'post',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN')!,
                },
                body: JSON.stringify({
                    type: this.entityType,
                    id: this.entityId,
                    react: reaction_type_id,
                }),
            });
            if (!resp.ok) return;

            // the returned data is the list of reactions of this type only - not of other types
            const data = await resp.json();
            this.reactions = this.reactions.filter((x) => x.reaction_type_id != reaction_type_id).concat(data);
            this.expanded = false;
        } catch (e) {
            console.error(e); // if we fail, then its not the end of the world
        } finally {
            this.render();
            this.loading = null;
            this.element.classList.add('interactive');
        }
    }

    private _renderType(type: ReactionType): HTMLElement {
        const el = document.createElement('span');
        el.classList.add('img');
        if (type.is_image) {
            const i = document.createElement('img');
            i.src = window.urls.images.smiley_folder + '/' + type.value;
            el.append(i);
        } else {
            el.innerText = type.value;
        }
        return el;
    }

    private _renderOuter(type: ReactionType, active: boolean, children: Element[]): HTMLElement {
        const reaEl = document.createElement('div');
        reaEl.classList.add('reaction');
        if (active) reaEl.classList.add('active');
        reaEl.dataset.reactionTypeId = type.id.toString();
        reaEl.append(...children);
        return reaEl;
    }

    private render() {
        const els: HTMLElement[] = [];

        // reactions that exist on the post
        for (const rg of this.reactionGroups) {
            if (rg.reactions.length === 0) continue;

            const imgEl = this._renderType(rg.type);

            const numEl = document.createElement('span');
            numEl.classList.add('num');
            numEl.textContent = rg.reactions.length.toString();

            const reaEl = this._renderOuter(rg.type, rg.active, [imgEl, numEl]);
            reaEl.classList.add('react-button', 'has-tooltip');

            els.push(reaEl);
        }

        if (this.userId) {
            // new reactions that can be added
            const unused = this.unusedReactions;

            // don't render the + button if all reactions are used already
            if (unused.length) {
                // + button
                const plusImgEl = this._renderType(plusReactionType);
                const plusNumEl = document.createElement('span');
                plusNumEl.classList.add('num');
                plusNumEl.innerHTML = '<span class="fa fa-plus"></span>';
                const plusEl = this._renderOuter(plusReactionType, false, [plusImgEl, plusNumEl]);
                plusEl.classList.add('expand-button', 'has-tooltip');
                els.push(plusEl);

                // reaction buttons
                for (const rt of unused) {
                    const imgEl = this._renderType(rt);
                    const reaEl = this._renderOuter(rt, false, [imgEl]);
                    reaEl.classList.add('react-button', 'expanded-only', 'has-tooltip');
                    els.push(reaEl);
                }
            }
        }

        closeTooltip();
        this.element.replaceChildren(...els);
        this.element.classList.toggle('expanded', this.expanded);
        this.element.classList.toggle('interactive', !!this.userId);
        this.element.classList.toggle('empty', els.length == 0);
    }
}

function setupReactions(element: HTMLElement, data: Reaction[]) {
    (element as any).__reaction_handler = new ReactionHandler(element, data);
}

/*
 * I compress the users data to reduce excess traffic.
 * Users are packed into minimal json objects with small key names and
 * image path substitutions as they have high repetition.
 * Reactions are compressed into packed strings of `user_id|reaction_type_id`
 * as these values are numbers or single ascii characters and therefore safe
 * from user tampering.
 */
type CompressedUser = {
    i: number;
    n: string;
    a: string;
};
type CompressedReactionsData = {
    repl: Record<string, string>;
    us: CompressedUser[];
    rs: string[];
};

function parseCompressedReactionData(jsonText: string, entity_type: string, entity_id: number): Reaction[] | null {
    const parsed = JSON.parse(jsonText) as CompressedReactionsData;
    if (!parsed || typeof parsed.repl != 'object' || !Array.isArray(parsed.us) || !Array.isArray(parsed.rs)) return null;

    const repl = (s: string): string => {
        for (let k in parsed.repl) {
            s = s.replaceAll(parsed.repl[k], k);
        }
        return s;
    };

    const users: Record<number, ReactionUser> = {};
    for (const u of parsed.us) {
        users[u.i] = { name: u.n, avatar_inline: repl(u.a) };
    }

    const reactions: Reaction[] = [];
    for (const r of parsed.rs) {
        const [user_id, reaction_type_id] = r.split('|').map((x) => parseInt(x, 10));
        const user = users[user_id];
        if (!user) continue;

        reactions.push({ entity_type, entity_id, user_id, reaction_type_id, user });
    }
    return reactions;
}

document.addEventListener('DOMContentLoaded', () => {
    if (!window.all_reaction_types) return;

    document.querySelectorAll('[data-reaction-type][data-reaction-entity-id]').forEach((el) => {
        if (!(el instanceof HTMLElement)) return;
        if (!el.dataset.reactionsDataId || !el.dataset.reactionType || !el.dataset.reactionEntityId) return;

        const entity_type = el.dataset.reactionType;
        const entity_id = parseInt(el.dataset.reactionEntityId, 10);
        if (!entity_type || !entity_id) return;

        const dataScript = document.getElementById(el.dataset.reactionsDataId);
        if (!dataScript || dataScript.tagName !== 'SCRIPT' || !dataScript.textContent) return;

        try {
            const data = parseCompressedReactionData(dataScript.textContent, entity_type, entity_id);
            if (!data) return;
            setupReactions(el, data);
        } catch {
            // failed to parse the data, we don't do anything in this case.
        }
    });
});
