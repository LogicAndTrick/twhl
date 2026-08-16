import TomSelect from 'tom-select';

type AutocompleteOptions = {
    model_name: string;
    url: string;
    multiple: boolean;
    paginated: boolean;
    count: number;
    minimumInputLength: 0;
    id: string;
    text: string;
    clearable: boolean;
    placeholder: string;
};

type PaginatedResult<T = any> = {
    items: T[];
    page: number;
    pages: number;
    total: number;
};

function renderItem(data: Record<string, any>, opts: AutocompleteOptions) {
    const el = document.createElement('div');
    el.textContent = data[opts.text] || data['name'] || data['text'] || data[opts.id];
    if (data['avatar_inline']) {
        const img = document.createElement('img');
        img.classList.add('me-1');
        img.src = data['avatar_inline'];
        img.alt = 'Avatar';
        el.prepend(img);
    }
    return el;
}

function getAutocompleteOptions(instance: TomSelect): AutocompleteOptions {
    return (instance as any).__autocomplete_options__;
}

export function getAutocompleteInstance(element: HTMLSelectElement): TomSelect | null {
    return (element as any).__tomselect_instance__;
}

export async function setAutocompleteValue(element: HTMLSelectElement, value: string | string[] | number | number[]) {
    const inst = getAutocompleteInstance(element);
    if (!inst) return;

    if (!value) {
        inst.clear();
        return;
    }
    const valArr = (Array.isArray(value) ? value : [value]).filter((x) => !!x).map((x) => x.toString());

    if (valArr.some((x) => !inst.options[x])) {
        // some values must be loaded from the server
        const opts = getAutocompleteOptions(inst);
        inst.wrapper.classList.add('loading');

        const url = new URL(opts.url);
        url.searchParams.set('id', valArr.join(','));
        url.searchParams.set('count', '100');
        const json = await fetch(url).then((x) => x.json());
        inst.addOptions(json);

        inst.wrapper.classList.remove('loading');
    }

    inst.setValue(valArr);
}

export async function setAutocompleteData(element: HTMLSelectElement, data: any | any[]) {
    const inst = getAutocompleteInstance(element);
    if (!inst) return;

    const dataArr = (Array.isArray(data) ? data : [data]).filter((x) => !!x);
    inst.clear();
    inst.addOptions(dataArr);
    inst.setValue(dataArr.map((x) => x[inst.settings.valueField]));
}

export function getAutocompleteValue(element: HTMLSelectElement) {
    return getAutocompleteInstance(element)?.getValue();
}

export function getAutocompleteData(element: HTMLSelectElement, multiple: boolean = false) {
    const inst = getAutocompleteInstance(element);
    if (!inst) return multiple ? [] : null;

    const val = inst.getValue();
    const valArr = Array.isArray(val) ? val : [val].filter((x) => !!x);
    const mapped = valArr.map((x) => {
        const v = inst.options[x];
        if (!v) return {};

        const r: Record<string, any> = {};
        for (const k in v) {
            // remove props that start with $, they are internal to tom-select
            if (k.startsWith('$')) continue;
            r[k] = v[k];
        }
        return r;
    });
    return multiple ? mapped : mapped[0];
}

export function initAutocomplete(element: HTMLSelectElement, options: Partial<AutocompleteOptions>) {
    const opts: AutocompleteOptions = {
        url: '',
        model_name: '',
        multiple: element.multiple,
        paginated: true,
        count: 10,
        minimumInputLength: 0,
        id: 'id',
        text: 'name',
        clearable: false,
        placeholder: '',
        ...options,
    };

    const currentPageInformation = {
        element: null as HTMLElement | null,
        paging: false,
        page: 1,
        pages: 1,
        total: 1,
    };

    let plugins: Record<string, any> = {};
    if (opts.clearable) plugins['clear_button'] = {};
    if (opts.multiple) plugins['remove_button'] = {};

    function changePage(self: TomSelect, offset: number) {
        let page = currentPageInformation.page + offset;
        if (page < 1) page = 1;
        if (page > currentPageInformation.pages) page = currentPageInformation.pages;
        currentPageInformation.page = page;
        self.load(self.inputValue()); // start a loading operation
        self.refreshOptions(); // early refresh so we show the loading spinner
    }

    let initialising: Promise<any> = Promise.resolve();

    const ts = new TomSelect(element, {
        openOnFocus: true,
        shouldOpen: true,
        plugins,
        closeAfterSelect: true,
        placeholder: opts.placeholder || 'Select item...',
        valueField: opts.id,
        labelField: opts.text,
        searchField: [],
        preload: true,

        async onInitialize(this: TomSelect) {
            const val = this.getValue();
            const ids = (Array.isArray(val) ? val : [val]).filter((x) => !!x);
            if (!ids.length) return;

            const url = new URL(opts.url);
            url.searchParams.set('id', ids.join(','));
            url.searchParams.set('count', '100');
            initialising = fetch(url).then((x) => x.json());
            const json = await initialising;
            this.addOptions(json);
        },
        shouldLoad(filter: string) {
            return filter.length >= opts.minimumInputLength;
        },
        async load(this: TomSelect, filter: string, callback: any) {
            await initialising;
            const url = new URL(opts.url + (opts.paginated ? '/paged' : ''));
            url.searchParams.set('filter', filter);
            if (opts.paginated) {
                url.searchParams.set('page', currentPageInformation.page.toString());
                url.searchParams.set('count', opts.count.toString());
            }
            const resp = await fetch(url);
            const json = await resp.json();
            this.clearOptions(); // clear previous search results

            if (opts.paginated) {
                const res = json as PaginatedResult;
                currentPageInformation.paging = true;
                currentPageInformation.page = res.page;
                currentPageInformation.pages = res.pages;
                currentPageInformation.total = res.total;
                callback(res.items);
            } else {
                currentPageInformation.paging = false;
                callback(json);
            }
        },
        onLoad(this: TomSelect) {
            const pi = currentPageInformation;
            if (!pi.paging) {
                pi.element?.remove();
            } else {
                if (!pi.element) {
                    pi.element = document.createElement('div');
                    pi.element.className = 'tom-pagination p-2 border-top d-flex';

                    const prev = document.createElement('button');
                    prev.className = 'btn btn-secondary btn-sm';
                    prev.type = 'button';
                    prev.textContent = '<';
                    prev.addEventListener('click', () => changePage(this, -1));

                    const next = document.createElement('button');
                    next.className = 'btn btn-secondary btn-sm';
                    next.type = 'button';
                    next.textContent = '>';
                    next.addEventListener('click', () => changePage(this, 1));

                    const mid = document.createElement('span');
                    mid.className = 'flex-fill text-center';

                    pi.element.replaceChildren(prev, mid, next);
                    this.dropdown.append(pi.element);
                }

                const [prev, mid, next] = pi.element.children as unknown as [HTMLButtonElement, HTMLSpanElement, HTMLButtonElement];
                prev.disabled = pi.page <= 1;
                next.disabled = pi.page >= pi.pages;
                mid.textContent = `Page ${pi.page} of ${pi.pages}`;
            }
        },
        render: {
            option(data: Record<string, any>) {
                return renderItem(data, opts);
            },
            item(data: Record<string, any>) {
                return renderItem(data, opts);
            },
        },
    });

    Object.assign(ts, {
        __autocomplete_options__: opts,
    });

    Object.assign(element, {
        __tomselect_instance__: ts,
    });

    return ts;
}

Object.assign(window, {
    initAutocomplete,
    getAutocompleteData,
    getAutocompleteInstance,
    getAutocompleteValue,
    setAutocompleteValue,
});
