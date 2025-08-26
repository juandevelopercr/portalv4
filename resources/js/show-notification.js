/**
 * Page User List
 */

'use strict';
// Función para mostrar el toast
function showToast(message, type) {
  let messageType = 'bg-primary';

  // Configura el tipo de mensaje basado en el tipo proporcionado
  if (type === 'success') {
      messageType = 'bg-primary';
  } else if (type === 'error') {
      messageType = 'bg-danger';
  } else if (type === 'warning') {
      messageType = 'bg-warning';
  } else if (type === 'info') {
      messageType = 'bg-info';
  }

  // Selecciona los elementos del toast
  let toastElement = document.querySelector('.toast-ex');
  let toastMessage = document.querySelector('#toast-message');

  // Quita clases previas de tipo y animación
  toastElement.classList.remove('bg-success', 'bg-danger', 'bg-primary', 'bg-warning', 'bg-info');

  // Configura animación y posición
  let selectedAnimation = 'animate__tada';
  let places = 'top-0 end-0'; // Posición del toast
  let selectedPlacement = places.split(' ');

  // Agrega la clase de tipo y posición
  toastElement.classList.add(messageType, selectedAnimation);
  DOMTokenList.prototype.add.apply(toastElement.classList, selectedPlacement);

  // Define el mensaje en el toast
  toastMessage.textContent = message;

  // Inicializa y muestra el toast
  let toastInstance = new bootstrap.Toast(toastElement);
  toastInstance.show();
}
