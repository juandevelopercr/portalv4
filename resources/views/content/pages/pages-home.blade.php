@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection



@section('content')

<div class="row">
    <!-- Cards with charts & info -->
    <div class="col-lg-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column">
                        <div class="card-title mb-auto">
                            <h5 class="mb-0">{{ __('Users') }}</h5>
                            <p class="mb-0">{{ __('Monthly Report') }}</p>
                        </div>
                        <div class="chart-statistics">
                            <h4 class="card-title mb-0">4,230</h4>
                            <p class="text-success text-nowrap mb-0"><i class='bx bx-chevron-up bx-lg'></i>
                                +12.8%
                            </p>
                        </div>
                    </div>
                    <div id="activityChart"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column">
                        <div class="card-title mb-auto">
                            <h5 class="mb-0">{{ __('Visits') }}</h5>
                            <p class="mb-0">{{ __('Weekly Report') }}</p>
                        </div>
                        <div class="chart-statistics">
                            <h4 class="card-title mb-0">500</h4>
                            <p class="text-success text-nowrap mb-0"><i class='bx bx-chevron-up bx-lg'></i>
                                +6.8%
                            </p>
                        </div>
                    </div>
                    <div id="visitorsChart"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column">
                        <div class="card-title mb-auto">
                            <h5 class="mb-0">{{ __('Products') }}</h5>
                            <p class="mb-0">{{ __('Monthly Report') }}</p>
                        </div>
                        <div class="chart-statistics">
                            <h4 class="card-title mb-0">300</h4>
                            <p class="text-success text-nowrap mb-0"><i class='bx bx-chevron-up bx-lg'></i>
                                +5.8%
                            </p>
                        </div>
                    </div>
                    <div id="activityChart1"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ Cards with charts & info -->

<div class="row">
    <!-- Bar Chart -->
    <div class="col-12 mb-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-md-center align-items-start">
                <h5 class="card-title mb-0">{{ __('Monthly User Report') }}</h5>
                <div class="dropdown">
                    <button type="button" class="btn dropdown-toggle p-0" data-bs-toggle="dropdown"
                        aria-expanded="false"><i class="bx bx-calendar"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Today</a>
                        </li>
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Yesterday</a>
                        </li>
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Last 7
                                Days</a></li>
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Last 30
                                Days</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Current
                                Month</a></li>
                        <li><a href="javascript:void(0);" class="dropdown-item d-flex align-items-center">Last
                                Month</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div id="barChart"></div>
            </div>
        </div>
    </div>
    <!-- /Bar Chart -->
</div>
@endsection

