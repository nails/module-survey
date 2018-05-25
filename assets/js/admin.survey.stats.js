/* globals google */
var _ADMIN_SURVEY_STATS;
_ADMIN_SURVEY_STATS = function(surveyId, accessToken) {
    /**
     * Avoid scope issues in callbacks and anonymous functions by referring to `this` as `base`
     * @type {Object}
     */
    var base = this;

    // --------------------------------------------------------------------------

    base.surveyId = surveyId;
    base.accessToken = accessToken;

    // --------------------------------------------------------------------------

    base.charts = [];

    // --------------------------------------------------------------------------

    /**
     * Construct the class
     * @return {Void}
     */
    base.__construct = function() {

        //  Set up charts
        //  Instantiate all defined charts
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

        $('.js-chart-target').on('draw', function() {
            base.drawChart($(this));
        });

        $('.js-chart-type select').on('change', function() {
            $(this)
                .closest('.js-field')
                .find('.js-chart-target')
                .data('chart-type', $(this).val())
                .trigger('draw');
        });

        var redrawTimeout;
        $(window).on('resize', function() {
            clearTimeout(redrawTimeout);
            redrawTimeout = setTimeout(function() {
                $('.js-chart-target').trigger('draw');
            }, 250);
        });

        $('.js-hide-respondents').on('click', function() {

            //  Fix the width of the inner content
            $('.js-respondents > *').each(function() {
                $(this).attr('style', 'width:' + $(this).outerWidth() + 'px!important;');
            });

            //  Animate out
            $('.js-respondents, .js-stats').addClass('respondents-hidden');

            //  Redraw charts
            setTimeout(function() {
                $('.js-chart-target').trigger('draw');
            }, 500);
        });

        $('.js-show-respondents').on('click', function() {

            //  Animate in
            $('.js-respondents, .js-stats').removeClass('respondents-hidden');

            //  Remove the fixed width and redraw charts
            setTimeout(function() {
                $('.js-respondents > *').removeAttr('style');
                $('.js-chart-target').trigger('draw');
            }, 500);
        });

        base.initCopyButtons();

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
                var field = $(this);
                var loader = field.find('.js-loading');
                var error = field.find('.js-error');
                var targets = field.find('.js-targets');
                error.addClass('hidden');

                //  If the request is returned quick enough then don't show the loader (bit jumpy)
                var loaderTimeout = setTimeout(function() {
                    loader.removeClass('hidden');
                }, 250);

                //  Get chart & text data
                $.ajax({
                        'url': window.SITE_URL + 'api/survey/survey/stats',
                        'data': {
                            'survey_id': base.surveyId,
                            'access_token': base.accessToken,
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
                        var textTarget = field.find('.js-text-target');
                        var chartType = field.find('.js-chart-type');
                        var chartTypeVal = chartType.find('select').val();

                        //  Populate
                        field.find('.js-response-count').text(data.response_count);
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

                            for (var i = 0; i < data.data.text.length; i++) {
                                var li = $('<li>').html(data.data.text[i]);
                                textTarget.append(li);
                            }
                            textTarget.removeClass('hidden');

                        } else {

                            textTarget.addClass('hidden');
                        }
                    })
                    .fail(function(data) {

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
    base.drawChart = function(target) {

        // Clean slate
        target.empty();

        var chartData = target.data('chart-data');
        var chartType = target.data('chart-type');

        //  Specify chart options
        var options = {
            'height': 400,
            'title': ''
        };

        //  Draw the chart
        var error = target.closest('.js-field').find('.js-error');

        if (base.charts[chartType]) {

            for (var i = 0; i < chartData.length; i++) {

                //  Generate chart target and a printable target
                var chartCanvas = $('<div>').addClass('media-screen');
                var printCanvas = $('<img>').addClass('media-print');
                target.append(chartCanvas).append(printCanvas);

                // Create the data table
                var dataTable = new google.visualization.DataTable();

                for (var x = 0; x < chartData[i].columns.length; x++) {
                    dataTable.addColumn(
                        chartData[i].columns[x][0],
                        chartData[i].columns[x][1]
                    );
                }

                dataTable.addRows(chartData[i].rows);

                //  Specify a title if there is one
                options.title = chartData[i].title || '';

                //  Draw it
                base.charts[chartType].draw(chartCanvas, printCanvas, dataTable, options, chartData);
            }
            error.addClass('hidden');

        } else {

            error.html('Invalid chart type.').removeClass('hidden');
        }

        return base;
    };

    // --------------------------------------------------------------------------

    base.initCopyButtons = function() {
        $('button.copy-link').each(function() {
            var $button = $(this);
            var client = new ZeroClipboard($button.get(0));
            client.on('ready', function() {
                client.on('aftercopy', function() {
                    $button
                        .removeClass('btn-info')
                        .addClass('success btn-success');
                    setTimeout(function() {
                        $button
                            .removeClass('success btn-success')
                            .addClass('btn-default');
                    }, 1500);
                });
            });
            client.on('error', function() {
                $button.hide();
            });
        });
    };

    // --------------------------------------------------------------------------

    return base.__construct();
};
