/* eslint-disable no-new */

// Setup
import Events from './Events.js';
import SetGlobalData from './SetUp/SetGlobalData.js';
import LoadAxios from './SetUp/LoadAxios.js';
import MarkdownTextArea from './Components/MarkdownTextArea.js';
// import Analytics from './SetUp/Analytics.js';
import SimpleTable from './Components/SimpleTable.js';

// Components
import FileUploadField from './Components/FileUploadField.js';
import Selects from './Components/Selects.js';

// Setup
Events();
SetGlobalData();
LoadAxios();
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