@section('page-script')
<script>
    'use strict';
    
    window.onload = function () {
        const config = {
            colors: {
                primary: '#696cff',
                secondary: '#8592a3',
                success: '#71dd37',
                info: '#03c3ec',
                warning: '#ffab00',
                danger: '#ff3e1d',
                dark: '#233446',
                black: '#22303e',
                white: '#fff',
                cardColor: '#fff',
                bodyBg: '#f5f5f9',
                bodyColor: '#646E78',
                headingColor: '#384551',
                textMuted: '#a7acb2',
                borderColor: '#e4e6e8'
            },
            colors_label: {
                primary: '#696cff29',
                secondary: '#8592a329',
                success: '#71dd3729',
                info: '#03c3ec29',
                warning: '#ffab0029',
                danger: '#ff3e1d29',
                dark: '#181c211a'
            }
        };

        // Color constant
        const chartColors = {
            column: {
            series1: '#826af9',
            series2: '#d2b0ff',
            bg: '#f8d3ff'
            },
            donut: {
            series1: '#fee802',
            series2: '#F1F0F2',
            series3: '#826bf8',
            series4: '#3fd0bd'
            },
            area: {
            series1: '#29dac7',
            series2: '#60f2ca',
            series3: '#a5f8cd'
            },
            bar: {
            bg: '#1D9FF2'
            }
        };

        let cardColor, shadeColor, labelColor, headingColor, legendColor, borderColor;
        cardColor = config.colors.cardColor;
        labelColor = config.colors.textMuted;
        legendColor = config.colors.bodyColor;
        headingColor = config.colors.headingColor;
        borderColor = config.colors.borderColor;
        shadeColor = '';

        const activityAreaChartEl = document.querySelector('#activityChart'),
            activityAreaChartConfig = {
                chart: {
                    height: 120,
                    width: 220,
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [
                    {
                        data: [15, 22, 17, 40, 12, 35, 25]
                    }
                ],
                colors: [config.colors.success],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: shadeColor,
                        shadeIntensity: 0.8,
                        opacityFrom: 0.8,
                        opacityTo: 0.25,
                        stops: [0, 85, 100]
                    }
                },
                grid: {
                    show: false,
                    padding: {
                        top: -20,
                        bottom: -8
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            fontSize: '13px',
                            colors: labelColor
                        }
                    }
                },
                yaxis: {
                    labels: {
                        show: false
                    }
                }
            };

        if (typeof activityAreaChartEl !== 'undefined' && activityAreaChartEl !== null) {
            const activityAreaChart = new ApexCharts(activityAreaChartEl, activityAreaChartConfig);
            activityAreaChart.render();
        }



        const activityAreaChartEl1 = document.querySelector('#activityChart1'),
            activityAreaChartConfig1 = {
                chart: {
                    height: 120,
                    width: 220,
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [
                    {
                        data: [15, 22, 17, 40, 12, 35, 25]
                    }
                ],
                colors: [config.colors.success],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: shadeColor,
                        shadeIntensity: 0.8,
                        opacityFrom: 0.8,
                        opacityTo: 0.25,
                        stops: [0, 85, 100]
                    }
                },
                grid: {
                    show: false,
                    padding: {
                        top: -20,
                        bottom: -8
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    labels: {
                        style: {
                            fontSize: '13px',
                            colors: labelColor
                        }
                    }
                },
                yaxis: {
                    labels: {
                        show: false
                    }
                }
            };

        if (typeof activityAreaChartEl1 !== 'undefined' && activityAreaChartEl1 !== null) {
            const activityAreaChart1 = new ApexCharts(activityAreaChartEl1, activityAreaChartConfig1);
            activityAreaChart1.render();
        }



        // Visitor Bar Chart
        // --------------------------------------------------------------------
        const visitorBarChartEl = document.querySelector('#visitorsChart'),
            visitorBarChartConfig = {
            chart: {
                height: 120,
                width: 200,
                parentHeightOffset: 0,
                type: 'bar',
                toolbar: {
                show: false
                }
            },
            plotOptions: {
                bar: {
                barHeight: '75%',
                columnWidth: '40px',
                startingShape: 'rounded',
                endingShape: 'rounded',
                borderRadius: 5,
                distributed: true
                }
            },
            grid: {
                show: false,
                padding: {
                top: -25,
                bottom: -12
                }
            },
            colors: [
                config.colors_label.primary,
                config.colors_label.primary,
                config.colors_label.primary,
                config.colors_label.primary,
                config.colors_label.primary,
                config.colors.primary,
                config.colors_label.primary
            ],
            dataLabels: {
                enabled: false
            },
            series: [
                {
                data: [40, 95, 60, 45, 90, 50, 75]
                }
            ],
            legend: {
                show: false
            },
            xaxis: {
                categories: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                axisBorder: {
                show: false
                },
                axisTicks: {
                show: false
                },
                labels: {
                style: {
                    colors: labelColor,
                    fontSize: '13px'
                }
                }
            },
            yaxis: {
                labels: {
                show: false
                }
            },
            responsive: [
                {
                breakpoint: 1440,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 9,
                        columnWidth: '60%'
                    }
                    }
                }
                },
                {
                breakpoint: 1300,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 9,
                        columnWidth: '60%'
                    }
                    }
                }
                },
                {
                breakpoint: 1200,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '50%'
                    }
                    }
                }
                },
                {
                breakpoint: 1040,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '50%'
                    }
                    }
                }
                },
                {
                breakpoint: 991,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '50%'
                    }
                    }
                }
                },
                {
                breakpoint: 420,
                options: {
                    plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '50%'
                    }
                    }
                }
                }
            ]
            };
        if (typeof visitorBarChartEl !== undefined && visitorBarChartEl !== null) {
            const visitorBarChart = new ApexCharts(visitorBarChartEl, visitorBarChartConfig);
            visitorBarChart.render();
        }


        // Order Statistics Chart
        // --------------------------------------------------------------------
        const leadsReportChartEl = document.querySelector('#leadsReportChart'),
            leadsReportChartConfig = {
            chart: {
                height: 157,
                width: 130,
                parentHeightOffset: 0,
                type: 'donut'
            },
            labels: ['Electronic', 'Sports', 'Decor', 'Fashion'],
            series: [20, 30, 20, 30],
            colors: [
                chartColors.donut.series1,
                chartColors.donut.series4,
                chartColors.donut.series3,
                chartColors.donut.series2
            ],
            stroke: {
                width: 0
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                return parseInt(val) + '%';
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                theme: false
            },
            grid: {
                padding: {
                top: 15
                }
            },
            plotOptions: {
                pie: {
                donut: {
                    size: '75%',
                    labels: {
                    show: true,
                    value: {
                        fontSize: '1.5rem',
                        fontFamily: 'Public Sans',
                        color: headingColor,
                        fontWeight: 500,
                        offsetY: -15,
                        formatter: function (val) {
                        return parseInt(val) + '%';
                        }
                    },
                    name: {
                        offsetY: 20,
                        fontFamily: 'Public Sans'
                    },
                    total: {
                        show: true,
                        fontSize: '15px',
                        fontFamily: 'Public Sans',
                        label: 'Average',
                        color: legendColor,
                        formatter: function (w) {
                        return '25%';
                        }
                    }
                    }
                }
                }
            }
            };
        if (typeof leadsReportChartEl !== undefined && leadsReportChartEl !== null) {
            const leadsReportChart = new ApexCharts(leadsReportChartEl, leadsReportChartConfig);
            leadsReportChart.render();
        }
        

        // Bar Chart
        // --------------------------------------------------------------------
        const barChartEl = document.querySelector('#barChart'),
            barChartConfig = {
            chart: {
                height: 400,
                type: 'bar',
                stacked: true,
                parentHeightOffset: 0,
                toolbar: {
                show: false
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    borderRadius: 4,
                    dataLabels: {
                        position: 'top',
                        orientation: 'vertical',
                    },
                    colors: {
                        
                        backgroundBarRadius: 10
                    }
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                fontSize: '10px',
                fontFamily: fontFamily,
                },
                offsetY: -27
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'start',
                labels: {
                colors: legendColor,
                useSeriesColors: false
                }
            },
            colors: [chartColors.column.series1, chartColors.column.series2],
            stroke: {
                show: true,
                colors: ['transparent']
            },
            grid: {
                borderColor: borderColor,
                xaxis: {
                lines: {
                    show: true
                }
                }
            },
            series: [
                {
                name: 'Users',
                data: @json($counts)
                }
            ],
            xaxis: {
                type: 'datetime',
                categories: @json($months),
                axisBorder: {
                show: false
                },
                axisTicks: {
                show: false
                },
                labels: {
                style: {
                    colors: labelColor,
                    fontSize: '13px'
                }
                }
            },
            yaxis: {
                title: {
                    text: 'Number of User',
                    style:{
                        size: 9,
                    }
                },
                labels: {
                style: {
                    colors: labelColor,
                    fontSize: '13px'
                }
                }
            },
            fill: {
                opacity: 1
            }
            };
        if (typeof barChartEl !== undefined && barChartEl !== null) {
            const barChart = new ApexCharts(barChartEl, barChartConfig);
            barChart.render();
        }



        var colors = {
    primary        : "#6571ff",
    secondary      : "#7987a1",
    success        : "#05a34a",
    info           : "#66d1d1",
    warning        : "#fbbc06",
    danger         : "#ff3366",
    light          : "#e9ecef",
    dark           : "#060c17",
    muted          : "#7987a1",
    gridBorder     : "rgba(77, 138, 240, .15)",
    bodyColor      : "#b8c3d9",
    cardBg         : "#0c1427"
  }

  var fontFamily = "'Roboto', Helvetica, sans-serif"


