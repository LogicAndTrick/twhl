import { CodeDiff } from 'v-code-diff';
import { createApp } from 'vue';

type WikiRevision = {
    content_html: string;
    content_plain: string;
    content_text: string;
    created_at: string;
    id: number;
    is_active: number;
    message: string;
    object_id: number;
    slug: string;
    title: string;
    user_id: number;
    wiki_revision_metas: WikiRevisionMeta[];
};

type WikiRevisionMeta = {
    id: number;
    key: string;
    revision_id: number;
    value: string;
};

async function getRevision(id: number) {
    const resp = await fetch(window.urls.wiki.get_revisions + '?expand=wiki_revision_metas&id=' + id);
    const revs = (await resp.json()) as WikiRevision[];
    return revs[0];
}

function extractText(rev: WikiRevision) {
    const meta = rev.wiki_revision_metas;
    let str = '';
    for (let i = 0; i < meta.length; i++) {
        const key = meta[i].key,
            val = meta[i].value;
        switch (key) {
            case 'w':
                str += '[META] Image Width: ' + val + '\n';
                break;
            case 'h':
                str += '[META] Image Height: ' + val + '\n';
                break;
            case 's':
                str += '[META] File Size: ' + val + '\n';
                break;
            case 'u':
                str += '[META] Upload ID: ' + val + '\n';
                break;
        }
    }
    str += rev.content_text;
    return str;
}

(window as any).attachWikiRevisionDiff = async function (host: Element, id1: number, id2: number) {
    host.replaceChildren('Loading...');

    const [rev1, rev2] = await Promise.all([getRevision(id1), getRevision(id2)]);
    const oldString = extractText(rev1);
    const newString = extractText(rev2);
    const outputFormat = 'side-by-side';
    const context = 3;
    const noDiffLineFeed = true;
    const filename = `Revision ${rev1.id}`;
    const newFilename = `Revision ${rev2.id}`;

    const app = createApp(CodeDiff, { oldString, newString, outputFormat, context, noDiffLineFeed, filename, newFilename });
    app.mount(host);
};
