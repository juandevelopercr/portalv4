window.renderFusionCharts = function (chartData) {
  window.fusionChartInstances = window.fusionChartInstances || {};

  // Paleta de colores CORREGIDA (array de colores individuales)
  const themePalettes = {
    candy: [
      '#36B5D8',
      '#F0DC46',
      '#F066AC',
      '#6EC85A',
      '#6E80CA',
      '#E09653',
      '#E1D7AD',
      '#61C8C8',
      '#EBE4F4',
      '#E64141'
    ],
    carbon: [
      '#444444',
      '#666666',
      '#888888',
      '#aaaaaa',
      '#cccccc',
      '#555555',
      '#777777',
      '#999999',
      '#bbbbbb',
      '#dddddd'
    ],
    fint: [
      '#0075c2',
      '#1aaf5d',
      '#f2c500',
      '#f45b00',
      '#8e0000',
      '#0e948c',
      '#8cbb2c',
      '#f3de00',
      '#c02d00',
      '#5b0101'
    ],
    fusion: ['#5D62B5', '#29C3BE', '#F2726F', '#FFC533', '#62B58F', '#BC95DF', '#67CDF2'],
    gammel: [
      '#7CB5EC',
      '#434348',
      '#8EED7D',
      '#F7A35C',
      '#8085E9',
      '#F15C80',
      '#E4D354',
      '#2B908F',
      '#F45B5B',
      '#91E8E1'
    ],
    ocean: [
      '#04476c',
      '#4d998d',
      '#77be99',
      '#a7dca6',
      '#cef19a',
      '#0e948c',
      '#64ad93',
      '#8fcda0',
      '#bbe7a0',
      '#dcefc1'
    ],
    umber: [
      '#5D4037',
      '#7B1FA2',
      '#0288D1',
      '#388E3C',
      '#E64A19',
      '#0097A7',
      '#AFB42B',
      '#8D6E63',
      '#5D4037',
      '#795548'
    ],
    zune: ['#0075c2', '#1aaf5d', '#f2c500', '#f45b00', '#8e0000', '#0e948c', '#8cbb2c', '#f3de00', '#c02d00', '#5b0101']
  };

  // Función para obtener colores rotados
  function getRotatedColors(theme, rotationIndex = 0) {
    const palette = themePalettes[theme] || themePalettes.zune;
    const rotated = [...palette];

    // Rotar la paleta según el índice
    for (let i = 0; i < rotationIndex; i++) {
      rotated.push(rotated.shift());
    }
    console.log(rotated);
    return rotated;
  }

  //*******************************************************************************************************//
  //*********************************** Gráfico de line caratulas******************************************//
  //*******************************************************************************************************//

  const lineCaratulasContainerId = 'caratulas_line';
  const lineCaratulasContainer = document.getElementById(lineCaratulasContainerId);

  if (lineCaratulasContainer) {
    const hasDataCaratulasLine = chartData.line_caratulas?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataCaratulasLine) {
      lineCaratulasContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.line_caratulas?.caption || ''} 
                ${chartData.line_caratulas?.subCaption ? '| ' + chartData.line_caratulas.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[lineCaratulasContainerId]) {
        window.fusionChartInstances[lineCaratulasContainerId].dispose();
        delete window.fusionChartInstances[lineCaratulasContainerId];
      }
    } else {
      if (window.fusionChartInstances[lineCaratulasContainerId]) {
        window.fusionChartInstances[lineCaratulasContainerId].dispose();
      }

      lineCaratulasContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'msline',
          renderAt: lineCaratulasContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.line_caratulas.caption || '-',
              subCaption: chartData.line_caratulas.subCaption || '',
              xAxisName: chartData.line_caratulas.xAxisName || 'Mes',
              yAxisName: chartData.line_caratulas.yAxisName || 'Carátulas',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '2',
              forceDecimals: '1',
              exportEnabled: '1',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5',
              showSum: '0',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            categories: chartData.line_caratulas.categories || [{ category: [] }],
            dataset: chartData.line_caratulas.dataset || []
          }
        };

        window.fusionChartInstances[lineCaratulasContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[lineCaratulasContainerId].render();
      } catch (error) {
        lineCaratulasContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //*********************************** Gráfico de line pre caratulas**************************************//
  //*******************************************************************************************************//

  const linePreCaratulasContainerId = 'precaratulas_line';
  const linePreCaratulasContainer = document.getElementById(linePreCaratulasContainerId);

  if (linePreCaratulasContainer) {
    const hasDataPreCaratulasLine = chartData.line_precaratulas?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataPreCaratulasLine) {
      linePreCaratulasContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.line_precaratulas?.caption || ''} 
                ${chartData.line_precaratulas?.subCaption ? '| ' + chartData.line_precaratulas.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[linePreCaratulasContainerId]) {
        window.fusionChartInstances[linePreCaratulasContainerId].dispose();
        delete window.fusionChartInstances[linePreCaratulasContainerId];
      }
    } else {
      if (window.fusionChartInstances[linePreCaratulasContainerId]) {
        window.fusionChartInstances[linePreCaratulasContainerId].dispose();
      }

      linePreCaratulasContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'msline',
          renderAt: linePreCaratulasContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.line_precaratulas.caption || '-',
              subCaption: chartData.line_precaratulas.subCaption || '',
              xAxisName: chartData.line_precaratulas.xAxisName || 'Mes',
              yAxisName: chartData.line_precaratulas.yAxisName || 'Pre carátulas',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 2).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '2',
              forceDecimals: '1',
              exportEnabled: '1',

              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '13',
              legendItemFontSize: '14',
              legendCaptionFontSize: '16',
              valueFontSize: '12',
              outCnvBaseFontSize: '14',
              labelDisplay: 'WRAP',
              labelPadding: '5',
              showSum: '0',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            categories: chartData.line_precaratulas.categories || [{ category: [] }],
            dataset: chartData.line_precaratulas.dataset || []
          }
        };

        window.fusionChartInstances[linePreCaratulasContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[linePreCaratulasContainerId].render();
      } catch (error) {
        linePreCaratulasContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }
};

// Redimensionar con debounce
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    if (window.fusionChartInstances) {
      for (const containerId in window.fusionChartInstances) {
        if (window.fusionChartInstances[containerId]) {
          try {
            window.fusionChartInstances[containerId].resizeTo({
              width: '100%',
              height: '500'
            });
          } catch (error) {
            console.error(`Error al redimensionar ${containerId}:`, error);
          }
        }
      }
    }
  }, 250);
});
