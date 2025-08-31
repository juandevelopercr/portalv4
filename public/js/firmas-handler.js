window.renderFusionCharts = function (chartData) {
  window.fusionChartInstances = window.fusionChartInstances || {};

  //*******************************************************************************************************//
  //**************************************** Gráfico de mscolumn3d*****************************************//
  //*******************************************************************************************************//

  const mscolumn3dContainerId = 'firmas_mscolumn3d';
  const mscolumn3dContainer = document.getElementById(mscolumn3dContainerId);

  if (mscolumn3dContainer) {
    const hasDataLine = chartData.mscolumn3d?.data?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataLine) {
      mscolumn3dContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.mscolumn3d?.caption || ''} 
                ${chartData.mscolumn3d?.subCaption ? '| ' + chartData.mscolumn3d.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[mscolumn3dContainerId]) {
        window.fusionChartInstances[mscolumn3dContainerId].dispose();
        delete window.fusionChartInstances[mscolumn3dContainerId];
      }
    } else {
      if (window.fusionChartInstances[mscolumn3dContainerId]) {
        window.fusionChartInstances[mscolumn3dContainerId].dispose();
      }

      mscolumn3dContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'mscolumn3d',
          renderAt: mscolumn3dContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.mscolumn3d.caption || 'Diferencia de firmas con año anterior',
              subCaption: chartData.mscolumn3d.subCaption || '',
              xAxisName: chartData.mscolumn3d.xAxisName || 'Mes',
              yAxisName: chartData.mscolumn3d.yAxisName || 'Firmas',
              theme: chartData.theme || 'zune',
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

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            categories: chartData.mscolumn3d.data.categories || [{ category: [] }],
            dataset: chartData.mscolumn3d.data.dataset || []
          }
        };

        window.fusionChartInstances[mscolumn3dContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[mscolumn3dContainerId].render();
      } catch (error) {
        mscolumn3dContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //**************************************** Gráfico de bar ********************************************//
  //*******************************************************************************************************//

  const datasetFirmas_bar = chartData.bar.data;
  const hasDataFirmas_bar = Array.isArray(datasetFirmas_bar) && datasetFirmas_bar.length > 0;

  const barContainerId = 'firmas_bar';
  const barContainer = document.getElementById(barContainerId);

  if (barContainer) {
    const validData = chartData.bar?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataHonorariosBar = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataHonorariosBar) {
      barContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.bar?.caption || ''} 
                ${chartData.bar?.subCaption ? '| ' + chartData.bar.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[barContainerId]) {
        window.fusionChartInstances[barContainerId].dispose();
        delete window.fusionChartInstances[barContainerId];
      }
    } else {
      if (window.fusionChartInstances[barContainerId]) {
        window.fusionChartInstances[barContainerId].dispose();
      }

      barContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'column3d',
          renderAt: barContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.bar.caption || '-',
              subCaption: chartData.bar.subCaption || '',
              theme: chartData.theme || 'zune',
              numberPrefix: '',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '2',
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

        window.fusionChartInstances[barContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[barContainerId].render();
      } catch (error) {
        barContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //**************************************** Gráfico de line ********************************************//
  //*******************************************************************************************************//

  const lineContainerId = 'firmas_line';
  const lineContainer = document.getElementById(lineContainerId);

  if (lineContainer) {
    const hasDataLine = chartData.line?.dataset?.some(d => d.data?.some(v => v.value !== 0 && v.value !== null));

    if (!hasDataLine) {
      lineContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.line?.caption || ''} 
                ${chartData.line?.subCaption ? '| ' + chartData.line.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[lineContainerId]) {
        window.fusionChartInstances[lineContainerId].dispose();
        delete window.fusionChartInstances[lineContainerId];
      }
    } else {
      if (window.fusionChartInstances[lineContainerId]) {
        window.fusionChartInstances[lineContainerId].dispose();
      }

      lineContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'msline',
          renderAt: lineContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.line.caption || '-',
              subCaption: chartData.line.subCaption || '',
              xAxisName: chartData.line.xAxisName || 'Mes',
              yAxisName: chartData.line.yAxisName || 'Firmas',
              theme: chartData.theme || 'zune',
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
            categories: chartData.line.categories || [{ category: [] }],
            dataset: chartData.line.dataset || []
          }
        };

        window.fusionChartInstances[lineContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[lineContainerId].render();
      } catch (error) {
        lineContainer.innerHTML = `
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
