/**
 * Page User List
 */

'use strict';

// Datatable (jquery)
$(function () {
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select value',
        dropdownParent: $this.parent()
      });
    });
  }

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // validating form and updating user's data
  const addNewForm = document.getElementById('productform');
  const actionUrl = addNewForm.action; // Accede al atributo action
  const formData = new FormData(document.getElementById('productform')); // Asegúrate que el formulario tenga el ID correcto
  // Obtén el método del formulario
  const methodInput = addNewForm.querySelector('input[name="_method"]');
  const method = methodInput ? methodInput.value : 'POST'; // Usa el valor del método si existe, de lo contrario, usa 'POST'

  const isEditing = methodInput !== null; // Verifica si el campo existe

  let isSubmitting = false;

  // user form validation
  const fv = FormValidation.formValidation(addNewForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname'
          }
        }
      },
      code: {
        validators: {
          notEmpty: {
            message: 'Please enter code'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.element_vd';
        }
      }),
      /*
      icon: new FormValidation.plugins.Icon({
        valid: 'fa fa-check',
        invalid: 'fa fa-times',
        validating: 'fa fa-refresh'
      }),
      */
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      //defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function (e) {
    // adding or updating user when form successfully validate

    isSubmitting = true; // Marca como enviando
    $('#btn_save').prop('disabled', true); // Deshabilita el botón
    //$('#loadingMessage').show(); // Muestra el mensaje de carga

    var formData = new FormData(addNewForm);

    $.ajax({
      url: actionUrl, // La ruta del formulario
      type: 'POST', // Usa el método real (`PUT` o `POST`)
      contentType: 'multipart/form-data',
      cache: false,
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function (xhr) {
        if (isEditing) {
          // Sobreescribe el método HTTP a `PUT` si es edición
          xhr.setRequestHeader('X-HTTP-Method-Override', 'PUT');
        }
      },
      success: function (response) {
        // Muestra mensaje de éxito
        showToast(response.message, response.type);
        /*
            // sweetalert
            Swal.fire({
              icon: 'success',
              title: `Successfully ${status}!`,
              text: `User ${status} Successfully.`,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
            */
        // Agrega un retraso de 2 segundos antes de redirigir
        if (response.type == 'success') {
          setTimeout(function () {
            $('#btn_save').prop('disabled', false); // Deshabilita el botón
            //$('#loadingMessage').hide(); // Muestra el mensaje de carga
            window.location.href = '/products';
          }, 2000); // 2000 ms = 2 segundos
        } else {
          $('#btn_save').prop('disabled', false); // Deshabilita el botón
          //$('#loadingMessage').hide(); // Muestra el mensaje de carga
        }
        isSubmitting = false;
      },
      error: function (err) {
        isSubmitting = false;
        $('#btn_save').prop('disabled', false); // Deshabilita el botón
        //$('#loadingMessage').hide(); // Muestra el mensaje de carga

        // Maneja y muestra errores
        let errorMessage = 'Ocurrió un error';

        showToast(errorMessage, 'error');
        /*
            Swal.fire({
              title: 'Duplicate Entry!',
              text: 'Your email should be unique.',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
            */
      }
    });

    return false;
  });

  const phoneMaskList = document.querySelectorAll('.phone-mask');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }

  // Función para mostrar el toast
  function showToast(message, type) {
    let messageType = 'bg-primary';
    if (type === 'success') {
      messageType = 'bg-primary';
    } else if (type === 'error') {
      messageType = 'bg-danger';
    } else if (type === 'warning') {
      messageType = 'bg-warning';
    } else if (type === 'info') {
      messageType = 'bg-info';
    }

    // Configura el mensaje y tipo
    let toastElement = document.querySelector('.toast-ex');
    let toastMessage = document.querySelector('#toast-message');
    toastElement.classList.remove('bg-success', 'bg-danger', 'bg-primary', 'bg-warning', 'bg-info'); // Quita clases previas
    let selectedAnimation = 'animate__tada';

    let places = 'top-0 end-05';
    let selectedPlacement = places.split(' ');

    toastElement.classList.add(messageType, selectedAnimation); // Agrega clase de tipo
    DOMTokenList.prototype.add.apply(toastElement.classList, selectedPlacement);
    toastMessage.textContent = message; // Define el mensaje

    // Inicializa y muestra el toast
    let toastInstance = new bootstrap.Toast(toastElement);
    toastInstance.show();
  }

  // Cargar estados cuando se selecciona un país
  $('#country_id').on('change', function () {
    let countryId = $(this).val();
    $('#state_id').empty().append('<option value="">Seleccione un estado</option>');
    $('#city_id').empty().append('<option value="">Seleccione una ciudad</option>');

    if (countryId) {
      $.ajax({
        url: '/get-states/' + countryId,
        type: 'GET',
        success: function (states) {
          $.each(states, function (key, state) {
            $('#state_id').append(`<option value="${state.id}">${state.name}</option>`);
          });
        }
      });
    }
  });
});
