/*
 Gammel Theme v0.0.3
 FusionCharts JavaScript Library

 Copyright FusionCharts Technologies LLP
 License Information at <http://www.fusioncharts.com/license>
*/
FusionCharts.register('theme', {
  name: 'gammel',
  theme: {
    base: {
      chart: {
        paletteColors: '#7CB5EC, #434348, #8EED7D, #F7A35C, #8085E9, #F15C80, #E4D354, #2B908F, #F45B5B, #91E8E1',
        usePlotGradientColor: '0',
        showPlotBorder: '0',
        showShadow: '0',
        showBorder: '0',
        showCanvasBorder: '0',
        bgColor: '#FFFFFF',
        bgAlpha: '100',
        canvasBgAlpha: '0',
        showAlternateHGridColor: '0',
        showAlternateVGridColor: '0',
        divLineAlpha: '100',
        divLineColor: '#E6E6E6',
        divLineThickness: '1',
        yAxisValuesPadding: '10',
        labelPadding: '10',
        canvasPadding: '10',
        valuePadding: '0',
        adjustDiv: '1',
        labelDisplay: 'AUTO',
        transposeAxis: '1',
        showCanvasBase: '0',
        tooltipColor: '#333333',
        toolTipBgColor: '#F6F6F6',
        toolTipBgAlpha: '85',
        toolTipPadding: '8',
        toolTipBorderColor: '#999999',
        toolTipBorderRadius: '3',
        toolTipBorderThickness: '1',
        tooltipBorderAlpha: '90',
        showToolTipShadow: '1',
        baseFontSize: '11',
        baseFont: 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
        baseFontColor: '#666666',
        outCnvBaseFontSize: '11',
        outCnvBaseFont: 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
        outCnvBaseFontColor: '#666666',
        captionFontSize: '18',
        captionFontBold: '0',
        captionFontColor: '#333333',
        subCaptionFontSize: '12',
        subCaptionFontBold: '0',
        subCaptionFontColor: '#666666',
        valueFontBold: '1',
        valueFontSize: '11',
        valueFontColor: '#000000',
        placeValuesInside: '0',
        xAxisNameFontBold: '0',
        xAxisNameFontSize: '12',
        xAxisNameFontColor: '#666666',
        yAxisNameFontBold: '0',
        yAxisNameFontSize: '12',
        yAxisNameFontColor: '#666666',
        sYAxisNameFontBold: '0',
        sYAxisNameFontSize: '12',
        sYAxisNameFontColor: '#666666',
        xAxisNamePadding: '8',
        yAxisNamePadding: '8',
        sYAxisNamePadding: '8',
        captionPadding: '10',
        centerLabelFontSize: '11',
        centerLabelColor: '#666666',
        centerLabelBgOval: '1',
        useEllipsesWhenOverflow: '1',
        textOutline: 1,
        showLegend: '1',
        legendBgAlpha: '0',
        legendBorderThickness: '0',
        legendBorderAlpha: '0',
        legendShadow: '0',
        legendItemFontSize: '12',
        legendItemFontColor: '#333333',
        legendItemFontBold: '1',
        legendPosition: 'bottom',
        legendNumColumns: '4',
        drawCustomLegendIcon: '1',
        legendIconSides: '2',
        legendIconBorderThickness: '0',
        legendItemHiddenColor: '#CCCCCC',
        drawCrossLineOnTop: '0',
        crossLineThickness: '1',
        crossLineColor: '#E6E6E6',
        crossLineAlpha: '60',
        showHoverEffect: '1',
        plotHoverEffect: '1',
        plotFillHoverAlpha: '95',
        columnHoverAlpha: '95',
        scrollColor: '#808080',
        scrollHeight: '11',
        flatscrollbars: '1',
        scrollshowbuttons: '1'
      },
      dataset: [{}],
      trendlines: [{}]
    },
    column2d: {
      chart: {
        plotToolText: '$label: <b>$dataValue</b>',
        paletteColors: '#7CB5EC'
      }
    },
    column3d: {
      chart: {
        plotToolText: '$label: <b>$dataValue</b>',
        paletteColors: '#7CB5EC'
      }
    },
    line: {
      chart: {
        paletteColors: '#7CB5EC',
        drawAnchors: '1',
        anchorRadius: '4',
        anchorHoverRadius: '6',
        plotToolText: '$label: <b>$dataValue</b>',
        showValues: '0',
        anchorBgColor: '#7CB5EC',
        anchorBorderHoverColor: '#D8D8D8',
        drawCrossLine: '1',
        anchorHoverEffect: '1',
        anchorBgHoverAlpha: '95',
        anchorBorderHoverThickness: '2',
        legendIconBorderThickness: '1'
      }
    },
    // ... (todas las demás definiciones de gráficos)
    timeseries: {
      caption: {
        style: {
          text: {
            'font-size': 18,
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            fill: '#333333',
            'font-weight': 500
          }
        }
      },
      subcaption: {
        style: {
          text: {
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            'font-size': 12,
            fill: '#666666'
          }
        }
      },
      chart: {
        paletteColors: '#7CB5EC, #434348, #8EED7D, #F7A35C, #8085E9, #F15C80, #E4D354, #2B908F, #F45B5B, #91E8E1',
        multiCanvasTooltip: 1,
        baseFont: 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
        style: {
          text: {
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif'
          },
          canvas: {
            stroke: '#E6E6E6',
            'stroke-width': 1
          }
        }
      },
      tooltip: {
        style: {
          container: {
            'background-color': '#F6F6F6',
            opacity: 0.85,
            border: '1px solid rgba(153, 153, 153, 0.9)',
            'border-radius': '3px',
            padding: '8px'
          },
          text: {
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            color: '#333333'
          },
          header: {
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            color: '#666666',
            padding: '0px'
          },
          body: {
            padding: '0px',
            'font-size': '12px'
          }
        }
      },
      navigator: {
        scrollbar: {
          style: {
            button: {
              fill: '#E6E6E6',
              stroke: '#CCCCCC',
              'stroke-width': 1
            },
            arrow: {
              fill: '#333333'
            },
            grip: {
              fill: '#333333'
            },
            track: {
              fill: '#F2F2F2'
            },
            scroller: {
              fill: '#CCCCCC'
            }
          }
        },
        window: {
          style: {
            handle: {
              fill: '#F2F2F2',
              stroke: '#999999',
              'stroke-width': 0.3
            },
            mask: {
              opacity: 0.3,
              fill: '#6667C2'
            }
          }
        }
      },
      crossline: {
        style: {
          line: {
            stroke: '#E6E6E6',
            opacity: 0.6,
            'stroke-width': 1
          }
        }
      },
      extensions: {
        standardRangeSelector: {
          style: {
            'button-text': {
              fill: '#666666',
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif'
            },
            'button-text:hover': {
              fill: '#333333',
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif'
            },
            'button-text:active': {
              fill: '#333333',
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              'font-weight': 600
            },
            'button-background': {
              fill: '#F7F7F7'
            },
            'button-background:hover': {
              fill: '#F7F7F7'
            },
            'button-background:active': {
              fill: '#EBEBF5'
            },
            separator: {
              stroke: '#E6E6E6'
            }
          }
        },
        customRangeSelector: {
          style: {
            'title-text': {
              fill: '#333333',
              'font-weight': '500'
            },
            'title-icon': {
              fill: '#333333'
            },
            label: {
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              color: '#666666'
            },
            input: {
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              color: '#333333',
              'background-color': '#FFFFFF',
              border: '1px solid #CCCCCC'
            },
            'button-apply': {
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              'background-color': '#7CB5EC',
              color: '#333333',
              border: 'none',
              'font-weight': 600
            },
            'button-cancel': {
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              color: '#666666',
              'background-color': '#FFFFFF',
              border: 'none',
              'font-weight': 500
            },
            'button-cancel:hover': {
              color: '#333333',
              'font-weight': 600
            },
            'cal-navprev': {
              'font-family': 'Lucida Grande',
              'font-weight': 400
            },
            'cal-navnext': {
              'font-family': 'Lucida Grande',
              'font-weight': 400
            },
            'cal-header': {
              'font-family': 'Lucida Grande',
              'background-color': '#7CB5EC',
              color: '#333333'
            },
            'cal-days': {
              'font-family': 'Lucida Grande',
              color: '#333333'
            },
            'cal-date': {
              'font-family': 'Lucida Grande',
              'font-size': '11px',
              color: '#333333'
            },
            'cal-disableddate': {
              'font-family': 'Lucida Grande',
              'font-size': '11px',
              color: '#999999'
            },
            'cal-selecteddate': {
              'font-family': 'Lucida Grande',
              'background-color': '#7CB5EC',
              color: '#333333',
              'font-weight': 'bold',
              'font-size': '11px'
            },
            'cal-weekend': {
              'background-color': '#F3F8FD'
            }
          }
        }
      },
      legend: {
        style: {
          text: {
            fill: '#333333',
            'font-size': 12,
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            'font-weight': 600
          }
        }
      },
      xaxis: {
        timemarker: [
          {
            style: {
              marker: {
                fill: '#ebebf5',
                stroke: '#666666',
                'stroke-width': 1
              },
              'marker-notch': {
                fill: '#ebebf5',
                stroke: '#ebebf5'
              },
              'marker:hover': {
                fill: '#b2b2d9',
                stroke: '#333333',
                'stroke-width': 1
              },
              'marker-notch:hover': {
                fill: '#b2b2d9',
                stroke: '#b2b2d9'
              },
              'marker-line': {
                stroke: '#ebebf5'
              },
              'marker-line:hover': {
                stroke: '#b2b2d9'
              },
              text: {
                fill: '#333333',
                'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif'
              },
              'text:hover': {
                fill: '#333333',
                'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
                'font-weight': 600
              }
            }
          }
        ],
        style: {
          title: {
            'font-size': 12,
            'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
            fill: '#666666'
          },
          'grid-line': {
            stroke: '#E6E6E6',
            'stroke-width': 1
          },
          'tick-mark-major': {
            stroke: '#CCD6EB',
            'stroke-width': 1
          },
          'tick-mark-minor': {
            stroke: '#CCD6EB',
            'stroke-width': 0.75
          },
          'label-major': {
            color: '#666666'
          },
          'label-minor': {
            color: '#666666'
          },
          'label-context': {
            color: '#333333',
            'font-weight': 600
          }
        }
      },
      plotconfig: {
        column: {
          style: {
            'plot:hover': {
              opacity: 0.75
            },
            'plot:highlight': {
              opacity: 0.9
            }
          }
        },
        line: {
          style: {
            plot: {
              'stroke-width': 2
            },
            anchor: {
              'stroke-width': 1
            }
          }
        },
        area: {
          style: {
            anchor: {
              'stroke-width': 1
            }
          }
        },
        candlestick: {
          style: {
            bear: {
              stroke: '#F45B5B',
              fill: '#F45B5B'
            },
            bull: {
              stroke: '#8EED7D',
              fill: '#8EED7D'
            },
            'bear:hover': {
              opacity: 0.75
            },
            'bear:highlight': {
              opacity: 0.9
            },
            'bull:hover': {
              opacity: 0.75
            },
            'bull:highlight': {
              opacity: 0.9
            }
          }
        },
        ohlc: {
          style: {
            bear: {
              stroke: '#F45B5B',
              fill: '#F45B5B'
            },
            bull: {
              stroke: '#8EED7D',
              fill: '#8EED7D'
            },
            'bear:hover': {
              opacity: 0.75
            },
            'bear:highlight': {
              opacity: 0.9
            },
            'bull:hover': {
              opacity: 0.75
            },
            'bull:highlight': {
              opacity: 0.9
            }
          }
        }
      },
      yaxis: [
        {
          style: {
            title: {
              'font-size': 12,
              'font-family': 'Lucida Grande, Lucida Sans Unicode, Arial, Helvetica, sans-serif',
              fill: '#666666'
            },
            'tick-mark': {
              stroke: '#E6E6E6',
              'stroke-width': 1
            },
            'grid-line': {
              stroke: '#E6E6E6',
              'stroke-width': 1
            },
            label: {
              color: '#666666'
            }
          }
        }
      ]
    }
  }
});
