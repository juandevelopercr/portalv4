@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
$navbarDetached = $navbarDetached ?? '';
@endphp

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
    id="layout-navbar">
    @endif
    @if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
            @endif

            <!--  Brand demo (display only for navbar-full and hide on below xl) -->
            @if (isset($navbarFull))
            <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                    <span class="app-brand-logo demo">@include('_partials.macros', ['width' => 25, 'withbg' =>
                        'var(--bs-primary)'])</span>
                    <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('variables.templateName')
                        }}</span>
                </a>

                @if (isset($menuHorizontal))
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                    <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
                </a>
                @endif
            </div>
            @endif



            <!-- ! Not required for layout-without-menu -->
            @if (!isset($navbarHideToggle))
            <div
                class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
                <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                    <i class="bx bx-menu bx-md"></i>
                </a>
            </div>
            @endif

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

                @if ($configData['hasCustomizer'] == true)
                <!-- Style Switcher -->
                <div class="navbar-nav align-items-center">
                    <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class='bx bx-md'></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                                    <span><i class='bx bx-sun bx-md me-3'></i>Light</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                                    <span><i class="bx bx-moon bx-md me-3"></i>Dark</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                                    <span><i class="bx bx-desktop bx-md me-3"></i>System</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--/ Style Switcher -->
                @endif

                <ul class="navbar-nav flex-row align-items-center ms-auto">

                    <!-- Language -->
                    <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class='bx bx-globe bx-md'></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                    href="{{ url('lang/en') }}" data-language="en" data-text-direction="ltr">
                                    <span>English</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ app()->getLocale() === 'es' ? 'active' : '' }}"
                                    href="{{ url('lang/es') }}" data-language="es" data-text-direction="ltr">
                                    <span>Spanish</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- /Language -->
                    @php
                    // dd(Auth::user()->profile_photo_url);
                    @endphp
                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img class="w-px-40 h-auto rounded-circle" src="{{ Auth::user()->profile_photo_url }}"
                                    alt="Photo">
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item"
                                    href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                                                    alt class="w-px-40 h-auto rounded-circle">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">
                                                @if (Auth::check())
                                                {{ Auth::user()->name }}
                                                @else
                                                John Doe
                                                @endif
                                            </h6>
                                            <small class="text-muted">{{ session('current_role_name') }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                                    <i class="bx bx-user bx-md me-3"></i><span>{{ __('My Profile') }}</span>
                                </a>
                            </li>
                            @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <li>
                                <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                                    <i class='bx bx-key bx-md me-3'></i><span>API Tokens</span>
                                </a>
                            </li>
                            @endif
                            @if (Auth::User() && Laravel\Jetstream\Jetstream::hasTeamFeatures())
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            <li>
                                <h6 class="dropdown-header">Manage Team</h6>
                            </li>
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ Auth::user() ? route('teams.show', Auth::user()->currentTeam->id) : 'javascript:void(0)' }}">
                                    <i class='bx bx-cog bx-md me-3'></i><span>Team Settings</span>
                                </a>
                            </li>
                            @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <li>
                                <a class="dropdown-item" href="{{ route('teams.create') }}">
                                    <i class='bx bx-user bx-md me-3'></i><span>Create New Team</span>
                                </a>
                            </li>
                            @endcan
                            @if (Auth::user()->allTeams()->count() > 1)
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            <li>
                                <h6 class="dropdown-header">Switch Teams</h6>
                            </li>
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            @endif
                            @if (Auth::user())
                            @foreach (Auth::user()->allTeams() as $team)
                            {{-- Below commented code read by artisan command while installing jetstream. !! Do not
                            remove if you want to use jetstream. --}}

                            <x-switchable-team :team="$team" />
                            @endforeach
                            @endif
                            @endif
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            @if (Auth::check())
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class='bx bx-power-off bx-md me-3'></i><span>{{ __('Logout') }}</span>
                                </a>
                            </li>
                            <form method="POST" id="logout-form" action="{{ route('logout') }}">
                                @csrf
                            </form>
                            @else
                            <li>
                                <a class="dropdown-item"
                                    href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                                    <i class='bx bx-log-in bx-md me-3'></i><span>{{ __('Login') }}</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>
                    <!--/ User -->
                </ul>
            </div>

            @if (!isset($navbarDetached))
        </div>
        @endif
    </nav>
    <!-- / Navbar -->
