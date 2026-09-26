export type ReactionType = {
    id: number;
    name: string;
    is_image: boolean;
    value: string;
    orderindex: number;
};

declare global {
    interface Window {
        csrfToken: string;
        all_reaction_types: ReactionType[];
        urls: {
            embed: {
                vault: string;
                vault_screenshot: string;
                game_icon: string;
            };
            view: {
                user: string;
                vault: string;
            };
            images: {
                no_screenshot_320: string;
                no_screenshot_640: string;
                smiley_folder: string;
            };
            api: {
                image_upload: string;
                format: string;
            };
            vault: {
                save_screenshot_order: string;
                get_screenshots: string;
                delete_screenshot: string;
            };
            wiki: {
                page: string;
                formatting_guide: string;
                book_info: string;
                get_revisions: string;
            };
            reaction: {
                add: string;
                remove: string;
            };
        };
    }
}
