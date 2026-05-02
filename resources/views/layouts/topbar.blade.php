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
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                    <span class="text-gray-600 small">
                        {{ $active->organization->organization_name ?? '-' }}
                        -
                        {{ $active->division->division_name ?? '-' }}
                        -
                        {{ $active->role->role_name ?? '-' }}
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-right shadow">
                    @foreach($userAccesses as $access)
                        <a class="dropdown-item switch-context"
                        href="#"
                        data-id="{{ $access->id }}">
                            {{ $access->organization->organization_name ?? '-' }}
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

                    {{ $user->username }}
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
</script>