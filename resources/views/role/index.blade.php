@extends('layouts.app')

@section('title', 'Role')

@section('content')

<div class="container-fluid">
    @include('components.alert')
    <h1 class="h4 mb-4 text-gray-800">Role</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

                <!-- LEFT: SEARCH -->
                <div>
                    <h6 class="font-weight-bold text-primary mb-2 mr-3">
                        Role List
                    </h6>
                    <input 
                        type="text" 
                        id="searchInput"
                        name="search"
                        class="form-control form-control"
                        placeholder="Search role"
                        value="{{ request('search') }}"
                        style="width: 250px;"
                    >
                </div>

                <!-- RIGHT: TITLE + BUTTON -->
                <div class="d-flex align-items-center">

                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                        <i class="fas fa-plus"></i> Add Role
                    </button>
                </div>

            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Role Name</th>
                            <th>Role Level</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($role as $item)
                            <tr>
                                <td>{{ $item->role_name }}</td>
                                <td>{{ $item->role_level }}</td>
                                <td class="text-center">
                                    <button 
                                        class="btn btn-sm btn-light editBtn" 
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->role_name }}"
                                        data-order="{{ $item->role_level }}"
                                        data-toggle="modal" 
                                        data-target="#editModal"
                                    >
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    <button 
                                        class="btn btn-sm btn-light deleteBtn" 
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->role_name }}"
                                        data-toggle="modal" 
                                        data-target="#deleteModal"
                                    >
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">

                <!-- SHOW ENTRIES -->
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-muted">Show</span>

                    <form method="GET" id="perPageForm" class="mb-0">
                        <select 
                            name="perPage" 
                            onchange="this.form.submit()" 
                            class="custom-select custom-select-sm rounded-pill px-3"
                            style="width: 80px;"
                        >
                            <option value="10" @selected(request('perPage') == 10)>10</option>
                            <option value="25" @selected(request('perPage') == 25)>25</option>
                            <option value="50" @selected(request('perPage') == 50)>50</option>
                        </select>
                    </form>

                    <span class="ml-2 text-muted">entries</span>
                </div>

                <!-- PAGINATION -->
                <div>
                    @if ($role->lastPage() > 1)
                       {{ $role->onEachSide(2)->links('pagination::bootstrap-4') }}
                    @else
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <span class="page-link">«</span>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">1</span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">»</span>
                            </li>
                        </ul>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Add-->
