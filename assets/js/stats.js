'use strict';

import '../sass/stats.scss';

import Stats from './components/Stats.js';
import Charts from './components/Charts.js';

(function() {
    new Stats(
        function() {
            return {
                'log': function() {
                },
                'warn': function() {
                },
                'error': function() {
                }
            }
        },
        (new Charts()).get()
    );
})();
