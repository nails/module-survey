class Stats {
    constructor(adminController, charts) {

        this.adminController = adminController;
        this.charts = charts;

        this.loadGoogleLibraries(() => {
            this.init();
        })
    }

    // --------------------------------------------------------------------------

    loadGoogleLibraries(callback) {
        let script = document.createElement('script');
        script.onload = callback;
        script.src = 'https://www.gstatic.com/charts/loader.js';
        document.head.appendChild(script);
    }

    // --------------------------------------------------------------------------

    init() {
        $('.group-survey.responses, .nails-survey.stats')
            .each((index, element) => {

                let $container = $(element);

                $(element).data('instance', new Instance(
                    this.adminController,
                    this.charts,
                    $container,
                    $container.data('survey-id'),
                    $container.data('survey-token')
                ));
            });
    }
}

class Instance {

    /**
     * Construct the class
     * @return {Void}
     */
    constructor(adminController, charts, $container, surveyId, accessToken) {

        this.adminController = adminController;
        this.charts = charts;
        this.$container = $container;
        this.surveyId = surveyId;
        this.accessToken = accessToken;

        //  Set up dropdown selectors
        $('.js-field .js-chart-type select', this.$container)
            .each((index, element) => {
                $(element).empty();
                for (let i = 0; i < this.charts.length; i++) {
                    $(element)
                        .append(
                            $('<option>')
                                .val(i)
                                .text(this.charts[i].getLabel())
                        )
                        .trigger('change');
                }
            });

        //  Work out all the packages we need to load from Google
        let packages = [];
        for (let i = 0; i < this.charts.length; i++) {
            $.merge(packages, this.charts[i].getPackages());
        }

        //  Filter out duplicates
        packages = packages.filter((el, index, arr) => {
            return index === arr.indexOf(el);
        });

        google.charts.load('current', {packages: packages});
        google.charts.setOnLoadCallback(() => {
            this.loadStats();
        });

        //  Bind UI
        $('.js-response input', this.$container)
            .on('click', () => {
                this.loadStats();
            });

        $('.js-chart-target', this.$container)
            .on('draw', (e) => {
                this.drawChart($(e.currentTarget));
            });

        $('.js-chart-type select', this.$container)
            .on('change', (e) => {
                $(e.currentTarget)
                    .closest('.js-field')
                    .find('.js-chart-target')
                    .data('chart-type', $(e.currentTarget).val())
                    .trigger('draw');
            });

        let redrawTimeout;
        $(window)
            .on('resize', () => {
                clearTimeout(redrawTimeout);
                redrawTimeout = setTimeout(() => {
                    $('.js-chart-target', this.$container).trigger('draw');
                }, 250);
            });

        $('.js-hide-respondents', this.$container)
            .on('click', () => {

                //  Fix the width of the inner content
                $('.js-respondents > *', this.$container)
                    .each((index, element) => {
                        $(element)
                            .attr('style', 'width:' + $(element).outerWidth() + 'px!important;');
                    });

                //  Animate out
                $('.js-respondents, .js-stats', this.$container)
                    .addClass('respondents-hidden');

                //  Redraw charts
                setTimeout(() => {
                    $('.js-chart-target', this.$container)
                        .trigger('draw');
                }, 500);
            });

        $('.js-show-respondents', this.$container)
            .on('click', () => {

                //  Animate in
                $('.js-respondents, .js-stats', this.$container)
                    .removeClass('respondents-hidden');

                //  Remove the fixed width and redraw charts
                setTimeout(() => {
                    $('.js-respondents > *', this.$container)
                        .removeAttr('style');
                    $('.js-chart-target', this.$container)
                        .trigger('draw');
                }, 500);
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Fetchs stats from the server
     * @return {Object}
     */
    loadStats() {

        //  Get response IDs
        let responseIds = [];
        $('.js-response input:checked', this.$container)
            .each((index, element) => {
                responseIds.push(parseInt($(element).val(), 10));
            });

        if (responseIds.length > 0) {

            $('.js-field', this.$container)
                .each((index, element) => {

                    //  Show loading and hide errors
                    let field = $(element);
                    let loader = field.find('.js-loading');
                    let error = field.find('.js-error');
                    let targets = field.find('.js-targets');
                    error.addClass('hidden');

                    //  If the request is returned quick enough then don't show the loader (bit jumpy)
                    let loaderTimeout = setTimeout(() => {
                        loader.removeClass('hidden');
                    }, 250);

                    //  Get chart & text data
                    $.ajax({
                        'url': window.SITE_URL + 'api/survey/survey/stats',
                        'data': {
                            'survey_id': this.surveyId,
                            'access_token': this.accessToken,
                            'field_id': field.data('id'),
                            'response_ids': responseIds.join(',')
                        }
                    })
                        .always(() => {
                            clearTimeout(loaderTimeout);
                            loader.addClass('hidden');
                        })
                        .done((data) => {

                            let chartTarget = field.find('.js-chart-target');
                            let textTarget = field.find('.js-text-target');
                            let chartType = field.find('.js-chart-type');
                            let chartTypeVal = chartType.find('select').val();

                            //  Populate
                            field.find('.js-response-count').text(data.meta.response_count);
                            chartTarget.data('chart-data', data.data.chart);
                            chartTarget.data('chart-type', chartTypeVal);

                            //  Show targets
                            targets.removeClass('hidden');

                            //  Draw charts
                            if (data.data.chart.length > 0) {

                                chartTarget.trigger('draw');
                                chartType.removeClass('hidden');
                                chartTarget.removeClass('hidden');

                            } else {

                                chartType.addClass('hidden');
                                chartTarget.addClass('hidden');
                            }

                            //  Render text portion of the responses
                            textTarget.empty();
                            if (data.data.text.length > 0) {

                                for (let i = 0; i < data.data.text.length; i++) {
                                    let li = $('<li>').html(data.data.text[i]);
                                    textTarget.append(li);
                                }
                                textTarget.removeClass('hidden');

                            } else {
                                textTarget.addClass('hidden');
                            }
                        })
                        .fail((data) => {

                            let _data;

                            try {
                                _data = JSON.parse(data.responseText);
                            } catch (e) {
                                _data = {
                                    'status': 500,
                                    'error': 'An unknown error occurred.'
                                };
                            }

                            error
                                .html(_data.error)
                                .removeClass('hidden');
                        });
                });
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Draws a specific chart for the data
     * @return {Object}
     */
    drawChart(target) {

        // Clean slate
        target.empty();

        let chartData = target.data('chart-data');
        let chartType = target.data('chart-type');

        //  Specify chart options
        let options = {
            'height': 400,
            'title': ''
        };

        //  Draw the chart
        let error = target
            .closest('.js-field')
            .find('.js-error');

        if (this.charts[chartType]) {

            for (let i = 0; i < chartData.length; i++) {

                //  Generate chart target and a printable target
                let chartCanvas = $('<div>').addClass('media-screen');
                let printCanvas = $('<img>').addClass('media-print');
                target.append(chartCanvas).append(printCanvas);

                // Create the data table
                let dataTable = new google.visualization.DataTable();

                for (let x = 0; x < chartData[i].columns.length; x++) {
                    dataTable.addColumn(
                        chartData[i].columns[x][0],
                        chartData[i].columns[x][1]
                    );
                }

                dataTable.addRows(chartData[i].rows);

                //  Specify a title if there is one
                options.title = chartData[i].title || '';

                //  Draw it
                this.charts[chartType]
                    .draw(chartCanvas, printCanvas, dataTable, options, chartData);
            }
            error.addClass('hidden');

        } else {

            error
                .html('Invalid chart type.')
                .removeClass('hidden');
        }

        return this;
    }
}

export default Stats;
