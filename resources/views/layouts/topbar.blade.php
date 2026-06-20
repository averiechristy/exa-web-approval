<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    @php
        $user = auth()->user();
        $isSuperadmin = $user->isSuperadmin();
        $userAccesses = $user->userAccesses ?? collect();
        $activeAccessId = session('active_access_id');
        $active = $userAccesses->firstWhere('id', $activeAccessId);
    @endphp

    <ul class="navbar-nav ml-auto">

       {{-- 🔽 SWITCH ORGANIZATION (HANYA USER BIASA) --}}
        @if(!$isSuperadmin)
        <li class="nav-item dropdown no-arrow mr-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center px-3 py-2" 
            href="#" 
            data-toggle="dropdown"
            style="border-radius: 8px; background: #f8fafc;">
                
                <i class="fas fa-building text-primary mr-2"></i>
                
                <div class="flex-grow-1 text-left">
                    <span class="text-gray-700 font-weight-bold small">
                        {{ $active->organization->organization_name ?? 'Pilih Organisasi' }}
                    </span>
                    <br>
                    <span class="text-gray-500 small">
                        {{ $active->division->division_name ?? '-' }} • 
                        {{ $active->role->role_name ?? '-' }}
                    </span>
                </div>
                
                <!-- Icon Dropdown -->
                <i class="fas fa-chevron-down text-gray-400 ml-3" 
                style="font-size: 0.85rem; transition: transform 0.3s;"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 py-2" 
                style="min-width: 280px; border-radius: 12px;">
                
                <h6 class="dropdown-header text-muted px-3 py-2">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Change Organization
                </h6>

                @foreach($userAccesses as $access)
                    <a class="dropdown-item switch-context d-flex align-items-center py-2 px-3 {{ $access->id == $activeAccessId ? 'active bg-light' : '' }}"
                    href="#"
                    data-id="{{ $access->id }}">
                        
                        <div class="flex-grow-1">
                            <div class="font-weight-medium">
                                {{ $access->organization->organization_name ?? '-' }}
                            </div>
                            <small class="text-muted">
                                {{ $access->division->division_name ?? '-' }} • 
                                {{ $access->role->role_name ?? '-' }}
                            </small>
                        </div>
                        
                        @if($access->id == $activeAccessId)
                            <i class="fas fa-check text-success ml-3"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </li>
        @endif

        {{-- 👤 USER --}}
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">

                    {{-- 👑 SUPERADMIN LABEL --}}
                    @if($isSuperadmin)
                        <span class="badge badge-danger mr-1">Superadmin</span>
                    @endif

                    {{ $user->name }}
                </span>

                <img class="img-profile rounded-circle" src="{{ asset('img/undraw_profile.svg') }}">
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow">
                <a class="dropdown-item" href="#">Change Password</a>
                
                <div class="dropdown-divider"></div>

                <form method="POST" action="/logout">
                    @csrf
                    <button class="dropdown-item">Logout</button>
                </form>
            </div>
        </li>

    </ul>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.switch-context');

    if (items.length > 0) {
        items.forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();

                fetch('/switch-context', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        access_id: this.dataset.id
                    })
                })
                .then(() => location.reload());
            });
        });
    }
});

item.addEventListener('click', function (e) {
    e.preventDefault();
    
    // Optional: loading feedback
    this.style.opacity = '0.6';
    this.style.pointerEvents = 'none';
    
    fetch('/switch-context', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ access_id: this.dataset.id })
    })
    .then(() => location.reload())
    .catch(() => location.reload());
});
</script>

<style>
.dropdown-menu {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}

.dropdown-item {
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8fafc;
    transform: translateX(4px);
}

.dropdown-item.active {
    background-color: #f1f5f9;
    font-weight: 500;
    color: #1e40af;
}
.dropdown-toggle[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}

.dropdown-item:hover {
    background-color: #f1f5f9;
}
</style>