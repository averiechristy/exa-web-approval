@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Upload Document</h1>

    <!-- STEPPER -->
    <div class="stepper">
        <div class="step active" data-step="0">
            <div class="circle">1</div>
            <span>Upload</span>
        </div>
        <div class="step" data-step="1">
            <div class="circle">2</div>
            <span>Add Approver</span>
        </div>
        <div class="step" data-step="2">
            <div class="circle">3</div>
            <span>Place E-Approval</span>
        </div>
        <div class="step" data-step="3">
            <div class="circle">4</div>
            <span>Review & Send</span>
        </div>
    </div>

{{-- STEP 1 --}}
<div class="card shadow mb-4 step-content active">
    <div class="card-body">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h5 class="mb-1 text-primary">Upload Dokumen</h5>
                <p class="text-muted small mb-0">Unggah dokumen PDF yang akan diproses</p>
            </div>
            <div class="text-end">
                <span class="badge badge-light px-3 py-2">
                    <i class="fas fa-file-pdf text-danger"></i> PDF Only
                </span>
            </div>
        </div>

        <!-- Upload Box -->
        <div id="uploadArea" class="upload-box-modern mb-4">
            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
            <h6 class="mb-1">Drag & Drop PDF di sini</h6>
            <p class="text-muted small mb-3">atau</p>
            <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('pdfInput').click()">
                <i class="fas fa-folder-open"></i> Pilih File
            </button>
            <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                Maksimal 50 MB • Hanya .pdf
            </p>
        </div>

        <input type="file" id="pdfInput" accept="application/pdf" multiple hidden>

        <!-- File List -->
        <div id="fileList" class="file-list"></div>

        <hr class="my-4">

        <!-- Form Fields -->
        <div class="row">
            @if ($isSuperAdmin)
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Organisasi</label>
                    <select id="organizationSelect" class="form-control form-control-lg">
                        <option value="">-- Pilih Organisasi --</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Divisi Pemohon</label>
                    <select id="divisionSelect" class="form-control form-control-lg">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            @if(!$isSuperAdmin)
                <input type="hidden" id="organizationSelectHidden" value="{{ session('active_organization_id') }}">
                <input type="hidden" id="divisionSelectHidden" value="{{ session('active_division_id', '') }}">
            @endif

            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Tipe Dokumen</label>
                    <select id="documentTypeSelect" class="form-control form-control-lg">
                        <option value="">-- Pilih Tipe Dokumen --</option>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Folder Tujuan</label>
                    <select id="folderSelect" class="form-control form-control-lg">
                        @if(!$isSuperAdmin)
                            @foreach($folderOptions as $folder)
                                <option value="{{ $folder['id'] }}">{{ $folder['name'] }}</option>
                            @endforeach
                        @else
                            <option value="">-- Pilih Organisasi Terlebih Dahulu --</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        <div class="text-right mt-4">
            <button class="btn btn-primary nextBtn">Next</button>
        </div>
    </div>
