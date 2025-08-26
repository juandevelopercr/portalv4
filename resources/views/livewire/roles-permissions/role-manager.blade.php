<div>
  <div class="row">
    <div class="col-xl">
      <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            {{ __('Roles') }}
          </h5>
          <small class="text-body float-end">Default label</small>
        </div>
        <div class="card-body">
          <div class="col-md-12">
            <p class="mb-6">{{ __("A role provided access to predefined menus and features") }}</p>
            <!-- Role cards -->
            <div class="row g-6">
              @foreach($roles as $role)
              <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <h6 class="fw-normal mb-0 text-body">{{ __('Total') }}: {{ $role['users_count']
                        }}
                        @if ($role['users_count'] > 1)
                        {{ __('Users') }}
                        @else
                        {{ __('User') }}
                        @endif
                      </h6>
                      <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                        @foreach($role['users'] as $user)
                        @php
                        $user_img = $user->profile_photo_path;
                        $name = $user->name;

                        preg_match_all('/\b\w/', $name, $matches);
                        $initials=strtoupper(substr(implode('', $matches[0]), 0, 2)); // Toma las primeras dos letras

                        if ($user_img) {
                        // Imagen de perfil del usuario
                        $imageUrl = asset('storage/assets/img/avatars/' . $user_img);
                        }
                        else {
                        // Avatar con iniciales
                        $states = [' success', 'danger' , 'warning' , 'info' , 'dark' , 'primary' , 'secondary' ];
                        $stateNum=array_rand($states); $state=$states[$stateNum]; // Obtener iniciales
                        $output='<span class="avatar-initial rounded-circle bg-label-' . $state . '">' . $initials .
                          '</span>';
                        }
                        @endphp
                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                          title="{{ $user->name }}" class="avatar pull-up">
                          @if($user_img)
                          <img class="rounded-circle" src="{{ $imageUrl }}" alt="{{ $initials }}">
                          @else
                          <span class="avatar-initial rounded-circle bg-label-{{ $state }}">{{ $initials }}</span>
                          @endif
                        </li>
                        @endforeach
                        @if ($role['users_count'] > 5)
                        <li class="avatar">
                          <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="4 more">+{{ $role['users_count'] - 4 }}</span>
                        </li>
                        @endif
                      </ul>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                      <div class="role-heading">
                        <h5 class="mb-1">{{ $role['name'] }}</h5>
                        <a href="#" wire:click="edit({{ $role['id'] }})"><span>{{ __("Edit") }} Rol</span></a>
                      </div>
                      <a href="javascript:void(0);"><i class="bx bx-copy bx-md text-muted"></i></a>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach

              <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100">
                  <div class="row h-100">
                    <div class="col-sm-5">
                      <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4 ps-6">
                        <img src="{{asset('assets/img/illustrations/sitting-girl-with-laptop.png')}}" class="img-fluid"
                          alt="Image" width="120" data-app-light-img="illustrations/sitting-girl-with-laptop.png"
                          data-app-dark-img="illustrations/sitting-girl-with-laptop.png">
                      </div>
                    </div>
                    <div class="col-sm-7">
                      <div class="card-body text-sm-end text-center ps-sm-0">
                        <button wire:click="create()" class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">{{
                          __("Create") }} Rol</button>
                        <p class="mb-0"> {{ __("Add new role") }}, <br> {{ __("if it doesn't exist") }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--/ Role cards -->

            <!-- Add Role Modal -->
            @if($action == 'create' || $action == 'edit')
            @include('_partials/_modals/modal-role')
            @endif
            <!-- / Add Role Modal -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
