window.renderFusionCharts = function (chartData) {
  window.fusionChartInstances = window.fusionChartInstances || {};

  //*******************************************************************************************************//
  //**************************************** Gráfico de línea *********************************************//
  //*******************************************************************************************************//

  const lineContainerId = 'honorarios_mes_line';
  const lineContainer = document.getElementById(lineContainerId);

  if (lineContainer) {
    const hasDataHonorariosLine = chartData.line?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHonorariosLine) {
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
              caption: chartData.line.caption || 'Gráfico de Honorarios',
              subCaption: chartData.line.subCaption || '',
              xAxisName: chartData.line.xAxisName || 'Mes',
              yAxisName: chartData.line.yAxisName || 'Facturado',
              theme: chartData.theme || 'zune',
              numberPrefix: '$',
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

  //*******************************************************************************************************//
  //**************************************** Gráfico de pastel ********************************************//
  //*******************************************************************************************************//

  const pieContainerId = 'honorarios_mes_pie';
  const pieContainer = document.getElementById(pieContainerId);

  if (pieContainer) {
    const validData = chartData.pie?.data
      ?.map(item => ({
        ...item,
        value: parseFloat(item.value),
        numericValue: parseFloat(item.value)
      }))
      .filter(item => !isNaN(item.numericValue));

    const hasDataHonorariosPie = validData?.length > 0 && validData.some(item => item.numericValue > 0);

    if (!hasDataHonorariosPie) {
      pieContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.pie?.caption || ''} 
                ${chartData.pie?.subCaption ? '| ' + chartData.pie.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[pieContainerId]) {
        window.fusionChartInstances[pieContainerId].dispose();
        delete window.fusionChartInstances[pieContainerId];
      }
    } else {
      if (window.fusionChartInstances[pieContainerId]) {
        window.fusionChartInstances[pieContainerId].dispose();
      }

      pieContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando distribución de honorarios...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'pie3d',
          renderAt: pieContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.pie.caption || 'Honorarios USD por Banco',
              subCaption: chartData.pie.subCaption || '',
              theme: chartData.theme || 'zune',
              numberPrefix: '$',
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

        window.fusionChartInstances[pieContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[pieContainerId].render();
      } catch (error) {
        pieContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //**************************************** Gráfico de stackedbar3d ********************************************//
  //*******************************************************************************************************//
  const stackedbar3dContainerId = 'centro_costo_stackedbar3d';
  const stackedbar3dContainer = document.getElementById(stackedbar3dContainerId);

  if (stackedbar3dContainer) {
    const hasDataHonorariosLine = chartData.stackedbar3d?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHonorariosLine) {
      stackedbar3dContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.stackedbar3d?.caption || ''} 
                ${chartData.stackedbar3d?.subCaption ? '| ' + chartData.stackedbar3d.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[stackedbar3dContainerId]) {
        window.fusionChartInstances[stackedbar3dContainerId].dispose();
        delete window.fusionChartInstances[stackedbar3dContainerId];
      }
    } else {
      if (window.fusionChartInstances[stackedbar3dContainerId]) {
        window.fusionChartInstances[stackedbar3dContainerId].dispose();
      }

      stackedbar3dContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'stackedbar3d',
          renderAt: stackedbar3dContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.stackedbar3d.caption || 'Gráfico de facturación por centro de costo USD',
              subCaption: chartData.stackedbar3d.subCaption || '',
              xAxisName: chartData.stackedbar3d.xAxisName || 'Centro de costo',
              yAxisName: chartData.stackedbar3d.yAxisName || 'Facturado',
              theme: chartData.theme || 'zune',
              numberPrefix: '$',
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
            categories: chartData.stackedbar3d.categories || [{ category: [] }],
            dataset: chartData.stackedbar3d.dataset || []
          }
        };

        window.fusionChartInstances[stackedbar3dContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[stackedbar3dContainerId].render();
      } catch (error) {
        stackedbar3dContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //************************************ Panel de KPI (Gráfico de Columnas) ******************************//
  //*******************************************************************************************************//

  const kpiContainerId = 'kpi-chart-container';
  const kpiContainer = document.getElementById(kpiContainerId);

  if (kpiContainer) {
    // Verificar si hay datos para mostrar
    const hasKpiData = chartData.kpi && Array.isArray(chartData.kpi) && chartData.kpi.length > 0;

    if (!hasKpiData) {
      kpiContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.kpiMeta?.caption || ''} 
                ${chartData.kpiMeta?.subCaption ? '| ' + chartData.kpiMeta.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[kpiContainerId]) {
        window.fusionChartInstances[kpiContainerId].dispose();
        delete window.fusionChartInstances[kpiContainerId];
      }
    } else {
      if (window.fusionChartInstances[kpiContainerId]) {
        window.fusionChartInstances[kpiContainerId].dispose();
      }

      kpiContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        // Obtener datos del KPI desde PHP
        const kpiDataPoints = chartData.kpi;

        // Configuración base del gráfico
        const chartConfig = {
          type: 'column2d',
          renderAt: kpiContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.kpiMeta.caption || 'Resumen de indicadores',
              subCaption: chartData.kpiMeta.subCaption || '',
              xAxisName: 'Indicadores',
              yAxisName: 'Valores',
              theme: chartData.theme || 'zune',
              numberPrefix: '$',
              numberPrefix: '$',
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
            data: kpiDataPoints // Usamos los puntos de datos de PHP
          }
        };

        window.fusionChartInstances[kpiContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[kpiContainerId].render();
      } catch (error) {
        // ... (manejo de errores) ...
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
