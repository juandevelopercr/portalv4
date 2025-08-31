/**
 * Page User List
 */

'use strict';

// Datatable (jquery)
$(function () {
  $(document).ready(function () {
    // Variable declaration for table
    var dt_table = $('.datatables-product'),
      select2 = $('.select2'),
      userView = baseUrl + 'app/user/view/account';

    if (select2.length) {
      var $this = select2;
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: 'Select Country',
        dropdownParent: $this.parent()
      });
    }

    // ajax setup
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    var languageUrl = baseUrl + `assets/datatable/${userLanguage}.json`;

    // Helthsystem datatable
    if (dt_table.length) {
      var dt_user = dt_table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: baseUrl + 'products/ajax/list'
        },
        columns: [
          // columns according to JSON
          { data: '' },
          { data: 'id' },
          { data: 'code' },
          { data: 'name' },
          { data: 'active' },
          { data: 'action' }
        ],
        columnDefs: [
          {
            // For Responsive
            className: 'control',
            searchable: false,
            orderable: false,
            responsivePriority: 2,
            targets: 0,
            render: function (data, type, full, meta) {
              return '';
            }
          },
          {
            searchable: false,
            orderable: false,
            targets: 1,
            render: function (data, type, full, meta) {
              return `<span>${full.fake_id}</span>`;
            }
          },

          {
            // name
            targets: 2,
            responsivePriority: 4,
            render: function (data, type, full, meta) {
              var $user_img = full['photo'],
                $name = full['name'],
                $post = '';
              if ($user_img) {
                // For Avatar image
                console.log(assetsPath);
                var $output =
                  '<img src="' + baseUrl + 'storage/assets/img/products/' + $user_img + '" alt="Product" class="rounded">';
              } else {
                // For Avatar badge
                var stateNum = Math.floor(Math.random() * 6);
                var states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                var $state = states[stateNum],
                  $name = full['name'],
                  $initials = $name.match(/\b\w/g) || [];
                $initials = (($initials.shift() || '') + ($initials.pop() || '')).toUpperCase();
                $output =
                  '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>';
              }
              // Creates full output for row
              var $row_output =
                '<div class="d-flex justify-content-start align-items-center user-name">' +
                '<div class="avatar-wrapper">' +
                '<div class="avatar me-2">' +
                $output +
                '</div>' +
                '</div>' +
                '<div class="d-flex flex-column">' +
                '<span class="emp_name text-truncate">' +
                $name +
                '</span>' +
                '<small class="emp_post text-truncate text-muted">' +
                $post +
                '</small>' +
                '</div>' +
                '</div>';
              return $row_output;
            }
          },
          {
            // code
            targets: 3,
            render: function (data, type, full, meta) {
              var $code = full['code'];

              return '<span class="user-email">' + $code + '</span>';
            }
          },
          {
            // active
            targets: 4,
            className: 'text-center',
            render: function (data, type, full, meta) {
              var $active = full['active'];
              return `${
                $active
                  ? '<i class="bx fs-4 bx-check-shield text-success" title="Activo"></i>'
                  : '<i class="bx fs-4 bx-shield-x text-danger" title="Inactivo"></i>'
              }`;
            }
          },
          {
            // Actions
            targets: -1,
            title: 'Actions',
            searchable: false,
            orderable: false,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-flex align-items-center gap-2">' +
                // Botón de editar
                `<a href="/products/${full['id']}/edit" class="btn btn-sm btn-icon edit-record">` +
                '<i class="bx bx-edit"></i></a>' +
                // Botón de eliminar con AJAX
                `<button class="btn btn-sm btn-icon delete-record" data-id="${full['id']}"><i class="bx bx-trash"></i></button>` +
                '</div>'
              );
            }
          }
        ],
        order: [[2, 'desc']],
        dom:
          '<"row"' +
          '<"col-md-2"<"ms-n2"l>>' +
          '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"fB>>' +
          '>t' +
          '<"row"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        //lengthMenu: [7, 10, 20, 50, 70, 100], //for length of menu
        lengthMenu: [20, 50, 70, 100], //for length of menu
        language: {
          url: languageUrl
        },
        // Buttons with Dropdown
        buttons: [
          {
            extend: 'collection',
            className: 'btn btn-label-secondary dropdown-toggle mx-4',
            text: '<i class="bx bx-export me-2 bx-sm"></i>Export',
            buttons: [
              {
                extend: 'print',
                title: 'Products',
                text: '<i class="bx bx-printer me-2" ></i>Print',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5],
                  // prevent avatar to be print
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                },
                customize: function (win) {
                  //customize print view for dark
                  $(win.document.body)
                    .css('color', config.colors.headingColor)
                    .css('border-color', config.colors.borderColor)
                    .css('background-color', config.colors.body);
                  $(win.document.body)
                    .find('table')
                    .addClass('compact')
                    .css('color', 'inherit')
                    .css('border-color', 'inherit')
                    .css('background-color', 'inherit');
                }
              },
              {
                extend: 'csv',
                title: 'Products',
                text: '<i class="bx bx-file me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5],
                  // prevent avatar to be print
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'excel',
                text: '<i class="bx bxs-file-export me-2"></i>Excel',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'pdf',
                title: 'Products',
                text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5],
                  // prevent avatar to be display
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              },
              {
                extend: 'copy',
                title: 'Products',
                text: '<i class="bx bx-copy me-2" ></i>Copy',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5],
                  // prevent avatar to be copy
                  format: {
                    body: function (inner, coldex, rowdex) {
                      if (inner.length <= 0) return inner;
                      var el = $.parseHTML(inner);
                      var result = '';
                      $.each(el, function (index, item) {
                        if (item.classList !== undefined && item.classList.contains('user-name')) {
                          result = result + item.lastChild.firstChild.textContent;
                        } else if (item.innerText === undefined) {
                          result = result + item.textContent;
                        } else result = result + item.innerText;
                      });
                      return result;
                    }
                  }
                }
              }
            ]
          },
          {
            text: '<i class="bx bx-plus bx-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add New</span>',
            className: 'add-new btn btn-primary',
            action: function (e, dt, node, config) {
              window.location.href = '/products/create'; // Cambia '/ruta/del/formulario' por la ruta que quieras usar
            }
          }
        ],
        // For responsive popup
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return 'Details of ' + data['name'];
              }
            }),
            type: 'column',
            renderer: function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                  ? '<tr data-dt-row="' +
                      col.rowIndex +
                      '" data-dt-column="' +
                      col.columnIndex +
                      '">' +
                      '<td>' +
                      col.title +
                      ':' +
                      '</td> ' +
                      '<td>' +
                      col.data +
                      '</td>' +
                      '</tr>'
                  : '';
              }).join('');

              return data ? $('<table class="table"/><tbody />').append(data) : false;
            }
          }
        }
      });
      // To remove default btn-secondary in export buttons
      $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
    }

    // Delete Record
    $(document).on('click', '.delete-record', function () {
      var user_id = $(this).data('id'),
        dtrModal = $('.dtr-bs-modal.show');

      // hide responsive modal in small screen
      if (dtrModal.length) {
        dtrModal.modal('hide');
      }

      // sweetalert for confirmation of delete
      Swal.fire({
        title: '¿Está seguro?',
        text: 'Usted no podrá revertir esta acción!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, elimínelo!',
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
      }).then(function (result) {
        if (result.value) {
          // delete the data
          $.ajax({
            url: `${baseUrl}` + `products/${user_id}`, // Asegúrate de que `baseUrl` esté correctamente definido
            type: 'DELETE',
            dataType: 'json',
            success: function (response) {
              console.log(response);
              if (response.type == 'success') {
                dt_user.draw(); // Redibuja la tabla después de eliminar el usuario

                // success sweetalert
                Swal.fire({
                  icon: 'success',
                  title: 'Eliminado!',
                  text: 'El Registro ha sido eliminado!',
                  customClass: {
                    confirmButton: 'btn btn-success'
                  }
                });
              } else {
                showToast(response.message, response.type);
              }
            },
            error: function (error) {
              console.log('Error al eliminar el usuario:', error);
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelado',
            text: 'El Registro no se ha eliminado!',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    });

    // edit record
    $(document).on('click', '.edit-record', function () {
      var user_id = $(this).data('id');
      window.location.href = '/products/' + user_id;
    });

    // Filter form control to default size
    // ? setTimeout used for multilingual table initialization
    setTimeout(() => {
      $('.dataTables_filter .form-control').removeClass('form-control-sm');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);

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
  });
});
