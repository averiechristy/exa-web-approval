@php
    $user = auth()->user();
    $isSuperadmin = $user->isSuperadmin();
@endphp

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-text mx-3">EXA E-APPROVAL</div>
    </a>

    <hr class="sidebar-divider">

    {{-- DASHBOARD --}}
    @if(!$isSuperadmin)
    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <span>Dashboard</span>
        </a>
    </li>
    @endif

    {{-- MASTER DATA --}}
    @if($isSuperadmin)

        @php
            $isMasterActive = request()->routeIs('organization.*') ||
                              request()->routeIs('division.*') ||
                              request()->routeIs('role.*') ||
                              request()->routeIs('user.*') ||
                              request()->routeIs('workflow.*') ||
                              request()->routeIs('folders.*');
        @endphp

        <div class="sidebar-heading">
            Master Data
        </div>

        <li class="nav-item {{ $isMasterActive ? 'active' : '' }}">
            <a class="nav-link collapsed {{ $isMasterActive ? '' : 'collapsed' }}"
               href="#"
               data-toggle="collapse"
               data-target="#collapseMaster"
               aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}">
               
                <i class="fas fa-database"></i>
                <span>Master Data</span>
            </a>

            <div id="collapseMaster"
                 class="collapse {{ $isMasterActive ? 'show' : '' }}">
                 
                <div class="bg-white py-2 collapse-inner rounded">

                    <a class="collapse-item {{ request()->routeIs('organization.*') ? 'active' : '' }}"
                       href="{{ route('organization.index') }}">
                        Organization
                    </a>

                    <a class="collapse-item {{ request()->routeIs('division.*') ? 'active' : '' }}"
                       href="{{ route('division.index') }}">
                        Divisi
                    </a>

                    <a class="collapse-item {{ request()->routeIs('role.*') ? 'active' : '' }}"
                       href="{{ route('role.index') }}">
                        Role
                    </a>

                    <a class="collapse-item {{ request()->routeIs('user.*') ? 'active' : '' }}"
                       href="{{ route('user.index') }}">
                        User
                    </a>

                    <a class="collapse-item {{ request()->routeIs('workflow.*') ? 'active' : '' }}"
                       href="{{ route('workflow.index') }}">
                        Workflow
                    </a>

                    <a class="collapse-item {{ request()->routeIs('folders.*') ? 'active' : '' }}"
                       href="{{ route('folders.index') }}">
                        Folder
                    </a>

                </div>
            </div>
        </li>

        <hr class="sidebar-divider">
    @endif

    {{-- USER MENU --}}
    @if(!$isSuperadmin)
        <div class="sidebar-heading">My Documents</div>

        <li class="nav-item {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i class="fas fa-inbox"></i>
                <span>Inbox</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('sent.*') ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i class="fas fa-paper-plane"></i>
                <span>Sent</span>
            </a>
        </li>

        <li class="nav-item {{ request()->routeIs('shared.*') ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i class="fas fa-share-alt"></i>
                <span>Shared With Me</span>
            </a>
        </li>

        <hr class="sidebar-divider">
    @endif

    {{-- UPLOAD --}}
    <li class="nav-item {{ request()->routeIs('upload.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('upload.index') }}">
            <i class="fas fa-upload"></i>
            <span>Upload Document</span>
        </a>
    </li>

</ul>