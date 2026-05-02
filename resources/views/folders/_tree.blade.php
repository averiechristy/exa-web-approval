<ul class="tree">
    @foreach($folders as $folder)
        <li>
            <div class="tree-item">

                <div class="left">
                    @if($folder->children->count())
                        <span class="toggle" onclick="toggleNode(this)">▶</span>
                    @else
                        <span style="width:14px;"></span>
                    @endif

                    <span class="icon">📁</span>
                    <span class="name">{{ $folder->folder_name }}</span>
                    <small class="org">
                        ({{ $folder->organization->organization_name ?? '-' }})
                    </small>
                </div>

                <div class="right">
    <button class="btn btn-warning btn-sm"
        data-toggle="modal"
        data-target="#editModal{{ $folder->id }}">
        Edit
    </button>

    <button 
        class="btn btn-danger btn-sm deleteBtn"
        data-id="{{ $folder->id }}"
        data-name="{{ $folder->folder_name }}"
    >
        Delete
    </button>
</div>

            </div>

                <div class="modal fade" id="editModal{{ $folder->id }}">
        <div class="modal-dialog">
            <form class="editForm" method="POST" action="{{ route('folders.update', $folder->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Edit Folder</h5>
                    </div>

                    <div class="modal-body">

                        <!-- ORGANIZATION -->
                        <div class="mb-2">
                            <label>Organization</label>
                            <select name="organization_id" class="form-control edit-org">
                                <option value="">-- Select --</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}"
                                        @selected($folder->organization_id == $org->id)>
                                        {{ $org->organization_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-danger error-org"></small>
                        </div>

                        <!-- PARENT -->
                        <div class="mb-2">
                            <label>Parent Folder</label>
                            <select 
                                name="parent_id" 
                                class="form-control edit-parent"
                                data-selected="{{ $folder->parent_id }}"
                            >
                                <option value="">-- Root --</option>
                            </select>
                            <small class="text-danger error-parent"></small>
                        </div>

                        <!-- NAME -->
                        <div class="mb-2">
                            <label>Folder Name</label>
                            <input 
                                type="text" 
                                name="folder_name"
                                class="form-control edit-name"
                                value="{{ $folder->folder_name }}"
                                oninput="this.value = this.value.replace(/[^A-Za-z0-9\s]/g, '')"
                            >
                            <small class="text-danger error-name"></small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary clearEdit">Clear</button>
                        <button class="btn btn-primary">Save</button>
                    </div>

                </div>
            </form>
        </div>
    </div>

            @if($folder->children->count())
                <ul class="tree-children">
                    @include('folders._tree', ['folders' => $folder->children])
                </ul>
            @endif
        </li>
    @endforeach
</ul>



<style>
.tree {
    list-style: none;
    padding-left: 20px;
}

.tree li {
    position: relative;
    margin: 8px 0;
}

/* garis vertikal */
.tree li::before {
    content: '';
    position: absolute;
    top: 0;
    left: -12px;
    border-left: 1px solid #ccc;
    height: 100%;
}

/* garis horizontal */
.tree li::after {
    content: '';
    position: absolute;
    top: 18px;
    left: -12px;
    width: 12px;
    border-top: 1px solid #ccc;
}

/* item box */
.tree-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 8px 12px;
}

/* kiri */
.tree-item .left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.tree-item .name {
    font-weight: 500;
}

.tree-item .org {
    color: #858796;
}

/* hover */
.tree-item:hover {
    background: #eef1f7;
}

.tree-children {
    display: none;
    margin-left: 10px;
}

.toggle {
    cursor: pointer;
    font-size: 12px;
    width: 14px;
    display: inline-block;
}
</style>