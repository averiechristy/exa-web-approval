@extends('layouts.app')

@section('title', 'Division')

@section('content')

<div class="container-fluid">
    @include('components.alert')
    <h1 class="h4 mb-4 text-gray-800">Division</h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

                <!-- LEFT: SEARCH -->
                <div>
                    <h6 class="font-weight-bold text-primary mb-2 mr-3">
                        Division List
                    </h6>
                    <input 
                        type="text" 
                        id="searchInput"
                        name="search"
                        class="form-control form-control"
                        placeholder="Search division"
                        value="{{ request('search') }}"
                        style="width: 250px;"
                    >
                </div>

                <!-- RIGHT: TITLE + BUTTON -->
                <div class="d-flex align-items-center">

                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                        <i class="fas fa-plus"></i> Add Division
                    </button>
                </div>

            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Division Name</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($division as $item)
                            <tr>
                                <td>{{ $item->division_name }}</td>
                                <td class="text-center">
                                    <button 
                                        class="btn btn-sm btn-light editBtn" 
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->division_name }}"
                                        data-toggle="modal" 
                                        data-target="#editModal"
                                    >
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>

                                    <button 
                                        class="btn btn-sm btn-light deleteBtn" 
                                        data-id="{{ $item->id }}"
                                        data-name="{{ $item->division_name }}"
                                        data-toggle="modal" 
                                        data-target="#deleteModal"
                                    >
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">
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
                    @if ($division->lastPage() > 1)
                       {{ $division->onEachSide(2)->links('pagination::bootstrap-4') }}
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


<!-- Modal Add-->
<div class="modal" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Division</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <form id="addForm" method="POST" action="{{ route('division.store') }}" novalidate>
                @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Division Name</label>
                            <input 
                                type="text" 
                                name="division_name"
                                id="divisionName"
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
                <h5 class="modal-title">Edit Division</h5>
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
                        <label>Division Name</label>
                        <input 
                            type="text" 
                            name="division_name"
                            id="editName"
                            class="form-control"
                            oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                        >
                        <small class="text-danger" id="edit-error-name"></small>
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

        let name = $('#divisionName');
        let error = $('#error-name');

        if (name.val().trim() === '') {
            error.text('Division Name is required');
            name.addClass('is-invalid');
            return;
        }

        let value = name.val().trim();

        if (value.length < 1) {
            error.text('Division Name is required');
            name.addClass('is-invalid');
            return;
        }

        if (value.length > 24) {
            error.text('Max 24 characters allowed');
            name.addClass('is-invalid');
            return;
        }

        error.text('');
        name.removeClass('is-invalid');

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
        let name = $('#divisionName');
        let error = $('#error-name');

        name.val('');
        error.text('');
        name.removeClass('is-invalid');
    }

    let originalName = '';

    $('.editBtn').on('click', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');

        originalName = name;

        $('#editId').val(id);
        $('#editName').val(name);

        $('#editForm').attr('action', '/division/' + id);

        $('#edit-error-name').text('');
        $('#editName').removeClass('is-invalid');
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        let name = $('#editName');
        let error = $('#edit-error-name');

        let currentValue = name.val().trim();

        if (currentValue.length < 1) {
            error.text('Division Name is required');
            name.addClass('is-invalid');
            return;
        }

        if (currentValue.length > 24) {
            error.text('Max 24 characters allowed');
            name.addClass('is-invalid');
            return;
        }

        // VALIDASI KOSONG
        if (currentValue === '') {
            error.text('Division Name is required');
            name.addClass('is-invalid');
            return;
        }

        // VALIDASI TIDAK ADA PERUBAHAN
        if (currentValue === originalName) {
            Swal.fire({
                icon: 'info',
                title: 'No changes detected',
                text: 'Please modify the data before saving'
            });
            return;
        }

        error.text('');
        name.removeClass('is-invalid');

        // ALERT CONFIRM
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
                $('#editForm')[0].submit(); // penting pake ini
            }
        });
    });

    function clearEditForm() {
        let name = document.getElementById('editName');
        let error = document.getElementById('edit-error-name');

        name.value = originalName;

        error.innerText = '';
        name.classList.remove('is-invalid');
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
                form.attr('action', '/division/' + id);
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