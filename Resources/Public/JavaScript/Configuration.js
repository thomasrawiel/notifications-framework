import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

class Configuration {
    constructor() {
        this.addEventListener();
    }

    addEventListener() {
        document.addEventListener('click', (event) => {
            const el = event.target.closest('.js-notification-configuration-ajax');
            if (!el) {
                return;
            }
            event.preventDefault();

            const {field, value, uid, table} = el.dataset;

            new AjaxRequest(TYPO3.settings.ajaxUrls.notifications_framework_update_configuration)
                .post({field, value, uid, table})
                .then(async (response) => {
                const data = await response.resolve();
                if (data && data.reload === true) {
                    if (top && top.location) {
                        top.location.href = top.location.href;
                    } else {
                        window.location.reload();
                    }
                }
            })
                .catch((error) => {
                    console.error('Request failed:', error);
                });
        });
    }
}

export default new Configuration();
