/* globals google */
var _ADMIN_SURVEY_STATS;
_ADMIN_SURVEY_STATS = function(surveyId)
{
    /**
     * Avoid scope issues in callbacks and anonymous functions by referring to `this` as `base`
     * @type {Object}
     */
    var base = this;

    // --------------------------------------------------------------------------

    base.surveyId = surveyId;

    // --------------------------------------------------------------------------

    base.charts = [];

    // --------------------------------------------------------------------------

    /**
     * Construct the class
     * @return {Void}
     */
    base.__construct = function() {

        //  Set up charts
        //  Instanciate all defined charts
        for (var key in window.NAILS.ModuleSurvey.charts) {
            if (window.NAILS.ModuleSurvey.charts.hasOwnProperty(key)) {
                base.charts.push(new window.NAILS.ModuleSurvey.charts[key]());
            }
        }

        //  Set up dropdown selectors
        $('.js-field .js-chart-type select').each(function() {
            $(this).empty();
            for (var i = 0; i < base.charts.length; i++) {
                $(this).append($('<option>').val(i).text(base.charts[i].label)).trigger('change');
            }
        });

        //  Work out all the packages we need to load from Google
        var packages = [];
        for (var i = 0; i < base.charts.length; i++) {
            $.merge(packages, base.charts[i].packages);
        }

        //  Filter out duplicates
        packages = packages.filter(function(el, index, arr) {
            return index === arr.indexOf(el);
        });

        google.charts.load('current', {packages: packages});
        google.charts.setOnLoadCallback(function() {

            //  Go...!
            base.loadStats();
        });

        //  Bind UI
        $('.js-response input').on('click', function() {
            base.loadStats();
        });
        $('.js-chart-type select').on('change', function() {
            base.drawChart(
                $(this).closest('.js-field').find('.js-chart-target'),
                $(this).val()
            );
        });

        $('.js-hide-respondees').on('click', function() {

            //  Fix the width of the inner content
            $('.js-respondees > *').each(function() {
                $(this).attr('style', 'width:' + $(this).outerWidth() + 'px!important;');
            });

            //  Animate out
            $('.js-respondees, .js-stats').addClass('respondees-hidden');

            //  Redraw charts
            //  @todo
        });

        $('.js-show-respondees').on('click', function() {

            //  Animate in
            $('.js-respondees, .js-stats').removeClass('respondees-hidden');

            //  Remove the fixed width
            setTimeout(function() {
                $('.js-respondees > *').removeAttr('style');
            }, 500);

            //  Redraw charts
            //  @todo
        });

        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Fetchs stats from the server
     * @return {Object}
     */
    base.loadStats = function() {

        //  Get response IDs
        var responseIds = [];
        $('.js-response input:checked').each(function() {
            responseIds.push(parseInt($(this).val(), 10));
        });

        if (responseIds.length > 0) {

            $('.js-field').each(function() {

                //  Show loading and hide errors
                var field   = $(this);
                var loader  = field.find('.js-loading');
                var error   = field.find('.js-error');
                var targets = field.find('.js-targets');
                error.addClass('hidden');

                //  If the request is returned quick enough then don't show the loader (bit jumpy)
                var loaderTimeout = setTimeout(function() { loader.removeClass('hidden'); }, 250);

                //  Get chart & text data
                $.ajax({
                    'url': window.SITE_URL + 'api/survey/survey/stats',
                    'data': {
                        'survey_id': base.surveyId,
                        'field_id': field.data('id'),
                        'response_ids': responseIds.join(',')
                    }
                })
                .always(function() {
                    clearTimeout(loaderTimeout);
                    loader.addClass('hidden');
                })
                .done(function(data) {

                    var chartTarget = field.find('.js-chart-target');
                    var textTarget  = field.find('.js-text-target');

                    //  Populate
                    field.find('.js-response-count').text(data.response_count);
                    chartTarget.data('chart-data', data.data.chart);

                    //  Show targets
                    targets.removeClass('hidden');

                    //  Draw charts
                    var chartType    = field.find('.js-chart-type');
                    var chartTypeVal = chartType.find('select').val().toUpperCase();

                    if (data.data.chart.rows[chartTypeVal].length > 0) {

                        base.drawChart(
                            chartTarget,
                            chartTypeVal
                        );

                        chartType.removeClass('hidden');
                        chartTarget.removeClass('hidden');

                    } else {

                        chartType.addClass('hidden');
                        chartTarget.addClass('hidden');
                    }

                    //  Render text portion of the responses
                    textTarget.empty();
                    if (data.data.text.length > 0) {

                        for (var i = 0; i < data.data.text.length; i++) {
                            var li = $('<li>').text(data.data.text[i]);
                            textTarget.append(li);
                        }
                        textTarget.removeClass('hidden');

                    } else {

                        textTarget.addClass('hidden');
                    }
                })
                .error(function(data) {

                    var _data;
                    try {

                        _data = JSON.parse(data.responseText);

                    } catch (e) {

                        _data = {
                            'status': 500,
                            'error': 'An unknown error occurred.'
                        };
                    }

                    error.html(_data.error).removeClass('hidden');
                });
            });
        }

        return base;
    };

    // --------------------------------------------------------------------------

    /**
     * Draws a specific chart for the data
     * @return {Object}
     */
    base.drawChart = function(target, chartType) {

        // Clean slate
        target.empty();

        var chartData = target.data('chart-data');
        chartType = chartType.toUpperCase();

        // Create the data table.
        var dataTable = new google.visualization.DataTable();

        for (var i = 0; i < chartData.columns.length; i++) {
            dataTable.addColumn(
                chartData.columns[i][0],
                chartData.columns[i][1]
            );
        }
        dataTable.addRows(chartData.rows);

        //  Specify chart options
        var options = {
            'height': 400
        };

        //  Draw the chart
        var error = target.closest('.js-field').find('.js-error');

        if (base.charts[chartType]) {

            base.charts[chartType].draw(target, dataTable, options, chartData);
            error.addClass('hidden');

        } else {

            error.html('Invalid chart type.').removeClass('hidden');
        }

        return base;
    };

    // --------------------------------------------------------------------------

    return base.__construct();
};
