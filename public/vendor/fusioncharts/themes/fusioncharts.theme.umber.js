/*
 Umber Theme v0.0.3
 FusionCharts JavaScript Library

 Copyright FusionCharts Technologies LLP
 License Information at <http://www.fusioncharts.com/license>
*/
FusionCharts.register('theme', {
  name: 'umber',
  theme: {
    base: {
      chart: {
        paletteColors: '#5D4037, #7B1FA2, #0288D1, #388E3C, #E64A19, #0097A7, #AFB42B, #8D6E63, #5D4037, #795548',
        labelDisplay: 'auto',
        baseFontColor: '#333333',
        baseFont: 'Titillium Web Regular, Arial',
        captionFontSize: '16',
        subcaptionFontSize: '12',
        subcaptionFontBold: '0',
        showBorder: '0',
        bgColor: '#FFF1E5',
        showShadow: '0',
        canvasBgColor: '#FFF1E5',
        showCanvasBorder: '0',
        useplotgradientcolor: '0',
        useRoundEdges: '0',
        showPlotBorder: '0',
        showAlternateHGridColor: '0',
        showAlternateVGridColor: '0',
        toolTipColor: '#333333',
        toolTipBorderThickness: '0.5',
        toolTipBgColor: '#FFF9F5',
        toolTipBgAlpha: '90',
        toolTipBorderRadius: '3',
        toolTipPadding: '6',
        legendBgAlpha: '0',
        legendBorderAlpha: '0',
        legendShadow: '0',
        legendItemFontSize: '12',
        legendItemFontColor: '#333333',
        legendCaptionFontSize: '14',
        divlineAlpha: '100',
        divlineColor: '#D5CDBE',
        divlineThickness: '0.75',
        divLineIsDashed: '0',
        divLineDashLen: '1',
        divLineGapLen: '1',
        scrollheight: '10',
        flatScrollBars: '1',
        scrollShowButtons: '0',
        scrollColor: '#F2E5D9',
        showHoverEffect: '1',
        valueFontSize: '11',
        showXAxisLine: '1',
        xAxisLineThickness: '0.75',
        xAxisLineColor: '#D5CDBE'
      },
      dataset: [{}],
      trendlines: [{}]
    },
    pie2d: {
      chart: {
        placeValuesInside: '0',
        use3dlighting: '0',
        valueFontColor: '#333333',
        captionPadding: '15'
      },
      data: function (c, a, b) {
        a = window.Math;
        return {
          alpha: 100 - (50 < b ? a.round(100 / a.ceil(b / 10)) : 20) * a.floor(c / 10)
        };
      }
    },
    doughnut2d: {
      chart: {
        placeValuesInside: '0',
        use3dlighting: '0',
        valueFontColor: '#333333',
        centerLabelFontSize: '12',
        centerLabelBold: '1',
        centerLabelFontColor: '#333333',
        captionPadding: '15'
      },
      data: function (c, a, b) {
        a = window.Math;
        return {
          alpha: 100 - (50 < b ? a.round(100 / a.ceil(b / 10)) : 20) * a.floor(c / 10)
        };
      }
    },
    line: {
      chart: {
        lineThickness: '2',
        anchorBgColor: '#FFF1E5',
        anchorBorderThickness: '2'
      }
    },
    spline: {
      chart: {
        lineThickness: '2',
        anchorBgColor: '#FFF1E5',
        anchorBorderThickness: '2'
      }
    },
    column2d: {
      chart: {
        paletteColors: '#0f5499',
        valueFontColor: '#ffffff',
        placeValuesInside: '1',
        rotateValues: '1'
      }
    },
    bar2d: {
      chart: {
        paletteColors: '#0f5499',
        valueFontColor: '#ffffff',
        placeValuesInside: '1'
      }
    },
    column3d: {
      chart: {
        paletteColors: '#0f5499',
        valueFontColor: '#ffffff',
        placeValuesInside: '1',
        rotateValues: '1',
        showCanvasBase: '0'
      }
    },
    bar3d: {
      chart: {
        paletteColors: '#0f5499',
        valueFontColor: '#ffffff',
        placeValuesInside: '1',
        showCanvasBase: '0'
      }
    },
    area2d: {
      chart: {
        valueBgColor: '#FFF9F5',
        valueBgAlpha: '90',
        valueBorderPadding: '-2',
        valueBorderRadius: '2',
        plotFillAlpha: '85',
        drawAnchors: '0'
      }
    },
    splinearea: {
      chart: {
        valueBgColor: '#FFF9F5',
        valueBgAlpha: '90',
        valueBorderPadding: '-2',
        valueBorderRadius: '2',
        plotFillAlpha: '85',
        drawAnchors: '0'
      }
    },
    mscolumn2d: {
      chart: {
        valueFontColor: '#ffffff',
        placeValuesInside: '1',
        rotateValues: '1'
      }
    },
    mscolumn3d: {
      chart: {
        showValues: '0',
        showCanvasBase: '0'
      }
    },
    msstackedcolumn2dlinedy: {
      chart: {
        showValues: '0'
      }
    },
    stackedcolumn2d: {
      chart: {
        showValues: '0'
      }
    },
    stackedarea2d: {
      chart: {
        valueBgColor: '#FFF9F5',
        valueBgAlpha: '90',
        valueBorderPadding: '-2',
        valueBorderRadius: '2',
        plotFillAlpha: '85',
        drawAnchors: '0'
      }
    },
    stackedbar2d: {
      chart: {
        showValues: '0'
      }
    },
    msstackedcolumn2d: {
      chart: {
        showValues: '0'
      }
    },
    mscombi3d: {
      chart: {
        showValues: '0',
        showCanvasBase: '0'
      }
    },
    mscombi2d: {
      chart: {
        showValues: '0'
      }
    },
    mscolumn3dlinedy: {
      chart: {
        showValues: '0',
        showCanvasBase: '0'
      }
    },
    stackedcolumn3dline: {
      chart: {
        showValues: '0',
        showCanvasBase: '0'
      }
    },
    stackedcolumn2dline: {
      chart: {
        showValues: '0'
      }
    },
    scrollstackedcolumn2d: {
      chart: {
        valueFontColor: '#ffffff'
      }
    },
    scrollcombi2d: {
      chart: {
        showValues: '0'
      }
    },
    scrollcombidy2d: {
      chart: {
        showValues: '0'
      }
    },
    logstackedcolumn2d: {
      chart: {
        showValues: '0'
      }
    },
    logmsline: {
      chart: {
        showValues: '0'
      }
    },
    logmscolumn2d: {
      chart: {
        showValues: '0'
      }
    },
    msstackedcombidy2d: {
      chart: {
        showValues: '0'
      }
    },
    scrollcolumn2d: {
      chart: {
        valueFontColor: '#ffffff',
        placeValuesInside: '1',
        rotateValues: '1'
      }
    },
    pareto2d: {
      chart: {
        paletteColors: '#0f5499',
        lineColor: '#B5323E',
        showValues: '0'
      }
    },
    pareto3d: {
      chart: {
        paletteColors: '#0f5499',
        lineColor: '#B5323E',
        showValues: '0',
        showCanvasBase: '0'
      }
    },
    angulargauge: {
      chart: {
        pivotFillColor: '#33302E',
        pivotRadius: '4',
        gaugeFillMix: '{light-0}',
        showTickValues: '1',
        majorTMHeight: '10',
        majorTMThickness: '1',
        majorTMColor: '#D5CDBE',
        minorTMNumber: '0',
        tickValueDistance: '10',
        valueFontSize: '12',
        valueFontBold: '1',
        gaugeInnerRadius: '50%',
        showHoverEffect: '0'
      },
      dials: {
        dial: [
          {
            baseWidth: '10',
            rearExtension: '7',
            bgColor: '#33302E',
            bgAlpha: '100',
            borderColor: '#D5CDBE',
            bgHoverAlpha: '20'
          }
        ]
      }
    },
    hlineargauge: {
      chart: {
        pointerFillColor: '#33302E',
        gaugeFillMix: '{light-0}',
        showTickValues: '1',
        majorTMHeight: '3',
        majorTMColor: '#D5CDBE',
        minorTMNumber: '0',
        valueFontSize: '12',
        valueFontBold: '1'
      },
      pointers: {
        pointer: [{}]
      }
    },
    bubble: {
      chart: {
        use3dlighting: '0',
        showplotborder: '0',
        showYAxisLine: '1',
        yAxisLineThickness: '1',
        yAxisLineColor: '#D5CDBE',
        showAlternateHGridColor: '0',
        showAlternateVGridColor: '0'
      },
      categories: [
        {
          verticalLineDashed: '1',
          verticalLineDashLen: '1',
          verticalLineDashGap: '1',
          verticalLineThickness: '1',
          verticalLineColor: '#D5CDBE',
          category: [{}]
        }
      ],
      vtrendlines: [
        {
          line: [
            {
              alpha: '0'
            }
          ]
        }
      ]
    },
    scatter: {
      chart: {
        use3dlighting: '0',
        showYAxisLine: '1',
        yAxisLineThickness: '1',
        yAxisLineColor: '#D5CDBE',
        showAlternateHGridColor: '0',
        showAlternateVGridColor: '0'
      },
      categories: [
        {
          verticalLineDashed: '1',
          verticalLineDashLen: '1',
          verticalLineDashGap: '1',
          verticalLineThickness: '1',
          verticalLineColor: '#D5CDBE',
          category: [{}]
        }
      ],
      vtrendlines: [
        {
          line: [
            {
              alpha: '0'
            }
          ]
        }
      ]
    },
    boxandwhisker2d: {
      chart: {
        valueBgColor: '#FFF9F5',
        valueBgAlpha: '90',
        valueBorderPadding: '-2',
        valueBorderRadius: '2'
      }
    },
    thermometer: {
      chart: {
        gaugeFillColor: '#0f5499'
      }
    },
    cylinder: {
      chart: {
        cylFillColor: '#0f5499'
      }
    },
    sparkline: {
      chart: {
        linecolor: '#0F5499'
      }
    },
    sparkcolumn: {
      chart: {
        plotFillColor: '#0F5499'
      }
    },
    sparkwinloss: {
      chart: {
        winColor: '#0A5E66',
        lossColor: '#B5323E',
        drawColor: '#EDB34A',
        scoreLessColor: '#0F5499'
      }
    },
    hbullet: {
      chart: {
        plotFillColor: '#0F5499',
        targetColor: '#000000'
      }
    },
    vbullet: {
      chart: {
        plotFillColor: '#0F5499',
        targetColor: '#000000'
      }
    }
  }
});
