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
  //********************************** Gráfico de mscolumn3d USD ******************************************//
  //*******************************************************************************************************//

  const mscolumn3dUsdContainerId = 'honorarios_usd_bar';
  const mscolumn3dUsdContainer = document.getElementById(mscolumn3dUsdContainerId);

  if (mscolumn3dUsdContainer) {
    const hasDataLine = chartData.bar_usd?.dataset?.some(d => d.data?.some(v => v.value !== 0 && v.value !== null));

    if (!hasDataLine) {
      mscolumn3dUsdContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.bar_usd?.caption || ''} 
                ${chartData.bar_usd?.subCaption ? '| ' + chartData.bar_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[mscolumn3dUsdContainerId]) {
        window.fusionChartInstances[mscolumn3dUsdContainerId].dispose();
        delete window.fusionChartInstances[mscolumn3dUsdContainerId];
      }
    } else {
      if (window.fusionChartInstances[mscolumn3dUsdContainerId]) {
        window.fusionChartInstances[mscolumn3dUsdContainerId].dispose();
      }

      mscolumn3dUsdContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'mscolumn3d',
          renderAt: mscolumn3dUsdContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.bar_usd.caption || 'Honorarios USD',
              subCaption: chartData.bar_usd.subCaption || '',
              xAxisName: chartData.bar_usd.xAxisName || 'Mes',
              yAxisName: chartData.bar_usd.yAxisName || 'Honorarios USD',
              theme: chartData.theme || 'zune',
              // Usar paleta de colores rotada
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

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            categories: chartData.bar_usd.categories || [{ category: [] }],
            dataset: chartData.bar_usd.dataset || []
          }
        };

        window.fusionChartInstances[mscolumn3dUsdContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[mscolumn3dUsdContainerId].render();
      } catch (error) {
        mscolumn3dUsdContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************** Gráfico de mscolumn3d CRC ******************************************//
  //*******************************************************************************************************//

  const mscolumn3dCrcContainerId = 'honorarios_crc_bar';
  const mscolumn3dCrcContainer = document.getElementById(mscolumn3dCrcContainerId);

  if (mscolumn3dCrcContainer) {
    const hasDataLine = chartData.bar_crc?.dataset?.some(d => d.data?.some(v => v.value !== 0 && v.value !== null));

    if (!hasDataLine) {
      mscolumn3dCrcContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.bar_crc?.caption || ''} 
                ${chartData.bar_crc?.subCaption ? '| ' + chartData.bar_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[mscolumn3dCrcContainerId]) {
        window.fusionChartInstances[mscolumn3dCrcContainerId].dispose();
        delete window.fusionChartInstances[mscolumn3dCrcContainerId];
      }
    } else {
      if (window.fusionChartInstances[mscolumn3dCrcContainerId]) {
        window.fusionChartInstances[mscolumn3dCrcContainerId].dispose();
      }

      mscolumn3dCrcContainer.innerHTML = `
        <div class="chart-loader">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando gráfico...</span>
          </div>
          <p>Cargando visualización...</p>
        </div>`;

      try {
        const chartConfig = {
          type: 'mscolumn3d',
          renderAt: mscolumn3dCrcContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.bar_crc.caption || 'Honorarios CRC',
              subCaption: chartData.bar_crc.subCaption || '',
              xAxisName: chartData.bar_crc.xAxisName || 'Mes',
              yAxisName: chartData.bar_crc.yAxisName || 'Honorarios CRC',
              theme: chartData.theme || 'zune',
              // Usar paleta de colores rotada
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

              // Elementos del gráfico
              showLegend: '1',
              showValues: '1', // Desactivar valores sobre puntos
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            categories: chartData.bar_crc.categories || [{ category: [] }],
            dataset: chartData.bar_crc.dataset || []
          }
        };

        window.fusionChartInstances[mscolumn3dCrcContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[mscolumn3dCrcContainerId].render();
      } catch (error) {
        mscolumn3dCrcContainer.innerHTML = `
          <div class="chart-error">
            <i class="fas fa-exclamation-triangle text-danger"></i>
            <h3>Error en el gráfico</h3>
            <p>${error.message || 'Por favor intenta nuevamente'}</p>
          </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************** Gráfico de Heatmap USD *********************************************//
  //*******************************************************************************************************//

  const heatmapUsdContainerId = 'facturacion_heatmap_usd';
  const heatmapUsdContainer = document.getElementById(heatmapUsdContainerId);

  if (heatmapUsdContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapUsd = chartData.heatmap_usd?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapUsd) {
      heatmapUsdContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_usd?.caption || ''} 
                ${chartData.heatmap_usd?.subCaption ? '| ' + chartData.heatmap_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapUsdContainerId]) {
        window.fusionChartInstances[heatmapUsdContainerId].dispose();
        delete window.fusionChartInstances[heatmapUsdContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapUsdContainerId]) {
        window.fusionChartInstances[heatmapUsdContainerId].dispose();
      }

      heatmapUsdContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapUsdContainerId,
          width: '100%',
          height: '300', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_usd.caption || '-',
              subCaption: chartData.heatmap_usd.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_usd.rows || { row: [] },
            columns: chartData.heatmap_usd.columns || { column: [] },
            dataset: chartData.heatmap_usd.dataset || [],
            colorrange: chartData.heatmap_usd.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapUsdContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapUsdContainerId].render();
      } catch (error) {
        heatmapUsdContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //******************************** Gráfico de Heatmap Bank USD ******************************************//
  //*******************************************************************************************************//

  const heatmapBankUsdContainerId = 'heatmap_bank_usd';
  const heatmapBankUsdContainer = document.getElementById(heatmapBankUsdContainerId);

  if (heatmapBankUsdContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapUsd = chartData.heatmap_bank_usd?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapUsd) {
      heatmapBankUsdContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_bank_usd?.caption || ''} 
                ${chartData.heatmap_bank_usd?.subCaption ? '| ' + chartData.heatmap_bank_usd.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapBankUsdContainerId]) {
        window.fusionChartInstances[heatmapBankUsdContainerId].dispose();
        delete window.fusionChartInstances[heatmapBankUsdContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapBankUsdContainerId]) {
        window.fusionChartInstances[heatmapBankUsdContainerId].dispose();
      }

      heatmapBankUsdContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapBankUsdContainerId,
          width: '100%',
          height: '800', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_bank_usd.caption || '-',
              subCaption: chartData.heatmap_bank_usd.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_bank_usd.rows || { row: [] },
            columns: chartData.heatmap_bank_usd.columns || { column: [] },
            dataset: chartData.heatmap_bank_usd.dataset || [],
            colorrange: chartData.heatmap_bank_usd.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapBankUsdContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapBankUsdContainerId].render();
      } catch (error) {
        heatmapBankUsdContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************** Gráfico de Heatmap CRC *********************************************//
  //*******************************************************************************************************//

  const heatmapCrcContainerId = 'facturacion_heatmap_crc';
  const heatmapCrcContainer = document.getElementById(heatmapCrcContainerId);

  if (heatmapCrcContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapUsd = chartData.heatmap_crc?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapUsd) {
      heatmapCrcContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_crc?.caption || ''} 
                ${chartData.heatmap_crc?.subCaption ? '| ' + chartData.heatmap_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapCrcContainerId]) {
        window.fusionChartInstances[heatmapCrcContainerId].dispose();
        delete window.fusionChartInstances[heatmapCrcContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapCrcContainerId]) {
        window.fusionChartInstances[heatmapCrcContainerId].dispose();
      }

      heatmapCrcContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapCrcContainerId,
          width: '100%',
          height: '300', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_crc.caption || '-',
              subCaption: chartData.heatmap_crc.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_crc.rows || { row: [] },
            columns: chartData.heatmap_crc.columns || { column: [] },
            dataset: chartData.heatmap_crc.dataset || [],
            colorrange: chartData.heatmap_crc.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapCrcContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapCrcContainerId].render();
      } catch (error) {
        heatmapCrcContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //******************************** Gráfico de Heatmap Bank CRC ******************************************//
  //*******************************************************************************************************//

  const heatmapBankCrcContainerId = 'heatmap_bank_crc';
  const heatmapBankCrcContainer = document.getElementById(heatmapBankCrcContainerId);

  if (heatmapBankCrcContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapCrc = chartData.heatmap_bank_usd?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapCrc) {
      heatmapBankCrcContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_bank_crc?.caption || ''} 
                ${chartData.heatmap_bank_crc?.subCaption ? '| ' + chartData.heatmap_bank_crc.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapBankCrcContainerId]) {
        window.fusionChartInstances[heatmapBankCrcContainerId].dispose();
        delete window.fusionChartInstances[heatmapBankCrcContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapBankCrcContainerId]) {
        window.fusionChartInstances[heatmapBankCrcContainerId].dispose();
      }

      heatmapBankCrcContainer.innerHTML = `
            <div class="chart-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando gráfico...</span>
                </div>
                <p>Cargando visualización...</p>
            </div>`;

      try {
        // Dentro del try del heatmap
        const chartConfig = {
          type: 'heatmap',
          renderAt: heatmapBankCrcContainerId,
          width: '100%',
          height: '800', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_bank_crc.caption || '-',
              subCaption: chartData.heatmap_bank_crc.subCaption || '',
              theme: chartData.theme || 'zune',
              valueFontSize: '12',
              showLabels: '1',
              showValues: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> facturado en <b>$rowlabel</b>',
              valueBgAlpha: '40',

              // Configuración clave para mostrar nombres de meses
              xAxisName: 'Meses',
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',
              labelDisplay: 'WRAP',
              labelWrap: '1',
              labelMaxWidth: '60',
              useEllipsesWhenOverflow: '1',
              rotateLabels: '0', // No rotar etiquetas

              // Control de tamaño de celdas y espacios

              cellHeight: '30',
              cellWidth: '50',
              cellPadding: '5',
              plotSpacePercent: '50', // Más espacio para etiquetas
              canvasPadding: '30',
              canvasTopPadding: '40', // Espacio extra arriba para meses

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '$',
              decimals: '2',
              forceDecimals: '1',

              // Configuraciones de estilo
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              labelFontSize: '13',
              legendItemFontSize: '14',
              outCnvBaseFontSize: '14',
              labelPadding: '10', // Más espacio entre etiquetas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',

              // Mejorar visualización de valores
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90'
            },
            rows: chartData.heatmap_bank_crc.rows || { row: [] },
            columns: chartData.heatmap_bank_crc.columns || { column: [] },
            dataset: chartData.heatmap_bank_crc.dataset || [],
            colorrange: chartData.heatmap_bank_crc.colorrange || {
              gradient: '1',
              minvalue: '0',
              code: '#FCFBFF',
              color: [
                { code: '#FBE1EA', minvalue: '0', maxvalue: '10' },
                { code: '#FEB0BA', minvalue: '10', maxvalue: '20' },
                { code: '#f7f8fd', minvalue: '20', maxvalue: '30' },
                { code: '#DCE8F4', minvalue: '30', maxvalue: '40' },
                { code: '#6B96CB', minvalue: '40', maxvalue: '50' }
              ]
            }
          }
        };

        window.fusionChartInstances[heatmapBankCrcContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapBankCrcContainerId].render();
      } catch (error) {
        heatmapBankCrcContainer.innerHTML = `
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
