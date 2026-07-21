<div class="modal-body">
    <!-- Info Singkat Aktivitas -->
    <div class="row mb-3">
        <div class="col-md-6">
            <small class="text-gray-600 font-weight-bold d-block">Event Action</small>
            <code class="text-primary font-weight-bold">{{ $activity->event }}</code>
        </div>
        <div class="col-md-6">
            <small class="text-gray-600 font-weight-bold d-block">Description</small>
            <span class="text-dark">{{ $activity->description }}</span>
        </div>
    </div>

    <hr>

    <!-- Tampilan Log Data Perubahan -->
    <div class="row">
        <!-- Kolom Kiri: Snapshot Asli / Atribut Utama -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold text-gray-800 small text-uppercase">Document Snapshot</h6>
            @if(!empty($attributes))
                <div class="bg-light p-3 rounded border" style="max-height: 250px; overflow-y: auto;">
                    <pre class="mb-0"><code class="text-dark small">{{ json_encode($attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </div>
            @else
                <p class="text-muted small italic">No baseline attributes captured.</p>
            @endif
        </div>

        <!-- Kolom Kanan: Perubahan (Changes) -->
        <div class="col-md-6 mb-3">
            <h6 class="font-weight-bold text-success small text-uppercase">Activity Changes</h6>
            @if(!empty($changes))
                <div class="bg-light p-3 rounded border" style="max-height: 250px; overflow-y: auto;">
                    <pre class="mb-0"><code class="text-success small">{{ json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                </div>
            @else
                <p class="text-muted small italic">No metadata changes tracked.</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
</div>