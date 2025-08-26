function numeroALetras(num) {
  const UNIDADES = ['CERO', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
  const DECENAS = [
    'DIEZ',
    'ONCE',
    'DOCE',
    'TRECE',
    'CATORCE',
    'QUINCE',
    'DIECISEIS',
    'DIECISIETE',
    'DIECIOCHO',
    'DIECINUEVE'
  ];
  const DECENAS_MAYORES = [
    'VEINTE',
    'VEINTIUNO',
    'VEINTIDOS',
    'VEINTITRES',
    'VEINTICUATRO',
    'VEINTICINCO',
    'VEINTISEIS',
    'VEINTISIETE',
    'VEINTIOCHO',
    'VEINTINUEVE'
  ];
  const CENTENAS = [
    'CIEN',
    'DOSCIENTOS',
    'TRESCIENTOS',
    'CUATROCIENTOS',
    'QUINIENTOS',
    'SEISCIENTOS',
    'SETECIENTOS',
    'OCHOCIENTOS',
    'NOVECIENTOS'
  ];
  const MIL = 'MIL';
  const MILLON = 'MILLON';
  const MILLONES = 'MILLONES';

  function convertirUnidades(num) {
    return UNIDADES[num];
  }

  function convertirDecenas(num) {
    if (num < 10) return convertirUnidades(num);
    if (num < 20) return DECENAS[num - 10];
    if (num < 30) return DECENAS_MAYORES[num - 20];
    const decena = Math.floor(num / 10);
    const unidad = num % 10;
    const decenasText = ['TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    return unidad === 0 ? decenasText[decena - 3] : decenasText[decena - 3] + ' Y ' + convertirUnidades(unidad);
  }

  function convertirCentenas(num) {
    if (num === 100) return 'CIEN';
    if (num < 100) return convertirDecenas(num);
    const centena = Math.floor(num / 100);
    const resto = num % 100;

    // Aquí cambiamos el manejo de "CIENTO"
    if (centena === 1 && resto > 0) {
      return 'CIENTO ' + convertirDecenas(resto);
    }
    return CENTENAS[centena - 1] + (resto > 0 ? ' ' + convertirDecenas(resto) : '');
  }

  function convertirMiles(num) {
    if (num === 1000) return MIL;
    if (num < 1000) return convertirCentenas(num);
    const miles = Math.floor(num / 1000);
    const resto = num % 1000;
    const milesText = miles === 1 ? MIL : convertirCentenas(miles) + ' ' + MIL;
    return resto === 0 ? milesText : milesText + ' ' + convertirCentenas(resto);
  }

  /*
    function convertirMillones(num) {
        if (num === 1000000) return MILLON;
        if (num < 1000000) return convertirMiles(num);
        const millones = Math.floor(num / 1000000);
        const resto = num % 1000000;
        const millonesText = millones === 1 ? MILLON : convertirCentenas(millones) + ' ' + MILLONES;
        return resto === 0 ? millonesText : millonesText + ' ' + convertirMiles(resto);
    }
    */
  function convertirMillones(num) {
    if (num === 1000000) return 'UN MILLON'; // Cambiar "MILLON" a "UN MILLON"
    if (num < 1000000) return convertirMiles(num);
    const millones = Math.floor(num / 1000000);
    const resto = num % 1000000;

    // Si el número de millones es 1, devolver "UN MILLON"
    const millonesText = millones === 1 ? 'UN MILLON' : convertirCentenas(millones) + ' ' + MILLONES;
    return resto === 0 ? millonesText : millonesText + ' ' + convertirMiles(resto);
  }

  function convertirParteEntera(num) {
    if (num === 0) return 'CERO';
    if (num < 100) return convertirDecenas(num);
    if (num < 1000) return convertirCentenas(num);
    if (num < 1000000) return convertirMiles(num);
    return convertirMillones(num);
  }

  function convertirParteDecimal(num) {
    return num < 10 ? '0' + num : num;
  }

  // Ajustar el uso de "UNO" y "UN"
  function ajustarUnidades(texto) {
    // Reemplazar "UN" y "UNO" de acuerdo al contexto
    return texto
      .replace(/UNO\sMIL/g, 'UN MIL')
      .replace(/UNO\s/g, 'UN ')
      .replace(/(\sUN\s)(?!MIL)/g, ' UNO ')
      .replace(/(\sUN\s)(?!MIL)/g, ' UNO ')
      .replace(/VEINTIUN/g, 'VEINTIUNO'); // Caso específico para "veintiuno"
  }

  const parteEntera = Math.floor(num);
  const parteDecimal = Math.round((num - parteEntera) * 100);

  let texto = convertirParteEntera(parteEntera);
  texto += ' CON ' + convertirParteDecimal(parteDecimal) + '/100';

  return ajustarUnidades(texto).toUpperCase();
}

// 👇 Esto expone la función al entorno global
// ✅ Esta línea es CRUCIAL para Vite con @vite(...) en Blade
if (typeof window !== 'undefined') {
  window.numeroALetras = numeroALetras;
}

// Ejemplos de uso
//console.log(numeroALetras(21));          // "VEINTIUNO CON 00/100"
//console.log(numeroALetras(201599.30));   // "DOSCIENTOS UN MIL QUINIENTOS NOVENTA Y NUEVE CON 30/100"
