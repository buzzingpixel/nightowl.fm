export default class {
    constructor (el) {
        const submitActionText = el.dataset.submitActionText || 'Submit';
        const cancelActionText = el.dataset.cancelActionText || 'Cancel';

        this.bypass = false;

        this.el = el;

        this.template = document
            .querySelector('[ref="confirmSubmitModalTemplate"]')
            .innerHTML
            .replace(/{{submitActionText}}/g, submitActionText)
            .replace(/{{cancelActionText}}/g, cancelActionText);

        el.addEventListener('submit', (e) => {
            this.checkSubmit(e);
        });
    }

    checkSubmit (e) {
        if (this.bypass === true) {
            return;
        }

        e.preventDefault();

        document.body.insertAdjacentHTML(
            'afterbegin',
            this.template,
        );

        const modal = document.querySelector(['#confirmModal']);

        modal.querySelector('[ref="modalConfirmButton"]')
            .addEventListener('click', () => {
                this.bypass = true;

                this.el.submit();
            });

        modal.querySelector('[ref="modalCancelButton"]')
            .addEventListener('click', () => {
                modal.remove();
            });
    }
}
