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
  const addNewForm = document.getElementById('userform');
  const actionUrl = addNewForm.action; // Accede al atributo action
  const formData = new FormData(document.getElementById('userform')); // Asegúrate que el formulario tenga el ID correcto
  // Obtén el método del formulario
  const methodInput = addNewForm.querySelector('input[name="_method"]');
  const method = methodInput ? methodInput.value : 'POST'; // Usa el valor del método si existe, de lo contrario, usa 'POST'

  const isEditing = methodInput !== null; // Verifica si el campo existe

  let isSubmitting = false;
  const rolesField = jQuery('#roles');

  const formValidationSelect2Country = jQuery(addNewForm.querySelector('[name="county_id"]'));
  const formValidationSelect2State = jQuery(addNewForm.querySelector('[name="state_id"]'));
  const formValidationSelect2City = jQuery(addNewForm.querySelector('[name="city_id"]'));

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
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'The value is not a valid email address'
          }
        }
      },
      password: {
        validators: {
          notEmpty: {
            enabled: !isEditing, // Solo requerido si no es edición
            message: 'Please enter your password'
          },
          stringLength: {
            min: 6,
            message: 'The password must be at least 6 characters long'
          }
        }
      },
      password_confirmation: {
        validators: {
          identical: {
            compare: function () {
              return addNewForm.querySelector('[name="password"]').value;
            },
            message: 'The password and its confirm are not the same'
          }
        }
      },
      country_id: {
        validators: {
          notEmpty: {
            message: 'Please select a country'
          }
        }
      },
      'roles[]': {
        validators: {
          notEmpty: {
            message: 'Please select at least one role'
          }
        }
        /*
        validators: {
          callback: {
            message: 'Please choose 2-4 color you like most',
            callback: function (input) {
              // Get the selected options
              const options = rolesField.select2('data');
              return options !== null && options.length >= 2 && options.length <= 4;
            }
          }
        }
        */
      },
      distributor_id: {
        validators: {
          notEmpty: {
            message: 'Please select one distributor'
          }
        }
      },
      'hospitals[]': {
        validators: {
          notEmpty: {
            message: 'Please select at least one hospital'
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

    /*
    var formData = new FormData();

    //let _token = $('meta[name="csrf-token"]').attr('content');
    var name = $('#name').val();
    var email = $('#email').val();
    var password = $('#password').val();
    var country_id = $('#country_id').val();
    var state_id = $('#state_id').val();
    var city_id = $('#city_id').val();
    var address = $('#address').val();
    var postal_code = $('#postal_code').val();
    var region_id = $('#region_id').val();
    var distributor_id = $('#distributor_id').val();

    formData.append('name', name);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('country_id', country_id);
    formData.append('state_id', state_id);
    formData.append('city_id', city_id);
    formData.append('address', address);
    formData.append('postal_code', postal_code);
    formData.append('region_id', region_id);
    formData.append('distributor_id', distributor_id);
    formData.append('profile_photo_path', profile_photo_path);
    // Añade la imagen
    var profilePhotoPath = $('#profile_photo_path').prop('files')[0];
    if (profilePhotoPath) {
      formData.append('profile_photo_path', profilePhotoPath);
    }
    */
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
            window.location.href = '/users';
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

  /*
  // Select2 (Country)
  if (formValidationSelect2Country.length) {
    formValidationSelect2Country.wrap('<div class="position-relative"></div>');
    formValidationSelect2Country
      .select2({
        placeholder: 'Select country',
        dropdownParent: formValidationSelect2Country.parent()
      })
      .on('change', function () {
        fv.revalidateField('county_id');
      });
  }
  */

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

  // Cargar ciudades cuando se selecciona un estado
  $('#state_id').on('change', function () {
    let stateId = $(this).val();
    $('#city_id').empty().append('<option value="">Seleccione una ciudad</option>');

    if (stateId) {
      $.ajax({
        url: '/get-cities/' + stateId,
        type: 'GET',
        success: function (cities) {
          $.each(cities, function (key, city) {
            $('#city_id').append(`<option value="${city.id}">${city.name}</option>`);
          });
        }
      });
    }
  });
});
