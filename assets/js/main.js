/* eslint-disable no-new */

// Setup
import Events from './Events.js';
import SetGlobalData from './SetUp/SetGlobalData.js';
import LoadAxios from './SetUp/LoadAxios.js';
// import Analytics from './SetUp/Analytics.js';

// Components
import Selects from './Components/Selects.js';

// Setup
Events();
SetGlobalData();
LoadAxios();
// Analytics();

// Components

// Selects
const selectEls = document.querySelectorAll('[ref="select"]');
if (selectEls.length > 0) {
    new Selects(selectEls);
}
