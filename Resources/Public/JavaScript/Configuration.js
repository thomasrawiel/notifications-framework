import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

class Configuration {
    constructor() {
        this.addEventListener();
    }

    addEventListener() {
        document.addEventListener('click', (event) => {
            event.preventDefault();
            const updateEl = event.target.closest('.js-notification-configuration-update-ajax');
            if (updateEl) {
                this.postUpdate(updateEl);
                return;
            }

            const cacheEl = event.target.closest('.js-notification-configuration-cache-ajax');
            if(cacheEl) {
                this.postCache(cacheEl);
                return;
            }
        });
    }

    postUpdate(el) {
        const {field, value, uid, table} = el.dataset;

        this.postAjax(
            TYPO3.settings.ajaxUrls.notifications_framework_update_configuration,
            {field, value, uid, table}
        )
    }

    postCache(el) {
        const {uid} = el.dataset;
        this.postAjax(
            TYPO3.settings.ajaxUrls.notifications_framework_update_cache,
            {uid}
        )
    }

    postAjax(url, params) {
        new AjaxRequest(url)
            .post(params)
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
    }
}

export default new Configuration();
