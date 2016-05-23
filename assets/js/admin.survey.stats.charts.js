/* globals google */
window.NAILS.ModuleSurvey = {
    'charts': {
        'pie': function() {

            var base = this;

            /**
             * The human readable package name
             * @type {String}
             */
            base.label = 'Pie Chart';

            /**
             *
             * The packages the Google loader should load
             * @type {Array}
             */
            base.packages = ['corechart'];

            /**
             * Renders the chart
             * @return {Void}
             */
            base.draw = function(target, dataTable, options) {
                options.is3D = true;
                var chart = new google.visualization.PieChart(target.get(0));
                chart.draw(dataTable, options);
            };
        },
        'bar': function() {

            var base = this;

            /**
             * The human readable package name
             * @type {String}
             */
            base.label = 'Bar Chart';

            /**
             *
             * The packages the Google loader should load
             * @type {Array}
             */
            base.packages = ['corechart'];

            /**
             * Renders the chart
             * @return {Void}
             */
            base.draw = function(target, dataTable, options) {
                var chart = new google.visualization.BarChart(target.get(0));
                chart.draw(dataTable, options);
            };
        },
        'column': function() {

            var base = this;

            /**
             * The human readable package name
             * @type {String}
             */
            base.label = 'Column Chart';

            /**
             *
             * The packages the Google loader should load
             * @type {Array}
             */
            base.packages = ['corechart'];

            /**
             * Renders the chart
             * @return {Void}
             */
            base.draw = function(target, dataTable, options) {
                var chart = new google.visualization.ColumnChart(target.get(0));
                chart.draw(dataTable, options);
            };
        }
    }
};
