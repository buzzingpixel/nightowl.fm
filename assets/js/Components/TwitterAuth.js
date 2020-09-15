export default class {
    constructor (formEl) {
        this.formEl = formEl;

        this.errorBanner = document.querySelector(
            '[ref="errorBanner"]',
        );

        this.errorMessage = this.errorBanner.querySelector(
            '[ref="errorMessage"]',
        );

        formEl.addEventListener('submit', (e) => {
            this.submitAuth(e);
        });
    }

    submitAuth (e) {
        e.preventDefault();

        const self = this;

        const formData = new FormData(self.formEl);

        window.axios.post('/cms/ajax/twitter-auth', formData)
            .then((resp) => {
                window.location = resp.data.url;
            })
            .catch((error) => {
                // window.location = '/cms/twitter/error';

                let errorMsg = '';

                JSON.parse(error.response.data).errors.forEach((i) => {
                    if (i.code) {
                        errorMsg += `<div class="font-bold mb-2">${i.code}</div>`;
                    }

                    if (i.message) {
                        errorMsg += `<div class="mb-4">${i.message}</div>`;
                    }
                });

                self.errorMessage.innerHTML = errorMsg;

                self.errorBanner.classList.remove('hidden');
            });
    }
}
