import './bootstrap';
import Alpine from 'alpinejs';

/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);

window.Alpine = Alpine;
Alpine.start();
