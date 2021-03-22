'use strict';

import '../sass/admin.scss';

import Stats from './components/Stats.js';
import Charts from './components/Charts.js';

(function() {
    window.NAILS.ADMIN.registerPlugin(
        'nails/module-survey',
        'Stats',
        function(controller) {
            return new Stats(
                controller,
                (new Charts()).get()
            );
        }
    );
})();
