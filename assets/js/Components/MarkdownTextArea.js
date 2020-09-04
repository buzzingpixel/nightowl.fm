/**
 * @see https://github.com/sparksuite/simplemde-markdown-editor
 */

import Loader from '../Helpers/Loader.js';

export default (model) => {
    // Loader.loadCss(
    //     'https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css',
    // );

    Loader.loadCss('/assets/lib/simplemde.css');

    Loader.loadJs('https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js')
        .then(() => {
            // eslint-disable-next-line no-undef,no-new
            new SimpleMDE({
                element: model.el,
                forceSync: true,
                indentWithTabs: false,
            });
        });
};
