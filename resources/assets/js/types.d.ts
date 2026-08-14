declare global {
    interface Window {
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
            wiki: {
                page: string;
                formatting_guide: string;
                book_info: string;
                get_revisions: string;
            };
        };
    }
}

export {};
