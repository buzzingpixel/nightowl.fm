/* eslint-disable no-new */

// import Analytics from './SetUp/Analytics.js';
import ConfirmSubmit from './Components/ConfirmSubmit.js';
import Events from './Events.js';
import FileUploadField from './Components/FileUploadField.js';
import Flatpickr from './Components/Flatpickr.js';
import LoadAxios from './SetUp/LoadAxios.js';
import MarkdownTextArea from './Components/MarkdownTextArea.js';
import SetGlobalData from './SetUp/SetGlobalData.js';
import Selects from './Components/Selects.js';
import SimpleTable from './Components/SimpleTable.js';

// Setup
Events();
LoadAxios();
SetGlobalData();
// Analytics();
window.Methods.FileUploadField = FileUploadField;
window.Methods.MarkdownTextArea = MarkdownTextArea;
window.Methods.SimpleTable = SimpleTable;

// Components

// Selects
const selectEls = document.querySelectorAll('[ref="select"]');
if (selectEls.length > 0) {
    new Selects(selectEls);
}

// Confirm Submit
const forms = document.querySelectorAll('[ref="ConfirmSubmit"]');
forms.forEach((el) => {
    new ConfirmSubmit(el);
});

// Flatpickr
const flatpickrEls = document.querySelectorAll(
    'input[type="date"], input[type="datetime-local"]',
);
if (flatpickrEls.length > 0) {
    new Flatpickr(flatpickrEls);
}
