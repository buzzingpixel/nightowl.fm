/**
 * @see https://joshuajohnson.co.uk/Choices/
 * @see https://github.com/jshjohnson/Choices
 */

import Loader from '../Helpers/Loader.js';

class Selects {
    /**
     * @param {NodeList} els
     */
    constructor (els) {
        // Loader.loadCss(
        //     'https://cdn.jsdelivr.net/npm/choices.js@9.0.1/public/assets/styles/choices.min.css',
        // );

        Loader.loadCss('/assets/lib/choices.css');

        Loader.loadJs('https://cdn.jsdelivr.net/npm/choices.js@9.0.1/public/assets/scripts/choices.min.js').then(() => {
            els.forEach((el) => {
                // eslint-disable-next-line no-undef,no-new
                new Choices(el, {
                    removeItemButton: true,
                });
            });
        });
    }
}

export default Selects;
