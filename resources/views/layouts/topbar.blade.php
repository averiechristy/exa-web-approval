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
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">
                        <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                        Change Password
                    </a>
                
                <div class="dropdown-divider"></div>

                <form method="POST" action="/logout">
                    @csrf
                    <button class="dropdown-item">Logout</button>
                </form>
            </div>
        </li>

    </ul>
</nav>

{{-- 🔔 ALERT SUCCESS / ERROR --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-4 mt-2" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->has('current_password') || $errors->has('new_password'))
    <div class="alert alert-danger alert-dismissible fade show mx-4 mt-2" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i> Password change failed. Please check the form again.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

{{-- 🔑 MODAL CHANGE PASSWORD --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="changePasswordModalLabel">
                    <i class="fas fa-lock mr-2"></i>Change Password
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    {{-- Current Password --}}
                    <div class="form-group mb-3">
                        <label for="current_password" class="font-weight-medium text-dark small">Current Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                   name="current_password" 
                                   id="current_password" 
                                   class="form-control pr-5 @error('current_password') is-invalid @enderror" 
                                   placeholder="Enter current password"
                                   required>
                            <span class="toggle-password-btn" data-target="current_password"
                                  style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6c757d; z-index:10;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        @error('current_password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="form-group mb-3">
                        <label for="new_password" class="font-weight-medium text-dark small">New Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password" 
                                   class="form-control pr-5 @error('new_password') is-invalid @enderror" 
                                   placeholder="Minimum 8 characters"
                                   required>
                            <span class="toggle-password-btn" data-target="new_password"
                                  style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6c757d; z-index:10;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        @error('new_password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="form-group mb-3">
                        <label for="new_password_confirmation" class="font-weight-medium text-dark small">Confirm New Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                   name="new_password_confirmation" 
                                   id="new_password_confirmation" 
                                   class="form-control pr-5" 
                                   placeholder="Re-enter new password"
                                   required>
                            <span class="toggle-password-btn" data-target="new_password_confirmation"
                                  style="position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6c757d; z-index:10;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggleButtons = document.querySelectorAll('.toggle-password-btn');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const inputField = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (inputField.type === 'password') {
                inputField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                inputField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

// Buka modal secara otomatis jika terdapat error validasi password
@if ($errors->has('current_password') || $errors->has('new_password'))
    $('#changePasswordModal').modal('show');
@endif
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