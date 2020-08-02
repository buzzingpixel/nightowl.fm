/* eslint-disable no-new */

// Setup
import Events from './Events.js';
import SetGlobalData from './SetUp/SetGlobalData.js';
import LoadAxios from './SetUp/LoadAxios.js';
import Analytics from './SetUp/Analytics.js';

// Setup
Events();
SetGlobalData();
LoadAxios();
Analytics();
