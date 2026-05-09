(function () {
    var element = document.getElementById('kt_apexcharts_3');

    if (!element) {
        return;
    }

    var height = parseInt(KTUtil.css(element, 'height'));
    var labelColor = KTUtil.getCssVariableValue('--kt-gray-500') || '#A1A5B7';
    var borderColor = KTUtil.getCssVariableValue('--kt-gray-200') || '#EFF2F5';
    var baseColor = KTUtil.getCssVariableValue('--kt-info') || '#009EF7';
    var lightColor = KTUtil.getCssVariableValue('--kt-info-light') || '#F1FAFF';

    var travelRequestStatistics = {
        day: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
        value: [0, 5, 10, 1, 2, 2, 3, 3, 4, 3, 0, 5, 6, 5, 7, 5, 0, 8, 0, 5, 10, 5, 11, 1, 0, 5, 3, 8, 0, 0, 3, 1, 1],
        month: 'November',
        year: '2025'
    };

    var options = {
        series: [{
            name: '',
            data: travelRequestStatistics['value']
        }],
        chart: {
            fontFamily: 'inherit',
            type: 'area',
            height: height,
            toolbar: {
                show: false
            }
        },
        plotOptions: {

        },
        legend: {
            show: false
        },
        dataLabels: {
            enabled: false
        },
        fill: {
            type: 'solid',
            opacity: 1
        },
        stroke: {
            curve: 'smooth',
            show: true,
            width: 3,
            colors: [baseColor]
        },
        xaxis: {
            categories: travelRequestStatistics['day'],
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: '12px'
                }
            },
            crosshairs: {
                position: 'front',
                stroke: {
                    color: baseColor,
                    width: 1,
                    dashArray: 3
                }
            },
            tooltip: {
                enabled: true,
                formatter: undefined,
                offsetY: 0,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: '12px'
                }
            }
        },
        states: {
            normal: {
                filter: {
                    type: 'none',
                    value: 0
                }
            },
            hover: {
                filter: {
                    type: 'none',
                    value: 0
                }
            },
            active: {
                allowMultipleDataPointsSelection: false,
                filter: {
                    type: 'none',
                    value: 0
                }
            }
        },
        tooltip: {
            style: {
                fontSize: '12px'
            },
            x: {
                formatter: function (val) {
                    return val + ' ' + travelRequestStatistics['month'] + ' ' + travelRequestStatistics['year']
                }
            },
            y: {
                formatter: function (val) {
                    return val + ' Pengajuan'
                }
            }
        },
        colors: [lightColor],
        grid: {
            borderColor: borderColor,
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        markers: {
            strokeColor: baseColor,
            strokeWidth: 3
        }
    };

    var chart = new ApexCharts(element, options);
    chart.render();
})();