<div class="modal" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <form id="addForm" method="POST" action="{{ route('role.store') }}" novalidate>
                @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Role Name</label>
                            <input 
                                type="text" 
                                name="role_name"
                                id="roleName"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                            >
                            <small class="text-danger" id="error-name">
                                @error('name')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                        <div class="form-group">
                            <label>Role Level</label>
                            <input 
                                type="text" 
                                name="role_level"
                                id="order"
                                class="form-control @error('order') is-invalid @enderror"
                                value="{{ old('order') }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            >
                            <small class="text-danger" id="error-order">
                                @error('order')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <input type="hidden" id="editId">

                    <div class="form-group">
                        <label>Role Name</label>
                        <input 
                            type="text" 
                            name="role_name"
                            id="editName"
                            class="form-control"
                            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                        >
                        <small class="text-danger" id="edit-error-name"></small>
                    </div>
                    <div class="form-group">
                        <label>Role Level</label>
                        <input 
                            type="number" 
                            name="role_level"
                            id="editOrder"
                            class="form-control"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        >
                        <small class="text-danger" id="edit-error-order"></small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="clearEditForm()">Clear</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form Hidden-->
 <form id="globalDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#addForm').on('submit', function(e) {
        e.preventDefault(); 

        let name = $('#roleName');
        let error = $('#error-name');
        let order = $('#order');
        let errorOrder = $('#error-order');

        let isValid = true;

        error.text('');
        errorOrder.text('');
        name.removeClass('is-invalid');
        order.removeClass('is-invalid');

        if (name.val().trim() === '') {
            error.text('Role Name is required');
            name.addClass('is-invalid');
            isValid = false;
        }

        if (order.val().trim() === '') {
            errorOrder.text('Role level is required');
            order.addClass('is-invalid');
            isValid = false;
        }

        let nameVal = name.val().trim();
        let orderVal = order.val().trim();

        if (nameVal.length < 1) {
            error.text('Role Name is required');
            name.addClass('is-invalid');
            isValid = false;
        } else if (nameVal.length > 24) {
            error.text('Max 24 characters allowed');
            name.addClass('is-invalid');
            isValid = false;
        }

        if (orderVal.length < 1) {
            errorOrder.text('Role level is required');
            order.addClass('is-invalid');
            isValid = false;
        } else if (orderVal.length > 3) {
            errorOrder.text('Max 3 digits allowed');
            order.addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) return;

        Swal.fire({
            title: 'Are you sure add this data?',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#addForm')[0].submit(); 
            }
        });
    });

    function clearForm() {
        let name = $('#roleName');
        let error = $('#error-name');
        let order = $('#order');
        let errorOrder = $('#error-order');

        name.val('');
        error.text('');
        name.removeClass('is-invalid');
        order.val('');
        errorOrder.text('');
        order.removeClass('is-invalid');
    }

    let originalName = '';
    let originalOrder = '';

    $('.editBtn').on('click', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let order = $(this).data('order');

        originalName = name;
        originalOrder = order;

        $('#editId').val(id);
        $('#editName').val(name);
        $('#editOrder').val(order);

        $('#editForm').attr('action', '/role/' + id);

        $('#edit-error-name').text('');
        $('#edit-error-order').text('');
        $('#editName').removeClass('is-invalid');
        $('#editOrder').removeClass('is-invalid');
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        let name = $('#editName');
        let order = $('#editOrder');

        let errorName = $('#edit-error-name');
        let errorOrder = $('#edit-error-order');

        let currentName = name.val().trim();
        let currentOrder = order.val().trim();

        let isValid = true;

        errorName.text('');
        errorOrder.text('');
        name.removeClass('is-invalid');
        order.removeClass('is-invalid');

        if (currentName === '') {
            errorName.text('Role Name is required');
            name.addClass('is-invalid');
            isValid = false;
        }

        if (currentOrder === '') {
            errorOrder.text('Role level is required');
            order.addClass('is-invalid');
            isValid = false;
        }

        if (currentName.length < 1) {
            errorName.text('Role Name is required');
            name.addClass('is-invalid');
            isValid = false;
        } else if (currentName.length > 24) {
            errorName.text('Max 24 characters allowed');
            name.addClass('is-invalid');
            isValid = false;
        }

        if (currentOrder.length < 1) {
            errorOrder.text('Role level is required');
            order.addClass('is-invalid');
            isValid = false;
        } else if (currentOrder.length > 3) {
            errorOrder.text('Max 3 digits allowed');
            order.addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) return;

        if (currentName === originalName && currentOrder == originalOrder) {
            Swal.fire({
                icon: 'info',
                title: 'No changes detected',
                text: 'Please modify the data before saving'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure update this data?',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#editForm')[0].submit();
            }
        });
    });

    function clearEditForm() {
        let name = $('#editName');
        let order = $('#editOrder');

        let errorName = $('#edit-error-name');
        let errorOrder = $('#edit-error-order');

        name.val(originalName);
        order.val(originalOrder);

        errorName.text('');
        errorOrder.text('');

        name.removeClass('is-invalid');
        order.removeClass('is-invalid');
    }

    $('.deleteBtn').on('click', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        Swal.fire({
            title: 'Are you sure delete this data?',
            text: `Delete ${name}? This cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {

                let form = $('#globalDeleteForm');
                form.attr('action', '/role/' + id);
                form.submit();

            }
        });
    });

    let searchTimer;

    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);

        let value = $(this).val();

        searchTimer = setTimeout(function () {
            let url = new URL(window.location.href);

            if (value) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }

            // biar perPage tetap kebawa
            let perPage = $('select[name="perPage"]').val();
            if (perPage) {
                url.searchParams.set('perPage', perPage);
            }

            window.location.href = url.toString();
        }, 400); // delay biar smooth
    });

    
</script>
@endsection