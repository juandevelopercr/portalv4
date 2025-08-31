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

  const lineCaratulasContainerId = 'volumen_line';
  const lineCaratulasContainer = document.getElementById(lineCaratulasContainerId);

  if (lineCaratulasContainer) {
    const hasDataCaratulasLine = chartData.line_volumen?.dataset?.some(d =>
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
                ${chartData.line_volumen?.caption || ''} 
                ${chartData.line_volumen?.subCaption ? '| ' + chartData.line_volumen.subCaption : ''}
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
              caption: chartData.line_volumen.caption || '-',
              subCaption: chartData.line_volumen.subCaption || '',
              xAxisName: chartData.line_volumen.xAxisName || 'Mes',
              yAxisName: chartData.line_volumen.yAxisName || 'Formalizaciones',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
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
            categories: chartData.line_volumen.categories || [{ category: [] }],
            dataset: chartData.line_volumen.dataset || []
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
  //******************************** Gráfico de pastel formalizaciones Mes ********************************//
  //*******************************************************************************************************//

  const pieFormalizacionMesContainerId = 'formalizaciones-mes';
  const pieformalizacionMesContainer = document.getElementById(pieFormalizacionMesContainerId);

  if (pieformalizacionMesContainer) {
    const validData = chartData.pie_formalizaciones_mes?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionMesPie = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionMesPie) {
      pieformalizacionMesContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_mes?.caption || ''} 
                ${chartData.pie_formalizaciones_mes?.subCaption ? '| ' + chartData.pie_formalizaciones_mes.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionMesContainerId]) {
        window.fusionChartInstances[pieFormalizacionMesContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionMesContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionMesContainerId]) {
        window.fusionChartInstances[pieFormalizacionMesContainerId].dispose();
      }

      pieformalizacionMesContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionMesContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_mes.caption || 'Formalizaciones USD',
              subCaption: chartData.pie_formalizaciones_mes.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

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
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionMesContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionMesContainerId].render();
      } catch (error) {
        pieformalizacionMesContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //******************************** Gráfico de pastel formalizaciones Year ********************************//
  //*******************************************************************************************************//

  const pieFormalizacionYearContainerId = 'formalizaciones-year';
  const pieformalizacionYearContainer = document.getElementById(pieFormalizacionYearContainerId);

  if (pieformalizacionYearContainer) {
    const validData = chartData.pie_formalizaciones_year?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataFormalizacionYearPie = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataFormalizacionYearPie) {
      pieformalizacionYearContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie_formalizaciones_year?.caption || ''} 
                ${chartData.pie_formalizaciones_year?.subCaption ? '| ' + chartData.pie_formalizaciones_year.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieFormalizacionYearContainerId]) {
        window.fusionChartInstances[pieFormalizacionYearContainerId].dispose();
        delete window.fusionChartInstances[pieFormalizacionYearContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieFormalizacionYearContainerId]) {
        window.fusionChartInstances[pieFormalizacionYearContainerId].dispose();
      }

      pieformalizacionYearContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieFormalizacionYearContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie_formalizaciones_year.caption || 'Formalizaciones USD',
              subCaption: chartData.pie_formalizaciones_year.subCaption || '',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '0',
              forceDecimals: '1',
              exportEnabled: '1',
              enableSmartLabels: '1',
              centerLabel: '$label: $value',

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

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
              labelPadding: '5'
            },
            data: validData
          }
        };

        window.fusionChartInstances[pieFormalizacionYearContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieFormalizacionYearContainerId].render();
      } catch (error) {
        pieformalizacionYearContainer.innerHTML = `
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