// Monthly User Chart
if($('#monthlyUserChart').length) {
    var options = {
      chart: {
        type: 'bar',
        height: '318',
        parentHeightOffset: 0,
        foreColor: colors.bodyColor,
        background: colors.cardBg,
        toolbar: {
          show: false
        },
      },
      theme: {
        mode: 'light'
      },
      tooltip: {
        theme: 'light'
      },
      colors: [colors.primary],
      fill: {
        opacity: .9
      } ,
      grid: {
        padding: {
          bottom: -4
        },
        borderColor: colors.gridBorder,
        xaxis: {
          lines: {
            show: true
          }
        }
      },
      series: [{
        name: 'Users',
        data: @json($counts)
      }],
      xaxis: {
        type: 'datetime',
        categories: @json($months),
        axisBorder: {
          color: colors.gridBorder,
        },
        axisTicks: {
          color: colors.gridBorder,
        },
      },
      yaxis: {
        title: {
          text: 'Number of User',
          style:{
            size: 9,
            color: colors.muted
          }
        },
      },
      legend: {
        show: true,
        position: "top",
        horizontalAlign: 'center',
        fontFamily: fontFamily,
        itemMargin: {
          horizontal: 8,
          vertical: 0
        },
      },
      stroke: {
        width: 0
      },
      dataLabels: {
        enabled: true,
        style: {
          fontSize: '10px',
          fontFamily: fontFamily,
        },
        offsetY: -27
      },
      plotOptions: {
        bar: {
          columnWidth: "50%",
          borderRadius: 4,
          dataLabels: {
            position: 'top',
            orientation: 'vertical',
          }
        },
      },
    }

    var apexBarChart = new ApexCharts(document.querySelector("#monthlyUserChart"), options);
    apexBarChart.render();
  }
  // Monthly User Chart - END
    };
</script>
@endsection