</div>
    
    <!-- STEP 2 -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">
            <div class="alert alert-info small">
                • Approver di Tier yang sama akan diproses secara paralel<br>
                • Centang "Show on document" jika ingin tanda tangan muncul di PDF
            </div>

            <h6 class="font-weight-bold text-primary mb-3">Daftar Approver</h6>
            
            <div id="tierContainer">
                <!-- Akan diisi oleh JavaScript -->
            </div>

            <!-- ================= CC ================= -->
                <h6 class="font-weight-bold text-primary mb-3">Penerima Salinan</h6>

                <div id="ccContainer">

                    <div class="form-row align-items-end cc-row mb-3">
                        <div class="col-md-10">
                            <label>Nama User</label>
                            <!-- Di STEP 2 - CC Container -->
                            <select class="form-control ccDropdown" disabled>
                                <option value="">Loading users...</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger removeCC w-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addCC">
                    <i class="fas fa-plus"></i> Add CC
                </button>

            <div class="text-right mt-4">
                <button class="btn btn-secondary prevBtn">Back</button>
                <button class="btn btn-primary nextBtn">Next</button>
            </div>
        </div>
    </div>

    <!-- STEP 3 -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">

            <!-- ================= PLACEMENT TYPE ================= -->
            <div class="mb-4">

                <h6 class="font-weight-bold text-primary mb-3">
                    Signature Placement Type
                </h6>

                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">

                    <label class="btn btn-outline-primary active">
                        <input type="radio" name="placementType" value="custom" checked>
                        Custom
                        <small class="d-block">Drag & drop manually</small>
                    </label>

                    <label class="btn btn-outline-primary">
                        <input type="radio" name="placementType" value="standard">
                        Standard
                        <small class="d-block">Auto place bottom right every page</small>
                    </label>

                    <label class="btn btn-outline-primary">
                        <input type="radio" name="placementType" value="fixed">
                        Fixed
                        <small class="d-block">Add approval summary page</small>
                    </label>

                </div>

            </div>

            <div class="row">

                <!-- SIGNER PANEL -->
                <div class="col-md-3">
                    <h6 class="font-weight-bold text-primary">Approvers</h6>

                    <div class="list-group mb-3">
                        <div class="list-group-item signer-item" draggable="true" data-signer="Andi Pratama">
                            <i class="fas fa-user text-primary"></i> Andi Pratama
                        </div>
                        <div class="list-group-item signer-item" draggable="true" data-signer="Budi Santoso">
                            <i class="fas fa-user text-primary"></i> Budi Santoso
                        </div>
                        <div class="list-group-item signer-item" draggable="true" data-signer="Citra Lestari">
                            <i class="fas fa-user text-primary"></i> Citra Lestari
                        </div>
                    </div>

                </div>

                <!-- PDF AREA -->
                <div class="col-md-9">
                    <ul class="nav nav-tabs mb-3" id="fileTabs"></ul>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button class="btn btn-sm btn-outline-secondary" id="prevPage">
                            <i class="fas fa-chevron-left"></i> Prev
                        </button>

                        <small id="pageInfo" class="text-muted">Page 1</small>

                        <button class="btn btn-sm btn-outline-secondary" id="nextPage">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div id="pdfArea" class="pdf-area">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                </div>

            </div>

            <div class="text-right mt-3">
                <button class="btn btn-secondary prevBtn">Back</button>
                <button class="btn btn-primary nextBtn">Next</button>
            </div>

        </div>
    </div>

    <!-- STEP 4 -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">

            <div class="form-group">
                <label>Email Subject</label>
                <input class="form-control" value="Permintaan Tanda Tangan">
            </div>

            <div class="form-group">
                <label>Email Message</label>
                <textarea class="form-control" rows="4">
                    Mohon bantuannya untuk menandatangani dokumen berikut.
                    Terima kasih.
                </textarea>
            </div>

            <div class="text-right">
                <button class="btn btn-secondary prevBtn">Back</button>
                <button class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
    "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js";
    
    // ================= GLOBAL VARIABLES =================
    let currentStep = 0;
    let step1Data = {}; 
    const steps = document.querySelectorAll('.step');
    const contents = document.querySelectorAll('.step-content');
    
    let renderTask = null;
    let pdfRendered = false;
    
    // PDF Variables
    const pdfArea = document.getElementById('pdfArea');
    let draggedSigner = null;
    let usedSigners = new Set();
    let uploadedFiles = [];
    let activeFileIndex = 0;
    const pdfCanvas = document.getElementById('pdfCanvas');
    const ctx = pdfCanvas ? pdfCanvas.getContext('2d') : null;
    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 0;


    // ================= ADD & REMOVE CC =================
    $(document).ready(function () {

        // Tambah CC Row
        $('#addCC').on('click', function () {
            const newRow = `
                <div class="form-row align-items-end cc-row mb-3">
                    <div class="col-md-10">
                        <label>Nama User</label>
                        <select class="form-control ccDropdown">
                            <option value="">Pilih User</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger removeCC w-100">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;

            $('#ccContainer').append(newRow);

            // Load ulang options ke dropdown baru
            const lastDropdown = $('#ccContainer .ccDropdown').last();
            if (typeof window.ccUsers !== 'undefined' && window.ccUsers.length > 0) {
                updateSingleCcDropdown(lastDropdown, window.ccUsers);
            }
        });

        // Hapus CC Row
        $(document).on('click', '.removeCC', function () {
            if ($('#ccContainer .cc-row').length > 1) {
                $(this).closest('.cc-row').remove();
            } else {
                alert("Minimal harus ada 1 baris CC!");
            }
        });

    });
    

    // ================= STEP NAVIGATION =================
    function updateStep() {
        contents.forEach((c, i) => {
            c.classList.remove('active');
            steps[i].classList.remove('active', 'completed');

            if (i < currentStep) {
                steps[i].classList.add('completed');
            } else if (i === currentStep) {
                steps[i].classList.add('active');
            }
        });

        contents[currentStep].classList.add('active');

        if (currentStep === 2 && uploadedFiles.length > 0 && !pdfRendered) {
            renderPDF();
            pdfRendered = true;
        }
    }

    // Next Button Handler
    document.querySelectorAll('.nextBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep === 0) {
                // ... kode step 1 tetap sama
                if (uploadedFiles.length === 0) {
                    alert("Upload file dulu ya");
                    return;
                }

                let orgId, divId;
                if (@json($isSuperAdmin)) {
                    orgId = $('#organizationSelect').val();
                    divId = $('#divisionSelect').val();
                } else {
                    orgId = $('#organizationSelectHidden').val();
                    divId = $('#divisionSelectHidden').val();
                }

                let folder = $('#folderSelect').val();
                let docType = $('#documentTypeSelect').val();

                if (!orgId || !folder || !docType) {
                    alert("Lengkapi semua field dulu");
                    return;
                }

                const step1DataTemp = collectStep1Data();
                console.log("STEP 1 DATA:", step1DataTemp);
                loadWorkflowApprovers(orgId, docType, divId);
                loadCC(orgId, docType, divId);
            }

            if (currentStep === 1) {
                const step2DataTemp = collectStep2Data();
                
                // Gabungkan dengan step 1
                const combinedData = {
                    ...step1Data,
                    ...step2DataTemp
                };
                
                console.log("=== STEP 2 DATA ===");
                console.log("Step 2 Raw Data:", step2DataTemp);
                console.log("=== COMBINED STEP 1 + 2 DATA ===");
                console.log("Combined Data:", combinedData);
                
                // Validasi minimal 1 approver
                if (step2DataTemp.approvers.length === 0) {
                    alert("Pilih minimal 1 approver!");
                    return;
                }
            }

            if (currentStep === 2) {
                const signatureData = collectSignatureData();
                console.log("STEP 3 SIGNATURE DATA:", signatureData);
            }

            if (currentStep < contents.length - 1) {
                currentStep++;
                updateStep();
            }
        });
    });

    // ================= LOAD APPROVER DENGAN TIER =================
    function loadWorkflowApprovers(organizationId, workflowId, requesterDivisionId) {
        if (!workflowId) return;

        $.ajax({
            url: '/api/workflow-approvers/' + workflowId,
            type: 'GET',
            data: {
                organization_id: organizationId,
                division_id: requesterDivisionId
            },
            beforeSend: function() {
                $('#tierContainer').html('<p class="text-muted">Memuat daftar approver sesuai workflow...</p>');
            },
            success: function(response) {
                $('#tierContainer').empty();

                if (!response.workflow_steps || response.workflow_steps.length === 0) {
                    $('#tierContainer').html('<p class="text-warning">Tidak ada approver yang tersedia.</p>');
                    return;
                }

                response.workflow_steps.forEach(group => {
                    let tierHtml = `
                    <div class="tier-box border rounded p-3 mb-4" data-tier="${group.tier}">
                        <h6 class="text-primary mb-3">
                            ${group.title || 'Tier ' + group.tier} • ${group.division_name}
                            ${group.sla_days > 0 ? `<small class="text-muted">(${group.sla_days} hari SLA)</small>` : ''}
                        </h6>
                        
                        <div class="approvers-list" data-tier="${group.tier}" data-division-id="${group.division_id || ''}"></div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 add-approver-per-tier"
                                data-tier="${group.tier}" data-division-id="${group.division_id || ''}">
                            <i class="fas fa-plus"></i> Tambah Approver
                        </button>
                    </div>`;

                    $('#tierContainer').append(tierHtml);
                });

                // Render approver pertama di tiap tier
                response.workflow_steps.forEach(group => {
                    if (group.users && group.users.length > 0) {
                        addApproverRow(group.tier, group.users, group.division_id || '');
                    }
                });

                // Event listener untuk tombol Add Approver per tier
                $('.add-approver-per-tier').off('click').on('click', function() {
                    const tier = $(this).data('tier');
                    const divisionId = $(this).data('division-id') || '';
                    const users = response.workflow_steps.find(g => g.tier == tier)?.users || [];
                    addApproverRow(tier, users, divisionId);
                });
            },
            error: function() {
                $('#tierContainer').html('<p class="text-danger">Gagal memuat approver. Silakan coba lagi.</p>');
            }
        });
    }

    // Fungsi untuk menambah row approver di dalam tier
    function addApproverRow(tier, usersList, divisionId = '') {
        const container = $(`.approvers-list[data-tier="${tier}"]`);
        
        let options = '<option value="">-- Pilih Approver --</option>';
        usersList.forEach(user => {
            options += `<option value="${user.id}" data-division="${user.division_id || divisionId}">
                ${user.name} ${user.role_name ? `(${user.role_name})` : ''} 
                ${user.division_name ? `| ${user.division_name}` : ''}
            </option>`;
        });

        const rowHtml = `
            <div class="row align-items-center approver-row mb-3" data-division="${divisionId}">
                <div class="col-md-5">
                    <label class="small text-muted">Approver</label>
                    <select class="form-control approver-select" data-tier="${tier}" data-division="${divisionId}">
                        ${options}
                    </select>
                </div>
                
                <div class="col-md-4 mt-4">
                    <div class="form-check">
                        <input class="form-check-input show-on-doc" type="checkbox" checked>
                        <label class="form-check-label small">Show on document</label>
                    </div>
                </div>

                <div class="col-md-3 mt-4">
                    <button type="button" class="btn btn-danger btn-sm remove-approver w-100">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>`;

        container.append(rowHtml);

        // Event remove (minimal 1 approver per tier)
        container.find('.remove-approver').last().on('click', function() {
            if (container.find('.approver-row').length > 1) {
                $(this).closest('.approver-row').remove();
            } else {
                alert("Minimal harus ada 1 approver per tier!");
            }
        });
    }

    function loadCC(organizationId, documentTypeId, divisionId) {
        $.ajax({
            url: '/users/carboncopy',
            type: 'GET',
            data: {
                organization_id: organizationId,
                document_type_id: documentTypeId,
                division_id: divisionId
            },
            beforeSend: function() {
                $('.ccDropdown').html('<option>Loading users...</option>').prop('disabled', true);
            },
            success: function(response) {
                window.ccUsers = response.users || [];   // ← Simpan global
                updateCcDropdowns(response.users);
            },
            error: function(xhr) {
                console.error('Error loading users:', xhr);
                updateCcDropdowns([]);
                alert('Gagal memuat daftar user approver');
            }
        });
    }

    function updateCcDropdowns(users) {
        $('.ccDropdown').each(function () {
            updateSingleCcDropdown($(this), users);
        });
    }

    function updateSingleCcDropdown(dropdown, users) {
        let options = '<option value="">Pilih User</option>';
        
        users.forEach(function(user) {
            options += `
                <option value="${user.id}"
                        data-org="${user.organization_id}"
                        data-division="${user.division_id || ''}">
                    ${user.name} ${user.division_name ? `| ${user.division_name}` : ''}
                </option>
            `;
        });
        
        dropdown.html(options).prop('disabled', false);
    }

    // Previous Button Handler
    document.querySelectorAll('.prevBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                updateStep();
            }
        });
    });


    // ================= DRAG & DROP SIGNATURES =================
    document.querySelectorAll('.signer-item').forEach(item => {
        item.addEventListener('dragstart', function () {
            const name = this.dataset.signer;
            if (usedSigners.has(name)) {
                draggedSigner = null;
                return;
            }
            draggedSigner = name;
        });
    });

    pdfArea.addEventListener('dragover', function (e) {
        e.preventDefault();
    });

    pdfArea.addEventListener('drop', function (e) {
        e.preventDefault();
        if (!draggedSigner) return;

        const currentFile = uploadedFiles[activeFileIndex];
        if (currentFile.usedSigners.has(draggedSigner)) return;

        const canvasRect = pdfCanvas.getBoundingClientRect();
        const xPx = e.clientX - canvasRect.left;
        const yPx = e.clientY - canvasRect.top;
        const xPercent = xPx / canvasRect.width;
        const yPercent = yPx / canvasRect.height;

        const box = document.createElement('div');
        box.classList.add('signature-box');
        box.dataset.signer = draggedSigner;
        box.dataset.page = currentPage;
        box.dataset.x = xPercent;
        box.dataset.y = yPercent;

        box.innerHTML = `
            <div class="delete-signature">&times;</div>
            <span>${draggedSigner}</span><br>
            <small>Signature</small>
        `;

        positionSignatureBox(box);
        makeDraggable(box);
        pdfArea.appendChild(box);

        currentFile.usedSigners.add(draggedSigner);
        disableSignerUI(draggedSigner);

        currentFile.signatures.push({
            signer: draggedSigner,
            page: currentPage,
            x_percent: xPercent,
            y_percent: yPercent
        });

        draggedSigner = null;
    });

    // Signer UI Controls
    function disableSignerUI(name) {
        const currentFile = uploadedFiles[activeFileIndex];
        currentFile.usedSigners.add(name);
        document.querySelectorAll('.signer-item').forEach(item => {
            if (item.dataset.signer === name) {
                item.classList.add('disabled');
                item.style.opacity = 0.5;
                item.style.pointerEvents = 'none';
            }
        });
    }

    function enableSignerUI(name) {
        const currentFile = uploadedFiles[activeFileIndex];
        currentFile.usedSigners.delete(name);
        document.querySelectorAll('.signer-item').forEach(item => {
            if (item.dataset.signer === name) {
                item.classList.remove('disabled');
                item.style.opacity = 1;
                item.style.pointerEvents = 'auto';
            }
        });
    }

    // Delete Signature
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('delete-signature')) {
            const box = e.target.closest('.signature-box');
            const signerName = box.dataset.signer;
            box.remove();
            usedSigners.delete(signerName);
            enableSignerUI(signerName);
        }
    });

    // Draggable Signature Boxes
    function makeDraggable(element) {
        let isDragging = false;
        let offsetX = 0;
        let offsetY = 0;

        element.addEventListener('pointerdown', function (e) {
            if (e.target.classList.contains('delete-signature')) return;
            isDragging = true;
            offsetX = e.offsetX;
            offsetY = e.offsetY;
            element.setPointerCapture(e.pointerId);
        });

        element.addEventListener('pointermove', function (e) {
            if (!isDragging) return;

            const canvasRect = pdfCanvas.getBoundingClientRect();
            let x = e.clientX - canvasRect.left - offsetX;
            let y = e.clientY - canvasRect.top - offsetY;

            element.dataset.x = x / canvasRect.width;
            element.dataset.y = y / canvasRect.height;

            positionSignatureBox(element);
        });

        element.addEventListener('pointerup', function () {
            isDragging = false;
        });
    }

    function positionSignatureBox(box) {
        const canvasRect = pdfCanvas.getBoundingClientRect();
        const x = box.dataset.x * canvasRect.width;
        const y = box.dataset.y * canvasRect.height;
        box.style.left = x + 'px';
        box.style.top = y + 'px';
    }

    // ================= PDF UPLOAD & RENDER =================
    const pdfInput = document.getElementById('pdfInput');
    pdfInput.addEventListener('change', function (e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (file.type !== "application/pdf") return;

            const reader = new FileReader();
            reader.onload = function () {
                const pdfData = new Uint8Array(this.result);

                pdfjsLib.getDocument(pdfData).promise.then(pdf => {
                    uploadedFiles.push({
                        name: file.name,
                        pdfData: pdfData,
                        totalPages: pdf.numPages,
                        signatures: [],
                        usedSigners: new Set()
                    });

                    renderFileList();   
                    renderFileTabs();   
                    switchFile(activeFileIndex); 
                });
            };
            reader.readAsArrayBuffer(file);
        });
    });

    function renderPDF(pageNumber = 1) {
        if (!uploadedFiles[activeFileIndex]) return;

        const file = uploadedFiles[activeFileIndex];

        pdfjsLib.getDocument(file.pdfData).promise.then(pdf => {
            pdfDoc = pdf;
            totalPages = pdf.numPages;

            pdf.getPage(pageNumber).then(p => {
                const viewport = p.getViewport({ scale: 1 });
                const scale = Math.min(
                    pdfArea.clientWidth / viewport.width,
                    pdfArea.clientHeight / viewport.height
                );
                const scaled = p.getViewport({ scale });

                pdfCanvas.width = scaled.width;
                pdfCanvas.height = scaled.height;

                ctx.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height);

                if (renderTask) {
                    renderTask.cancel();
                }

                renderTask = p.render({
                    canvasContext: ctx,
                    viewport: scaled
                });

                renderTask.promise.then(() => {
                    $('#pageInfo').text(`Page ${currentPage} of ${totalPages}`);
                    renderSignaturesForPage(currentPage);
                }).catch(err => {
                    if (err.name !== 'RenderingCancelledException') {
                        console.error(err);
                    }
                });
            });
        });
    }

    function renderSignaturesForPage(page) {
        document.querySelectorAll('.signature-box').forEach(box => box.remove());

        const file = uploadedFiles[activeFileIndex];
        if (!file) return;

        file.signatures.forEach(sig => {
            if (sig.page === page) {
                const box = document.createElement('div');
                box.classList.add('signature-box');
                box.dataset.signer = sig.signer;
                box.dataset.page = sig.page;
                box.dataset.x = sig.x_percent;
                box.dataset.y = sig.y_percent;

                box.innerHTML = `
                    <div class="delete-signature">&times;</div>
                    <span>${sig.signer}</span><br>
                    <small>Signature</small>
                `;

                positionSignatureBox(box);
                makeDraggable(box);
                pdfArea.appendChild(box);
            }
        });
    }

    // Page Navigation
    document.getElementById('prevPage').addEventListener('click', function () {
        if (currentPage <= 1) return;
        currentPage--;
        renderPDF(currentPage);
    });

    document.getElementById('nextPage').addEventListener('click', function () {
        if (currentPage >= totalPages) return;
        currentPage++;
        renderPDF(currentPage);
    });

    // ================= DATA COLLECTION =================
    function collectSignatureData() {
        return uploadedFiles.map(file => {
            const fileData = {
                file_name: file.name,
                total_pages: file.totalPages,
                signatures: []
            };
            
            const type = document.querySelector('input[name="placementType"]:checked').value;

            if(type === 'custom') {
                fileData.signatures = file.signatures;
            } else if(type === 'standard') {
                const dummySigners = ['Andi Pratama', 'Budi Santoso', 'Citra Lestari'];
                for(let p=1; p<=file.totalPages; p++){
                    dummySigners.forEach(signer => {
                        fileData.signatures.push({
                            signer: signer,
                            page: p,
                            x_percent: 0.75,
                            y_percent: 0.85,
                            mode: 'standard'
                        });
                    });
                }
            } else if(type === 'fixed') {
                const dummySigners = ['Andi Pratama', 'Budi Santoso', 'Citra Lestari'];
                const lastPage = file.totalPages;
                dummySigners.forEach(signer => {
                    fileData.signatures.push({
                        signer: signer,
                        page: lastPage,
                        x_percent: 0.5,
                        y_percent: 0.5,
                        mode: 'fixed'
                    });
                });
            }

            return fileData;
        });
    }

    function collectStep1Data() {
        let orgId, divId;
        if (@json($isSuperAdmin)) {
            orgId = $('#organizationSelect').val();
            divId = $('#divisionSelect').val();
        } else {
            orgId = $('#organizationSelectHidden').val();
            divId = $('#divisionSelectHidden').val();
        }

        const data = {
            organization_id: orgId,
            division_id: divId,
            folder_id: $('#folderSelect').val(),
            document_type_id: $('#documentTypeSelect').val(),
            files: uploadedFiles.map(f => f.name),
            is_superadmin: @json($isSuperAdmin)
        };
        
        step1Data = data;
        return data;
    }

    // ================= COLLECT STEP 2 DATA =================
    function collectStep2Data() {
        const approvers = [];
        
        // Collect approvers dari setiap tier
        $('.tier-box').each(function() {
            const tier = $(this).data('tier');
            const tierApprovers = [];
            
            $(this).find('.approver-row').each(function() {
                const approverSelect = $(this).find('.approver-select');
                const showOnDoc = $(this).find('.show-on-doc');
                const rowDivisionId = $(this).data('division') || '';
                
                const approverId = approverSelect.val();
                const selectedOption = approverSelect.find('option:selected');
                const approverDivisionId = selectedOption.data('division') || rowDivisionId;
                
                const showOnDocument = showOnDoc.is(':checked');
                
                if (approverId) {
                    tierApprovers.push({
                        user_id: approverId,
                        division_id: approverDivisionId,  // 🔥 Division ID dari approver
                        show_on_document: showOnDocument
                    });
                }
            });
            
            if (tierApprovers.length > 0) {
                approvers.push({
                    tier: tier,
                    division_id: $('.approvers-list[data-tier="' + tier + '"]').data('division-id') || '', // 🔥 Tier division
                    approvers: tierApprovers
                });
            }
        });
        
        // Collect CC - juga ambil division_id
        const ccUsers = [];
        $('.cc-row').each(function() {
            const ccSelect = $(this).find('.ccDropdown');
            const ccUserId = ccSelect.val();
            const selectedOption = ccSelect.find('option:selected');
            const ccDivisionId = selectedOption.data('division') || '';
            
            if (ccUserId) {
                ccUsers.push({
                    user_id: ccUserId,
                    division_id: ccDivisionId  // 🔥 CC division ID
                });
            }
        });
        
        return {
            approvers: approvers,
            cc_users: ccUsers
        };
    }

    // ================= PLACEMENT MODE =================
    document.querySelectorAll('input[name="placementType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const type = this.value;

            document.querySelectorAll('.signature-box').forEach(b => b.remove());
            usedSigners.clear();

            document.querySelectorAll('.signer-item').forEach(item => {
                item.style.opacity = 1;
                item.style.pointerEvents = 'auto';
            });

            if (type === 'standard') {
                uploadedFiles.forEach(file => {
                    file.signatures = [];
                    file.usedSigners.clear();

                    document.querySelectorAll('.signer-item').forEach(item => {
                        const name = item.dataset.signer;

                        for (let p = 1; p <= file.totalPages; p++) {
                            file.signatures.push({
                                signer: name,
                                page: p,
                                x_percent: 0.75,
                                y_percent: 0.85,
                                mode: 'standard'
                            });
                        }

                        file.usedSigners.add(name);
                    });
                });

                document.querySelectorAll('.signer-item').forEach(item => {
                    item.style.opacity = 0.5;
                    item.style.pointerEvents = 'none';
                });
            } else if (type === 'custom') {
                renderSignaturesForPage(currentPage);
            }
        });
    });

    function disableSignerPanel() {
        document.querySelectorAll('.signer-item').forEach(item => {
            item.style.opacity = 0.5;
            item.style.pointerEvents = 'none';
        });
    }

    // ================= FILE MANAGEMENT =================
function renderFileList() {
    const list = document.getElementById('fileList');
    list.innerHTML = '';

    uploadedFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'file-item';
        div.innerHTML = `
            <i class="fas fa-file-pdf text-danger mr-3" style="font-size: 24px;"></i>
            <div class="flex-grow-1">
                <div class="font-weight-medium">${file.name}</div>
                <small class="text-muted">${(file.pdfData.length / 1024 / 1024).toFixed(2)} MB</small>
            </div>
            <button type="button" class="btn btn-light remove-file ml-2" data-index="${index}">
                <i class="fas fa-times"></i>
            </button>
        `;
        list.appendChild(div);
    });

    // Event remove file
    document.querySelectorAll('.remove-file').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            uploadedFiles.splice(index, 1);
            renderFileList();
            renderFileTabs();
            if (uploadedFiles.length === 0) {
                activeFileIndex = 0;
            }
        });
    });
}

    function renderFileTabs() {
        const tabs = document.getElementById('fileTabs');
        tabs.innerHTML = '';

        uploadedFiles.forEach((file, index) => {
            tabs.innerHTML += `
                <li class="nav-item">
                    <a class="nav-link ${index === activeFileIndex ? 'active' : ''}"
                    href="#"
                    onclick="switchFile(${index})">
                    ${file.name}
                    </a>
                </li>
            `;
        });
    }

    function switchFile(index) {
        activeFileIndex = index;
        currentPage = 1;
        pdfRendered = false; 
        renderFileTabs();

        document.querySelectorAll('.signer-item').forEach(item => {
            const name = item.dataset.signer;
            if (uploadedFiles[activeFileIndex].usedSigners.has(name)) {
                item.style.opacity = 0.5;
                item.style.pointerEvents = 'none';
            } else {
                item.style.opacity = 1;
                item.style.pointerEvents = 'auto';
            }
        });

        renderPDF();
    }

    // ================= JQUERY DOCUMENT READY - SIGNER & APPROVAL =================
    $(document).ready(function () {
        checkSigner();

        $(document).on("change", "#signerContainer select", function () {
            checkSigner();
        });

        // ADD SIGNER
        $("#addSigner").click(function () {
            let newRow = $(".signer-row:first").clone();
            newRow.find("select").val("");
            newRow.find("input[name='signer_order[]']").val(
                $("#signerContainer .signer-row").length + 1
            );
            $("#signerContainer").append(newRow);
            checkSigner();
        });

        // REMOVE SIGNER
        $(document).on("click", ".removeSigner", function () {
            if ($("#signerContainer .signer-row").length > 1) {
                $(this).closest(".signer-row").remove();
            }
            checkSigner();
        });

        // ADD APPROVAL
        $("#addApproval").click(function () {
            let newRow = $(".approval-row:first").clone();
            newRow.find("select").val("");
            $("#approvalContainer").append(newRow);
        });

        // REMOVE APPROVAL
        $(document).on("click", ".removeApproval", function () {
            if ($("#approvalContainer .approval-row").length > 1) {
                $(this).closest(".approval-row").remove();
            }
        });
    });

    function checkSigner() {
        let filledSigner = 0;
        $("#signerContainer select").each(function () {
            if ($(this).val() !== "") {
                filledSigner++;
            }
        });

        if (filledSigner >= 1) {
            $("#approvalSection").show();
        } else {
            $("#approvalSection").hide();
        }
    }

    // ================= JQUERY DOCUMENT READY - ORGANIZATION/FOLDER =================
    $(document).ready(function () {
        @if($isSuperAdmin)
            // SuperAdmin: Manual select organization
            $('#organizationSelect').on('change', function () {
                let orgId = $(this).val();
                
                if (!orgId) {
                    $('#folderSelect').html('<option value="">-- Select Organization First --</option>');
                    $('#documentTypeSelect').html('<option value="">-- Select Organization First --</option>');
                    return;
                }
                
                loadFoldersAndDocTypes(orgId);
            });
        @else
            // Non SuperAdmin: Auto load dari session organization
            const sessionOrgId = "{{ session('active_organization_id') }}";
            if (sessionOrgId) {
                $('#organizationSelectHidden').val(sessionOrgId);
                loadFoldersAndDocTypes(sessionOrgId);
            } else {
                $('#folderSelect').html('<option value="">No organization found</option>');
                $('#documentTypeSelect').html('<option value="">No organization found</option>');
            }
        @endif
    });

    // Helper function untuk load folders & document types
    function loadFoldersAndDocTypes(orgId) {
        // Show loading
        $('#folderSelect').html('<option value="">Loading folders...</option>');
        $('#documentTypeSelect').html('<option value="">Loading document types...</option>');

        // Load Folders
        $.ajax({
            url: '/folders/by-organization/' + orgId,
            type: 'GET',
            success: function (res) {
                let options = '<option value="">-- Select Folder --</option>';
                if (res.length === 0) {
                    options = '<option value="">No folders available</option>';
                } else {
                    res.forEach(function (folder) {
                        options += `<option value="${folder.id}">${folder.name}</option>`;
                    });
                }
                $('#folderSelect').html(options);
            },
            error: function (xhr) {
                console.error('Folders error:', xhr);
                $('#folderSelect').html('<option value="">Failed to load folders</option>');
            }
        });

        // Load Document Types
        $.ajax({
            url: '/workflows/by-organization/' + orgId,
            type: 'GET',
            success: function (res) {
                let options = '<option value="">-- Select Document Type --</option>';
                if (res.length === 0) {
                    options = '<option value="">No document types available</option>';
                } else {
                    res.forEach(function (item) {
                        options += `<option value="${item.id}">${item.document_type}</option>`;
                    });
                }
                $('#documentTypeSelect').html(options);
            },
            error: function (xhr) {
                console.error('Document types error:', xhr);
                $('#documentTypeSelect').html('<option value="">Failed to load document types</option>');
            }
        });
    }
</script>

@endpush

@push('styles')
    <style>
        .pdf-area {
            position: relative;
            height: 600px;
            background: #f8f9fc;
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            overflow: hidden; /* 🔥 tetap hidden */
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .pdf-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #aaa;
        }

        .signature-box {
            position: absolute;
            padding: 8px 28px 8px 12px;
            background: rgba(78, 115, 223, 0.1);
            border: 2px dashed #4e73df;
            border-radius: 6px;
            cursor: move;
            font-size: 13px;
        }

        .signature-box .delete-signature {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e74a3b;
            color: #fff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            text-align: center;
            line-height: 18px;
            cursor: pointer;
        }

        .signature-box span {
            font-weight: bold;
            color: #4e73df;
        }

        .stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e3e6f0;
            z-index: 0;
        }

        .circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e3e6f0;
            margin: 0 auto 8px;
            line-height: 32px;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }

        .step.active .circle {
            background: #4e73df;
            color: #fff;
        }

        .step.completed .circle {
            background: #1cc88a;
            color: #fff;
        }

        .step span {
            font-size: 13px;
        }

        .step-content {
            display: none;
        }

        .step-content.active {
            display: block;
        }

        .upload-box {
            border: 2px dashed #d1d3e2;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
        }

        .upload-box:hover {
            background: #f8f9fc;
        }

        .upload-box-modern {
    border: 3px dashed #4e73df;
    border-radius: 16px;
    padding: 60px 20px;
    text-align: center;
    background: #f8fbff;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-box-modern:hover {
    background: #f0f4ff;
    border-color: #2a5cff;
    transform: translateY(-3px);
}

.file-list {
    max-height: 280px;
    overflow-y: auto;
}

.file-item {
    display: flex;
    align-items: center;
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.file-item:hover {
    background: #edf2ff;
    border-color: #4e73df;
}

.file-item .remove-file {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #e74a3b;
    transition: all 0.2s;
}

.file-item .remove-file:hover {
    background: #ffebee;
}

/* Scrollbar Cantik */
.file-list::-webkit-scrollbar {
    width: 6px;
}
.file-list::-webkit-scrollbar-thumb {
    background: #c5c7d0;
    border-radius: 20px;
}
    </style>
@endpush