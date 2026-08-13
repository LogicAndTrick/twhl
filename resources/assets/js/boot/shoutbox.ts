import { createApp } from 'vue';

import Shoutbox from '../components/Shoutbox.vue';

(window as any).initShoutbox = function (options: any) {
    const app = createApp(Shoutbox, options);
    app.mount('#shoutbox-container');
};
