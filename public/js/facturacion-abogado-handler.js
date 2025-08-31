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
  //********************************* Gráfico de mscolumn3d facturas usd***********************************//
  //*******************************************************************************************************//

  const mscolumn3dFacturasUSDContainerId = 'facturas_usd_bar';
  const mscolumn3dFacturasUSDContainer = document.getElementById(mscolumn3dFacturasUSDContainerId);

  if (mscolumn3dFacturasUSDContainer) {
    // Verificar si hay datos
    const hasDataFacturasUSD = chartData.mscolumn3d_facturas_usd?.data?.categories?.[0]?.category?.length > 0;

    if (!hasDataFacturasUSD) {
      mscolumn3dFacturasUSDContainer.innerHTML = `
      <div class="no-data-container">
        <div class="no-data-content">
          <div class="no-data-icon">
            <i class="fas fa-chart-heatmap"></i>
          </div>
          <h3 class="no-data-title">No hay datos disponibles</h3>
          <p class="no-data-message">
            ${chartData.mscolumn3d_facturas_usd?.caption || ''} 
            ${chartData.mscolumn3d_facturas_usd?.subCaption ? '| ' + chartData.mscolumn3d_facturas_usd.subCaption : ''}
          </p>
          <p class="no-data-message">Intenta con otros filtros o parámetros</p>
        </div>
      </div>`;

      if (window.fusionChartInstances[mscolumn3dFacturasUSDContainerId]) {
        window.fusionChartInstances[mscolumn3dFacturasUSDContainerId].dispose();
        delete window.fusionChartInstances[mscolumn3dFacturasUSDContainerId];
      }
    } else {
      if (window.fusionChartInstances[mscolumn3dFacturasUSDContainerId]) {
        window.fusionChartInstances[mscolumn3dFacturasUSDContainerId].dispose();
      }

      mscolumn3dFacturasUSDContainer.innerHTML = `
      <div class="chart-loader">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando gráfico...</span>
        </div>
        <p>Cargando visualización...</p>
      </div>`;

      try {
        const chartConfig = {
          type: 'column3d',
          renderAt: mscolumn3dFacturasUSDContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.mscolumn3d_facturas_usd.caption || 'Facturación por abogado USD',
              subCaption: chartData.mscolumn3d_facturas_usd.subCaption || '',
              xAxisName: chartData.mscolumn3d_facturas_usd.xAxisName || 'Abogado',
              yAxisName: chartData.mscolumn3d_facturas_usd.yAxisName || 'Facturación',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 0).join(','),
              numberPrefix: '$',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '2',
              forceDecimals: '0',
              exportEnabled: '1',

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

              // CONFIGURACIÓN PARA VALORES NO ROTADOS
              showValues: '1', // Mostrar valores
              valuePosition: 'top', // Posición de los valores
              valueAngle: '0', // Ángulo 0° (horizontal)
              valueFont: 'Arial', // Fuente clara
              valueFontSize: '12', // Tamaño adecuado
              valueFontColor: '#333333', // Color contrastante
              valueBgColor: '#FFFFFF90', // Fondo semitransparente
              valueBgAlpha: '40', // Opacidad del fondo
              valueBorderColor: '#CCCCCC', // Borde sutil
              valueBorderThickness: '1', // Grosor del borde
              valueBorderAlpha: '50', // Opacidad del borde
              valuePadding: '5', // Espacio interno
              valueBorderRadius: '3', // Esquinas redondeadas

              // Tamaños de fuente
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '11', // Tamaño reducido para etiquetas
              //valueFontSize: '12',
              outCnvBaseFontSize: '14',

              // Elementos del gráfico
              showValues: '1', // Mostrar valores sobre columnas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',
              plotSpacePercent: '75', // Más espacio para el gráfico

              // Efectos 3D
              use3DLighting: '1',
              showShadow: '1',
              animation: '1',
              divLineEffect: 'FADE'
            },
            // CORRECCIÓN CLAVE: Usar "data" en lugar de "dataset"
            data:
              chartData.mscolumn3d_facturas_usd.data.categories?.[0]?.category?.map((category, index) => {
                return {
                  label: category.label,
                  value: chartData.mscolumn3d_facturas_usd.data.dataset?.[0]?.data?.[index]?.value || 0
                };
              }) || []
          }
        };

        window.fusionChartInstances[mscolumn3dFacturasUSDContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[mscolumn3dFacturasUSDContainerId].render();
      } catch (error) {
        mscolumn3dFacturasUSDContainer.innerHTML = `
        <div class="chart-error">
          <i class="fas fa-exclamation-triangle text-danger"></i>
          <h3>Error en el gráfico</h3>
          <p>${error.message || 'Por favor intenta nuevamente'}</p>
        </div>`;
      }
    }
  }

  //*******************************************************************************************************//
  //********************************* Gráfico de mscolumn3d facturas crc***********************************//
  //*******************************************************************************************************//

  const mscolumn3dFacturasCRCContainerId = 'facturas_crc_bar';
  const mscolumn3dFacturasCRCContainer = document.getElementById(mscolumn3dFacturasCRCContainerId);

  if (mscolumn3dFacturasCRCContainer) {
    // Verificar si hay datos
    const hasDataFacturasCRC = chartData.mscolumn3d_facturas_crc?.data?.categories?.[0]?.category?.length > 0;

    if (!hasDataFacturasCRC) {
      mscolumn3dFacturasCRCContainer.innerHTML = `
      <div class="no-data-container">
        <div class="no-data-content">
          <div class="no-data-icon">
            <i class="fas fa-chart-heatmap"></i>
          </div>
          <h3 class="no-data-title">No hay datos disponibles</h3>
          <p class="no-data-message">
            ${chartData.mscolumn3d_facturas_crc?.caption || ''} 
            ${chartData.mscolumn3d_facturas_crc?.subCaption ? '| ' + chartData.mscolumn3d_facturas_crc.subCaption : ''}
          </p>
          <p class="no-data-message">Intenta con otros filtros o parámetros</p>
        </div>
      </div>`;

      if (window.fusionChartInstances[mscolumn3dFacturasCRCContainerId]) {
        window.fusionChartInstances[mscolumn3dFacturasCRCContainerId].dispose();
        delete window.fusionChartInstances[mscolumn3dFacturasCRCContainerId];
      }
    } else {
      if (window.fusionChartInstances[mscolumn3dFacturasCRCContainerId]) {
        window.fusionChartInstances[mscolumn3dFacturasCRCContainerId].dispose();
      }

      mscolumn3dFacturasCRCContainer.innerHTML = `
      <div class="chart-loader">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando gráfico...</span>
        </div>
        <p>Cargando visualización...</p>
      </div>`;

      try {
        const chartConfig = {
          type: 'column3d',
          renderAt: mscolumn3dFacturasCRCContainerId,
          width: '100%',
          height: '500',
          dataFormat: 'json',
          dataSource: {
            chart: {
              caption: chartData.mscolumn3d_facturas_crc.caption || 'Facturación por abogado CRC',
              subCaption: chartData.mscolumn3d_facturas_crc.subCaption || '',
              xAxisName: chartData.mscolumn3d_facturas_crc.xAxisName || 'Abogado',
              yAxisName: chartData.mscolumn3d_facturas_crc.yAxisName || 'Facturación',
              theme: chartData.theme || 'zune',
              paletteColors: getRotatedColors(chartData.theme || 'zune', 2).join(','),
              numberPrefix: '¢',
              formatNumberScale: '0',
              decimalSeparator: ',',
              thousandSeparator: '.',
              decimals: '2',
              forceDecimals: '0',
              exportEnabled: '1',

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

              // CONFIGURACIÓN PARA VALORES NO ROTADOS
              showValues: '1', // Mostrar valores
              valuePosition: 'top', // Posición de los valores
              valueAngle: '0', // Ángulo 0° (horizontal)
              valueFont: 'Arial', // Fuente clara
              valueFontSize: '12', // Tamaño adecuado
              valueFontColor: '#333333', // Color contrastante
              valueBgColor: '#FFFFFF90', // Fondo semitransparente
              valueBgAlpha: '40', // Opacidad del fondo
              valueBorderColor: '#CCCCCC', // Borde sutil
              valueBorderThickness: '1', // Grosor del borde
              valueBorderAlpha: '50', // Opacidad del borde
              valuePadding: '5', // Espacio interno
              valueBorderRadius: '3', // Esquinas redondeadas

              // Tamaños de fuente
              baseFontSize: '14',
              captionFontSize: '20',
              subCaptionFontSize: '16',
              xAxisNameFontSize: '15',
              yAxisNameFontSize: '15',
              xAxisNameFontBold: '1',
              yAxisNameFontBold: '1',
              labelFontSize: '11', // Tamaño reducido para etiquetas
              //valueFontSize: '12',
              outCnvBaseFontSize: '14',

              // Elementos del gráfico
              showValues: '1', // Mostrar valores sobre columnas
              showBorder: '1',
              borderColor: '#CCCCCC',
              borderThickness: '1',
              borderAlpha: '50',
              plotSpacePercent: '75', // Más espacio para el gráfico

              // Efectos 3D
              use3DLighting: '1',
              showShadow: '1',
              animation: '1',
              divLineEffect: 'FADE'
            },
            // CORRECCIÓN CLAVE: Usar "data" en lugar de "dataset"
            data:
              chartData.mscolumn3d_facturas_crc.data.categories?.[0]?.category?.map((category, index) => {
                return {
                  label: category.label,
                  value: chartData.mscolumn3d_facturas_crc.data.dataset?.[0]?.data?.[index]?.value || 0
                };
              }) || []
          }
        };

        window.fusionChartInstances[mscolumn3dFacturasCRCContainerId] = new FusionCharts(chartConfig);
        window.fusionChartInstances[mscolumn3dFacturasCRCContainerId].render();
      } catch (error) {
        mscolumn3dFacturasCRCContainer.innerHTML = `
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
