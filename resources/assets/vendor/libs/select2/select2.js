import select2 from 'select2/dist/js/select2.full';

try {
  window.select2 = select2;
} catch (e) {}
select2();

if (window.jQuery && window.jQuery.fn.select2) {
  window.jQuery.fn.select2.defaults.set('language', 'es');
}

export { select2 };
