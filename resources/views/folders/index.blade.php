@extends('layouts.app')

@section('title', 'Folder')

@section('content')

<div class="container-fluid">
    @include('components.alert')

    <h1 class="h4 mb-4 text-gray-800">Folder</h1>

    <div class="card shadow mb-4">
        <div class="card-body">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="font-weight-bold text-primary">Folder Tree</h6>

                <button class="btn btn-primary btn-sm"
                    data-toggle="modal"
                    data-target="#addModal">
                    + Add Folder
                </button>
            </div>

            <!-- TREE -->
            @include('folders._tree', ['folders' => $rootFolders])

        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <form id="addForm" method="POST" action="{{ route('folders.store') }}" novalidate>
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add Folder</h5>
                </div>

                <div class="modal-body">

                    <!-- ORGANIZATION -->
                    <div class="mb-2">
                        <label>Organization</label>
                        <select name="organization_id" id="orgSelect" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">
                                    {{ $org->organization_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="error-org"></small>
                    </div>

                    <!-- PARENT -->
                    <div class="mb-2">
                        <label>Parent Folder</label>
                        <select name="parent_id" id="parentSelect" class="form-control">
                            <option value="">-- Root --</option>
                        </select>
                    </div>

                    <!-- NAME -->
                    <div class="mb-2">
                        <label>Folder Name</label>
                        <input 
                            type="text" 
                            name="folder_name" 
                            id="folderName"
                            class="form-control"
                            oninput="this.value = this.value.replace(/[^A-Za-z0-9\s]/g, '')"
                        >
                        <small class="text-danger" id="error-name"></small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="clearForm()">Clear</button>
                    <button class="btn btn-primary">Save</button>
                </div>

            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    const folders = @json($folders);

function buildOptions(folders, parentId = null, path = '') {
    let result = '';

    folders
        .filter(f => f.parent_id == parentId)
        .forEach(f => {

            let currentPath = path 
                ? path + ' / ' + f.folder_name 
                : f.folder_name;

            result += `<option value="${f.id}">
                ${currentPath}
            </option>`;

            result += buildOptions(folders, f.id, currentPath);
        });

    return result;
}

    document.getElementById('orgSelect').addEventListener('change', function() {
        let orgId = this.value;
        let parentSelect = document.getElementById('parentSelect');

        parentSelect.innerHTML = '<option value="">-- Root --</option>';

        let filtered = folders.filter(f => f.organization_id == orgId);

        parentSelect.innerHTML += buildOptions(filtered);
    });


    function toggleNode(el) {
    let parentLi = el.closest('li');
    let child = parentLi.querySelector('.tree-children');

    if (!child) return;

    if (child.style.display === 'none') {
        child.style.display = 'block';
        el.innerText = '▼';
    } else {
        child.style.display = 'none';
        el.innerText = '▶';
    }
}

$('#addForm').on('submit', function(e) {
    e.preventDefault();

    let org = $('#orgSelect');
    let name = $('#folderName');

    let errOrg = $('#error-org');
    let errName = $('#error-name');

    let valid = true;

    // RESET ERROR
    errOrg.text('');
    errName.text('');
    org.removeClass('is-invalid');
    name.removeClass('is-invalid');

    // VALIDASI ORGANIZATION
    if (!org.val()) {
        errOrg.text('Organization is required');
        org.addClass('is-invalid');
        valid = false;
    }

    // VALIDASI NAME
    let value = name.val().trim();

    if (value === '') {
        errName.text('Folder name is required');
        name.addClass('is-invalid');
        valid = false;
    } else if (!/^[A-Za-z0-9\s]+$/.test(value)) {
        errName.text('Only letters and numbers are allowed');
        name.addClass('is-invalid');
        valid = false;
    } else if (value.length > 50) {
        errName.text('Max 50 characters allowed');
        name.addClass('is-invalid');
        valid = false;
    }

    if (!valid) return;

    // SWAL CONFIRM
    Swal.fire({
        title: 'Are you sure add this folder?',
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
    $('#orgSelect').val('');
    $('#parentSelect').html('<option value="">-- Root --</option>');
    $('#folderName').val('');

    $('#error-org').text('');
    $('#error-name').text('');

    $('#orgSelect').removeClass('is-invalid');
    $('#folderName').removeClass('is-invalid');
}

$(document).on('click', '.deleteBtn', function() {
    let id = $(this).data('id');
    let name = $(this).data('name');

    Swal.fire({
        title: 'Are you sure delete this folder?',
        text: `Delete "${name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {

            let form = $('#deleteForm');
            form.attr('action', `/folders/${id}`); // pastiin route sesuai
            form.submit();

        }
    });
});
$(document).on('submit', '.editForm', function(e) {
    e.preventDefault();

    let form = $(this);

    let org = form.find('.edit-org');
    let name = form.find('.edit-name');
    let parent = form.find('.edit-parent');

    let errOrg = form.find('.error-org');
    let errName = form.find('.error-name');
    let errParent = form.find('.error-parent');

    let valid = true;

    // RESET
    errOrg.text('');
    errName.text('');
    errParent.text('');

    org.removeClass('is-invalid');
    name.removeClass('is-invalid');
    parent.removeClass('is-invalid');

    // ✅ VALIDASI ORGANIZATION
    if (!org.val()) {
        errOrg.text('Organization is required');
        org.addClass('is-invalid');
        valid = false;
    }

    // ✅ VALIDASI NAME
    let value = name.val().trim();

    if (value === '') {
        errName.text('Folder name is required');
        name.addClass('is-invalid');
        valid = false;
    } else if (!/^[A-Za-z0-9\s]+$/.test(value)) {
        errName.text('Only letters and numbers are allowed');
        name.addClass('is-invalid');
        valid = false;
    } else if (value.length > 50) {
        errName.text('Max 50 characters allowed');
        name.addClass('is-invalid');
        valid = false;
    }

    // ✅ VALIDASI PARENT (optional tapi bagus)
    let currentId = form.closest('.modal').attr('id').replace('editModal', '');

    if (parent.val() == currentId) {
        errParent.text('Cannot set itself as parent');
        parent.addClass('is-invalid');
        valid = false;
    }

    if (!valid) return;

    // ✅ SWAL CONFIRM
    Swal.fire({
        title: 'Are you sure update this folder?',
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        confirmButtonColor: '#4e73df',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            form[0].submit();
        }
    });
});

function buildParentOptions(folders, parentId = null, path = '', excludeId = null) {
    let result = '';

    folders
        .filter(f => f.parent_id == parentId)
        .forEach(f => {

            // ❌ jangan boleh pilih diri sendiri
            if (f.id == excludeId) return;

            let currentPath = path 
                ? path + ' / ' + f.folder_name 
                : f.folder_name;

            result += `<option value="${f.id}">
                ${currentPath}
            </option>`;

            result += buildParentOptions(folders, f.id, currentPath, excludeId);
        });

    return result;
}

$(document).on('show.bs.modal', '[id^=editModal]', function () {

    let modal = $(this);

    let form = modal.find('.editForm');
    let orgId = form.find('.edit-org').val();
    let parentSelect = form.find('.edit-parent');

    let currentId = modal.attr('id').replace('editModal', '');

    // ambil parent lama
    let selectedParent = parentSelect.data('selected'); 

    parentSelect.html('<option value="">-- Root --</option>');

    let filtered = folders.filter(f => f.organization_id == orgId);

    parentSelect.append(
        buildParentOptions(filtered, null, '', currentId)
    );

    parentSelect.val(selectedParent);

});
</script>
@endpush