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
            base.draw = function(target, dataTable, options, rawData) {

                /**
                 * In order to represent multiple columns/rows appropriately we need to recreate the dataTable
                 * so that we can draw multiple pie charts. Each row should represent a chart, with the first column
                 * being the chart's label.
                 */

                for (var i = 0; i < rawData.rows.length; i++) {

                    dataTable = new google.visualization.DataTable();

                    options.title = rawData.rows[i][0];

                    dataTable.addColumn('string', 'Statement');
                    dataTable.addColumn('number', 'Value');

                    for (var y = 0; y < rawData.columns.length; y++) {
                        if (y !== 0) {
                            dataTable.addRow([rawData.columns[y][1], rawData.rows[i][y]]);
                        }
                    }

                    //  Draw the chart
                    var chartCanvas = $('<div>');
                    target.append(chartCanvas);

                    var chart = new google.visualization.PieChart(chartCanvas.get(0));
                    chart.draw(dataTable, options);
                }
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
            base.draw = function(target, dataTable, options, rawData) {
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
            base.draw = function(target, dataTable, options, rawData) {
                var chart = new google.visualization.ColumnChart(target.get(0));
                chart.draw(dataTable, options);
            };
        }
    }
};
