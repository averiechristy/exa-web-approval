{{-- resources/views/components/folder-tree.blade.php --}}
<li class="list-group-item p-1 border-0">

<div class="d-flex align-items-center folder-item 
            {{ request()->routeIs('documents.folder') && request()->segment(3) == $folder->id ? 'active' : '' }}" 
     data-folder-id="{{ $folder->id }}">

    <!-- Toggle -->
    @if($folder->children->isNotEmpty())
        <a href="#" class="folder-toggle me-1 text-muted" data-toggle="collapse" data-target="#folder{{ $folder->id }}" style="width: 18px;">
            <i class="fas fa-chevron-right fa-xs transition"></i>
        </a>
    @else
        <span class="me-1" style="width: 18px;"></span>
    @endif

    <!-- Folder Name -->
    <a href="{{ route('documents.folder', $folder->id) }}" 
       class="flex-grow-1 text-decoration-none py-1 d-flex align-items-center folder-link">
        <i class="fas fa-folder text-warning mr-2"></i> 
        <span class="text-truncate">{{ $folder->folder_name }}</span>
    </a>
</div>

    <!-- Children -->
    @if($folder->children->isNotEmpty())
        <ul class="collapse list-unstyled ml-3 mt-1" id="folder{{ $folder->id }}">
            @foreach($folder->children as $child)
                @include('components.folder-tree', ['folder' => $child])
            @endforeach
        </ul>
    @endif

</li>