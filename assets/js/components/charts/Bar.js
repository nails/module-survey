class Bar {

    /**
     * The human readable package name
     * @returns {string}
     */
    getLabel() {
        return 'Bar Chart';
    }

    /**
     * The packages the Google loader should load
     * @returns {string[]}
     */
    getPackages() {
        return ['corechart'];
    }

    /**
     * Renders the chart
     * @return {Void}
     */
    draw(target, printTarget, dataTable, options) {

        //  Instantiate the chart
        var chart = new google.visualization.BarChart(target.get(0));

        //  Draw the chart and the printable chart
        chart.draw(dataTable, options);
        printTarget.attr('src', chart.getImageURI());
    };
}

export default Bar;
