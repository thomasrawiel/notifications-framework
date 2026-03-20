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

            const field = el.dataset.field;
            const value = el.dataset.value;

            const url = TYPO3.settings.ajaxUrls.notifications_framework_update_configuration;

            const payload = {
                field: field,
                value: value
            };
            console.log(payload);
            new AjaxRequest(url)
                .post(payload)
                .then(async (response) => {
                    const data = await response.resolve();
                    console.log(data);
                    if (data && data.success === true) {
                        // Reload current backend module (iframe)
                        if (top && top.location) {
                            top.location.reload();
                        } else {
                            // fallback (should normally not happen in BE)
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
