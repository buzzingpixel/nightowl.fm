export default class {
    constructor (formEl) {
        this.formEl = formEl;

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
            .catch(() => {
                window.location = '/cms/twitter/error';
            });
    }
}
