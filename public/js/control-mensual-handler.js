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
  //********************************** Gráfico de Heatmap One *********************************************//
  //*******************************************************************************************************//

  const heatmapOneContainerId = 'firmas_caratulas_mes_one';
  const heatmapOneContainer = document.getElementById(heatmapOneContainerId);

  if (heatmapOneContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapOne = chartData.heatmap_one?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapOne) {
      heatmapOneContainer.innerHTML = `
        <div class="no-data-container">
          <div class="no-data-content">
            <div class="no-data-icon">
              <i class="fas fa-chart-heatmap"></i>
            </div>
            <h3 class="no-data-title">No hay datos disponibles</h3>
            <p class="no-data-message">
              ${chartData.heatmap_one?.caption || ''} 
              ${chartData.heatmap_one?.subCaption ? '| ' + chartData.heatmap_one.subCaption : ''}
            </p>
            <p class="no-data-message">Intenta con otros filtros o parámetros</p>
          </div>
        </div>
      `;

      if (window.fusionChartInstances[heatmapOneContainerId]) {
        window.fusionChartInstances[heatmapOneContainerId].dispose();
        delete window.fusionChartInstances[heatmapOneContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapOneContainerId]) {
        window.fusionChartInstances[heatmapOneContainerId].dispose();
      }

      heatmapOneContainer.innerHTML = `
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
          renderAt: heatmapOneContainerId,
          width: '100%',
          height: '400', // Aumenta la altura para espacio adicional
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_one.caption || '-',
              subCaption: chartData.heatmap_one.subCaption || '',
              theme: chartData.theme || 'zune',

              // CONFIGURACIÓN CLAVE PARA ROTACIÓN
              rotateLabels: '45', // Ángulo de rotación
              slantLabel: '1', // Forzar rotación
              centerLabel: '0', // Desactivar centrado
              labelDisplay: 'AUTO', // Ajuste automático
              labelWrap: '0', // Desactivar envoltura
              useEllipsesWhenOverflow: '0', // Sin puntos suspensivos

              // AJUSTES DE ESPACIO
              canvasBottomMargin: '100', // Espacio inferior aumentado
              canvasPadding: '40', // Padding general
              xAxisNamePadding: '30', // Espacio para nombre del eje
              labelPadding: '15', // Espacio entre etiquetas

              // TAMAÑOS DE FUENTE
              labelFontSize: '10', // Reducir tamaño de fuente
              valueFontSize: '10',
              baseFontSize: '12',

              // Mejoras de visualización
              showValues: '1',
              showLabels: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> <b>$rowlabel</b>',
              valueBgAlpha: '40',
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90',

              // Títulos
              xAxisName: 'Días del mes', // Más descriptivo
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '',
              decimals: '0', // Sin decimales (cantidades enteras)
              forceDecimals: '0',

              // Bordes
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            rows: chartData.heatmap_one.rows || { row: [] },
            columns: chartData.heatmap_one.columns || { column: [] },
            dataset: chartData.heatmap_one.dataset || [],
            colorrange: chartData.heatmap_one.colorrange || {
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

        window.fusionChartInstances[heatmapOneContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapOneContainerId].render();
      } catch (error) {
        heatmapOneContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************** Gráfico de Heatmap Two *********************************************//
  //*******************************************************************************************************//

  const heatmapTwoContainerId = 'firmas_caratulas_mes_two';
  const heatmapTwoContainer = document.getElementById(heatmapTwoContainerId);

  if (heatmapTwoContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapTwo = chartData.heatmap_two?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapTwo) {
      heatmapTwoContainer.innerHTML = `
        <div class="no-data-container">
          <div class="no-data-content">
            <div class="no-data-icon">
              <i class="fas fa-chart-heatmap"></i>
            </div>
            <h3 class="no-data-title">No hay datos disponibles</h3>
            <p class="no-data-message">
              ${chartData.heatmap_two?.caption || ''} 
              ${chartData.heatmap_two?.subCaption ? '| ' + chartData.heatmap_two.subCaption : ''}
            </p>
            <p class="no-data-message">Intenta con otros filtros o parámetros</p>
          </div>
        </div>
      `;

      if (window.fusionChartInstances[heatmapTwoContainerId]) {
        window.fusionChartInstances[heatmapTwoContainerId].dispose();
        delete window.fusionChartInstances[heatmapTwoContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapTwoContainerId]) {
        window.fusionChartInstances[heatmapTwoContainerId].dispose();
      }

      heatmapTwoContainer.innerHTML = `
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
          renderAt: heatmapTwoContainerId,
          width: '100%',
          height: '300', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_two.caption || '-',
              subCaption: chartData.heatmap_two.subCaption || '',
              theme: chartData.theme || 'zune',

              // Ajustes clave para etiquetas horizontales
              rotateLabels: '0', // Desactivar rotación
              labelDisplay: 'AUTO', // Ajuste automático de etiquetas
              labelWrap: '0', // Desactivar envoltura de texto
              useEllipsesWhenOverflow: '0', // No usar puntos suspensivos
              centerLabel: '1', // Centrar texto en celdas

              // Tamaño y posición
              cellHeight: '30',
              cellWidth: '35', // Ancho reducido para más columnas
              cellPadding: '3',
              plotSpacePercent: '75', // Más espacio para el gráfico
              canvasPadding: '20', // Padding reducido
              canvasTopPadding: '30',

              // Fuentes y texto
              valueFontSize: '11',
              labelFontSize: '12', // Tamaño adecuado para etiquetas
              baseFontSize: '13',
              captionFontSize: '18',
              subCaptionFontSize: '14',
              labelPadding: '5', // Espacio entre etiquetas

              // Mejoras de visualización
              showValues: '1',
              showLabels: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> <b>$rowlabel</b>',
              valueBgAlpha: '40',
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90',

              // Títulos
              xAxisName: 'Días del mes', // Más descriptivo
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '',
              decimals: '0', // Sin decimales (cantidades enteras)
              forceDecimals: '0',

              // Bordes
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            rows: chartData.heatmap_two.rows || { row: [] },
            columns: chartData.heatmap_two.columns || { column: [] },
            dataset: chartData.heatmap_two.dataset || [],
            colorrange: chartData.heatmap_two.colorrange || {
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

        window.fusionChartInstances[heatmapTwoContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapTwoContainerId].render();
      } catch (error) {
        heatmapTwoContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <h3>Error en el gráfico</h3>
                    <p>${error.message || 'Por favor intenta nuevamente'}</p>
                </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************** Gráfico de Heatmap Two *********************************************//
  //*******************************************************************************************************//

  const heatmapThreeContainerId = 'firmas_caratulas_mes_three';
  const heatmapThreeContainer = document.getElementById(heatmapThreeContainerId);

  if (heatmapThreeContainer) {
    // Verificar si hay datos para mostrar
    const hasDataHeatmapThree = chartData.heatmap_three?.dataset?.some(d =>
      d.data?.some(v => v.value !== 0 && v.value !== null)
    );

    if (!hasDataHeatmapThree) {
      heatmapThreeContainer.innerHTML = `
        <div class="no-data-container">
            <div class="no-data-content">
              <div class="no-data-icon">
                <i class="fas fa-chart-heatmap"></i>
              </div>
              <h3 class="no-data-title">No hay datos disponibles</h3>
              <p class="no-data-message">
                ${chartData.heatmap_three?.caption || ''} 
                ${chartData.heatmap_three?.subCaption ? '| ' + chartData.heatmap_three.subCaption : ''}
              </p>
              <p class="no-data-message">Intenta con otros filtros o parámetros</p>
            </div>
          </div>
        `;

      if (window.fusionChartInstances[heatmapThreeContainerId]) {
        window.fusionChartInstances[heatmapThreeContainerId].dispose();
        delete window.fusionChartInstances[heatmapThreeContainerId];
      }
    } else {
      if (window.fusionChartInstances[heatmapThreeContainerId]) {
        window.fusionChartInstances[heatmapThreeContainerId].dispose();
      }

      heatmapThreeContainer.innerHTML = `
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
          renderAt: heatmapThreeContainerId,
          width: '100%',
          height: '300', // Aumentamos la altura para mejor visualización
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.heatmap_three.caption || '-',
              subCaption: chartData.heatmap_three.subCaption || '',
              theme: chartData.theme || 'zune',

              // Ajustes clave para etiquetas horizontales
              rotateLabels: '0', // Desactivar rotación
              labelDisplay: 'AUTO', // Ajuste automático de etiquetas
              labelWrap: '0', // Desactivar envoltura de texto
              useEllipsesWhenOverflow: '0', // No usar puntos suspensivos
              centerLabel: '1', // Centrar texto en celdas

              // Tamaño y posición
              cellHeight: '30',
              cellWidth: '35', // Ancho reducido para más columnas
              cellPadding: '3',
              plotSpacePercent: '75', // Más espacio para el gráfico
              canvasPadding: '20', // Padding reducido
              canvasTopPadding: '30',

              // Fuentes y texto
              valueFontSize: '11',
              labelFontSize: '12', // Tamaño adecuado para etiquetas
              baseFontSize: '13',
              captionFontSize: '18',
              subCaptionFontSize: '14',
              labelPadding: '5', // Espacio entre etiquetas

              // Mejoras de visualización
              showValues: '1',
              showLabels: '1',
              showPlotBorder: '1',
              placeXAxisLabelsOnTop: '1',
              mapByCategory: '0',
              showLegend: '1',
              plotToolText: '<b>$displayValue</b> <b>$rowlabel</b>',
              valueBgAlpha: '40',
              valueFontColor: '#333333',
              valueBgColor: '#FFFFFF90',

              // Títulos
              xAxisName: 'Días del mes', // Más descriptivo
              xAxisNameFontSize: '14',
              xAxisNameFontBold: '1',

              // Formato numérico
              formatNumber: '1',
              numberPrefix: '',
              decimals: '0', // Sin decimales (cantidades enteras)
              forceDecimals: '0',

              // Bordes
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50'
            },
            rows: chartData.heatmap_three.rows || { row: [] },
            columns: chartData.heatmap_three.columns || { column: [] },
            dataset: chartData.heatmap_three.dataset || [],
            colorrange: chartData.heatmap_three.colorrange || {
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

        window.fusionChartInstances[heatmapThreeContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[heatmapThreeContainerId].render();
      } catch (error) {
        heatmapThreeContainer.innerHTML = `
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
