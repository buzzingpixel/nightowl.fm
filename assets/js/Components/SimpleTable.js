/* global $ */

import Loader from '../Helpers/Loader.js';

class SimpleTable {
    constructor (model) {
        Loader.loadJs('https://code.jquery.com/jquery-3.5.1.min.js').then(() => {
            const rowTemplate = model.el.querySelector(
                '[ref="rowTemplate"]',
            );

            const addButton = model.el.querySelector('[ref="addRow"]');

            this.tableBody = model.el.querySelector('[ref="tableBody"]');

            this.rowTemplateHtml = rowTemplate.innerHTML;

            addButton.addEventListener('click', () => {
                this.addRow();
            });

            this.tableBody.addEventListener('click', (e) => {
                SimpleTable.deleteRowWatcher(e);
            });

            Loader.loadJs('/lib/velocity-animate/velocity.min.js').then(() => {
                Loader.loadJs('/lib/garnishjs/dist/garnish.min.js').then(() => {
                    this.setUpSorting();
                });
            });
        });
    }

    setUpSorting () {
        const self = this;
        const $tableBody = $(self.tableBody);
        const $existingItems = $tableBody.find('[ref="row"]');

        // eslint-disable-next-line no-new
        self.sorter = new window.Garnish.DragSort({
            // eslint-disable-next-line no-undef
            container: $tableBody,
            handle: '.js-drag-sort-handle',
            axis: window.Garnish.Y_AXIS,
            collapseDraggees: false,
            magnetStrength: 4,
            helperLagBase: 1.5,
            helperOpacity: 0.6,
        });

        $existingItems.detach();

        $existingItems.each((i, el) => {
            const $item = $(el);

            $tableBody.append($item);

            self.sorter.addItems($item);
        });
    }

    addRow () {
        const self = this;
        const $tableBody = $(self.tableBody);
        const $newRow = $(self.rowTemplateHtml);

        $tableBody.append($newRow);

        self.sorter.addItems($newRow);

        // this.tableBody.insertAdjacentHTML(
        //     'beforeend',
        //     this.rowTemplateHtml,
        // );
    }

    static deleteRowWatcher (e) {
        if (e.target.closest('[ref="deleteRow"]') === null) {
            return;
        }

        e.target.closest('[ref="row"]').remove();
    }
}

export default (model) => {
    // eslint-disable-next-line no-new
    new SimpleTable(model);
};
