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
                    <h5 class="mb-1 text-primary">Upload Document</h5>
                    <p class="text-muted small mb-0">Upload the PDF document to be processed</p>
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
                <h6 class="mb-1">Drag & Drop PDF here</h6>
                <p class="text-muted small mb-3">or</p>
                <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('pdfInput').click()">
                    <i class="fas fa-folder-open"></i> Choose File
                </button>
                <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
                    Max 5 files • Max 25 MB per file • PDF only
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
                        <label class="font-weight-bold">Organization</label>
                        <select id="organizationSelect" class="form-control form-control-lg">
                            <option value="">-- Select Organization --</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Requested Division</label>
                        <select id="divisionSelect" class="form-control form-control-lg">
                            <option value="">-- Select Division --</option>
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
                        <label class="font-weight-bold">Document Type</label>
                        <select id="documentTypeSelect" class="form-control form-control-lg">
                            <option value="">-- Select Document Type --</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Folder</label>
                        <select id="folderSelect" class="form-control form-control-lg">
                            @if(!$isSuperAdmin)
                                @foreach($folderOptions as $folder)
                                    <option value="{{ $folder['id'] }}">{{ $folder['name'] }}</option>
                                @endforeach
                            @else
                                <option value="">-- Select Organization First --</option>
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
            
            <div class="alert alert-info border-0 bg-light py-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Info:</strong> Approvers in the same tier will be processed in parallel. 
                Check "Show on document" if you want the signature to appear on the PDF.
            </div>

            <!-- ================= APPROVERS ================= -->
            <h5 class="font-weight-bold text-primary mb-4">
                <i class="fas fa-users mr-2"></i> Approver List
            </h5>
            
            <div id="tierContainer" class="mb-5">
                <!-- Diisi oleh JavaScript -->
            </div>

            <!-- ================= CC ================= -->
            <div class="card border-0 bg-light shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="font-weight-bold text-primary mb-0">
                        <i class="fas fa-copy mr-2"></i> Copy Recipients
                    </h5>
                </div>
                <div class="card-body pt-0">

                    <div id="ccContainer">
                        <!-- CC Rows akan ditambah via JS -->
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="addCC">
                        <i class="fas fa-plus"></i> Add Copy Recipients
                    </button>

                </div>
            </div>

            <div class="text-right mt-5">
                <button class="btn btn-secondary prevBtn px-4">Back</button>
                <button class="btn btn-primary nextBtn px-5">Next</button>
            </div>
        </div>
    </div>
    <!-- STEP 3 -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">

            <!-- Signature Placement Type -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-primary mb-3">Signature Placement Type</h6>
                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                    <label class="btn btn-outline-primary active">
                        <input type="radio" name="placementType" value="custom" checked>
                        Custom <small class="d-block">Drag & drop manually</small>
                    </label>
                    <label class="btn btn-outline-primary">
                        <input type="radio" name="placementType" value="standard">
                        Standard <small class="d-block">Auto bottom right</small>
                    </label>
                    <label class="btn btn-outline-primary">
                        <input type="radio" name="placementType" value="fixed">
                        Fixed <small class="d-block">Approval summary page</small>
                    </label>
                </div>
            </div>

            <div class="row">
                <!-- SIGNER PANEL -->
                <div class="col-md-2">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-user-check mr-2"></i> Approvers
                    </h6>
                    <div class="list-group mb-3" id="dynamicSignerList" style="max-height: 600px; overflow-y: auto;">
                        <!-- Diisi JS -->
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i> Drag approver ke canvas PDF
                    </small>
                </div>

                <!-- PDF PREVIEW -->
                <div class="col-md-10">
                    <!-- File Tabs -->
                    <ul class="nav nav-tabs mb-3" id="fileTabs"></ul>
                    
                    <!-- Page Navigation -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-sm btn-outline-secondary" id="prevPage">
                            <i class="fas fa-chevron-left"></i> Prev
                        </button>
                        <small id="pageInfo" class="text-muted fw-bold">Page 1 of 1</small>
                        <button class="btn btn-sm btn-outline-secondary" id="nextPage">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <!-- PDF Area with Subtle Border -->
                    <div id="pdfArea" class="pdf-area">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                </div>
            </div>

            <div class="text-right mt-4">
                <button class="btn btn-secondary prevBtn">Back</button>
                <button class="btn btn-primary nextBtn">Next</button>
            </div>

        </div>
    </div>
    <!-- STEP 4  -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">
            <div class="form-group">
                <label class="font-weight-bold">Email Subject <span class="text-danger">*</span></label>
                <input id="emailSubject" class="form-control" placeholder="Document for Approval - ...">
            </div>
            <div class="form-group">
                <label class="font-weight-bold">Email Message <span class="text-danger">*</span></label>
                <textarea id="emailMessage" class="form-control" rows="5" placeholder="Please review this document..."></textarea>
            </div>

            <div class="text-right mt-4">
                <button class="btn btn-secondary prevBtn">Back</button>
                <button class="btn btn-success sendDocument" id="btnSendDocument">
                    <i class="fas fa-paper-plane"></i> Send Document
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

    const requester = {
        user_id: {{ auth()->id() }},           // Pastikan ini terkirim dari controller
        name: "{{ auth()->user()->name }}",
        division_id: {{ session('active_division_id') ?? 'null' }},
        is_requester: true
    };
    let currentStep = 0;
    let step1Data = {}; 
    const steps = document.querySelectorAll('.step');
    const contents = document.querySelectorAll('.step-content');
    
    let renderTask = null;
    let pdfRendered = false;
    
    // PDF Variables
    const pdfArea = document.getElementById('pdfArea');
    let draggedSigner = null;
    let uploadedFiles = [];
    let activeFileIndex = 0;
    const pdfCanvas = document.getElementById('pdfCanvas');
    const ctx = pdfCanvas ? pdfCanvas.getContext('2d') : null;
    let pdfDoc = null;
    let currentPage = 1;
    let totalPages = 0;
    let MAX_FILES = 5;


    // ================= ADD & REMOVE CC =================
    $(document).ready(function () {
        // Tambah CC Row
        $('#addCC').on('click', function () {
            const newRow = `
                <div class="form-row align-items-center cc-row">
                    <div class="col-md-10">
                        <select class="form-control ccDropdown">
                            <option value="">Select Copy Recipients</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm w-100 removeCC">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>`;

            $('#ccContainer').append(newRow);

            const lastDropdown = $('#ccContainer .ccDropdown').last();
            if (typeof window.ccUsers !== 'undefined' && window.ccUsers.length > 0) {
                updateSingleCcDropdown(lastDropdown, window.ccUsers);
            }
        });
        // Hapus CC Row
        $(document).on('click', '.removeCC', function () {
                $(this).closest('.cc-row').remove();
        });

        updateUploadAreaState();

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

        // Step 3: Render dynamic signers dari Step 2
        if (currentStep === 2 && uploadedFiles.length > 0) {
            if (!pdfRendered) {
                renderPDF();
                pdfRendered = true;
            }
            
            renderSignerListFromStep2();
            
            // Re-render signatures untuk current page
            renderSignaturesForPage(currentPage);
        }
    }

    // ================= DYNAMIC SIGNER LIST FROM STEP 2 =================
 function renderSignerListFromStep2() {
    const step2Data = collectStep2Data();
    const showOnDocSigners = [];
    
    step2Data.approvers.forEach(tier => {
        tier.approvers.forEach(approver => {
            if (approver.show_on_document) {
                const tierBox = $(`.tier-box[data-tier="${tier.tier}"]`);
                const select = tierBox.find('.approver-select').filter(function() {
                    return $(this).val() == approver.user_id;
                });
                
                let approverName = select.length > 0 
                    ? select.find('option:selected').text() 
                    : approver.name || 'Unknown User';

                showOnDocSigners.push({
                    id: approver.user_id,
                    name: approverName,
                    tier: tier.tier,
                    division_id: approver.division_id
                });
            }
        });
    });

    const signerList = document.getElementById('dynamicSignerList');
    signerList.innerHTML = '';

    if (showOnDocSigners.length === 0) {
        signerList.innerHTML = `
            <div class="list-group-item text-muted small p-3 text-center">
                <i class="fas fa-info-circle"></i> No approvers selected for document
            </div>`;
        return;
    }

    showOnDocSigners.forEach(signer => {
        const item = document.createElement('div');
        item.className = 'list-group-item signer-item px-3 py-2';
        item.style.cursor = 'pointer';
        item.title = 'Klik untuk menempatkan di PDF';
        
        item.dataset.signerId = signer.id;
        item.dataset.signerName = signer.name;
        item.dataset.tier = signer.tier;

        item.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-user text-primary mr-2"></i>
                <div class="flex-grow-1">
                    <div class="font-weight-medium" style="font-size: 13px;">${signer.name}</div>
                    <small class="text-muted">Tier ${signer.tier}</small>
                </div>
            </div>
        `;

        // === CLICK TO PLACE ===
        item.addEventListener('click', () => handleSignerClick(signer));

        signerList.appendChild(item);
    });
}

function handleSignerClick(signer) {
    const currentFile = uploadedFiles[activeFileIndex];
    if (!currentFile) {
        Swal.fire('Error', 'No file selected', 'error');
        return;
    }

    // Cek apakah sudah ada di halaman ini
    const alreadyOnThisPage = currentFile.signatures.some(
        sig => sig.signer_id == signer.id && sig.page === currentPage
    );

    if (alreadyOnThisPage) {
        Swal.fire({
            icon: 'warning',
            title: 'Already Placed',
            text: `Approver ini sudah ditempatkan di halaman ${currentPage}`,
            confirmButtonText: 'OK'
        });
        return;
    }

    // Default position (bisa diubah)
    const defaultXPercent = 78;   // agak kanan
    const defaultYPercent = 65;   // agak bawah

    const xPx = (defaultXPercent / 100) * pdfCanvas.width;
    const yPx = (defaultYPercent / 100) * pdfCanvas.height;

    const signatureData = {
        signer_id: signer.id,
        signer_name: signer.name,
        tier: parseInt(signer.tier || 1),
        page: currentPage,
        x_percent: defaultXPercent,
        y_percent: defaultYPercent,
        pos_x: Math.round(xPx),
        pos_y: Math.round(yPx)
    };

    currentFile.signatures.push(signatureData);

    // Buat visual box
    const box = document.createElement('div');
    box.className = 'signature-box';
    box.dataset.signerId = signer.id;
    box.dataset.signerName = signer.name;
    box.dataset.page = currentPage;
    box.dataset.x = defaultXPercent;
    box.dataset.y = defaultYPercent;
    box.dataset.tier = signer.tier || 1;
    box.dataset.fileIndex = activeFileIndex;

    box.innerHTML = `
        <div class="delete-signature">×</div>
        <div class="signature-text">
            <span class="approved-by">Approved by</span>
            <span class="approver-name">${signer.name}</span>
            <span class="at">at</span>
            <span class="datetime">${new Date().toLocaleString('id-ID', { 
                day: '2-digit', month: 'short', year: 'numeric', 
                hour: '2-digit', minute: '2-digit' 
            }).replace(',', '')}</span>
        </div>
        <div class="resize-handle"></div>
    `;

    positionSignatureBoxAtCanvas(box, xPx, yPx);
    makeSignatureBoxDraggable(box);   // tetap bisa di-drag
    pdfArea.appendChild(box);
makeResizable(box);
    updateSignerUIForCurrentFile();

    // Optional: feedback
    // box.style.animation = 'pulse 0.6s';
}

    // Drag handler untuk dynamic signers
   // ================= DRAG & DROP - CANVAS AREA ONLY =================

// Drag start dari signer panel
function handleSignerDragStart(e) {
    const signerId = this.dataset.signerId;
    const currentFile = uploadedFiles[activeFileIndex];


    draggedSigner = {
        id: signerId,
        name: this.dataset.signerName,
        tier: this.dataset.tier
    };

    this.style.opacity = '0.5';
}

// Drag over di CANVAS saja (bukan pdfArea)
pdfCanvas.addEventListener('dragover', function (e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    pdfCanvas.style.cursor = 'copy';
});

// Drag leave dari canvas
pdfCanvas.addEventListener('dragleave', function () {
    pdfCanvas.style.cursor = 'default';
});

// DROP di CANVAS saja
// Drop di CANVAS saja
pdfCanvas.addEventListener('drop', function (e) {
    console.log('DROP TRIGGERED');
    e.preventDefault();
    pdfCanvas.style.cursor = 'default';

    if (!draggedSigner || !draggedSigner.id) {
        console.warn("⚠️ draggedSigner kosong");
        return;
    }

    const currentFile = uploadedFiles[activeFileIndex];
    if (!currentFile) {
        console.warn("⚠️ File tidak ditemukan");
        return;
    }

    // 🔥 PENTING: Cek apakah signer sudah digunakan di HALAMAN INI
    const alreadyOnThisPage = currentFile.signatures.some(
        sig => sig.signer_id == draggedSigner.id && sig.page === currentPage
    );
    
    if (alreadyOnThisPage) {
        Swal.fire({
            icon: 'warning',
            title: 'Already Placed',
            text: `This approver already has a signature on page ${currentPage}`,
            confirmButtonText: 'OK'
        });
        return;
    }

    // ===== HITUNG POSISI DI DALAM CANVAS =====
    // ===== HITUNG POSISI DI DALAM CANVAS =====
const canvasRect = pdfCanvas.getBoundingClientRect();

    let xPx = e.clientX - canvasRect.left;
    let yPx = e.clientY - canvasRect.top;

    const boxWidth = 200;
    const boxHeight = 60;

    // Center box di cursor
    xPx = xPx - (boxWidth / 2);
    yPx = yPx - (boxHeight / 2) + 10;

    // 🔥 BUFFER DI SEMUA SISI
    const bufferLeft = 10;
    const bufferRight = 10;
    const bufferTop = 10;
    const bufferBottom = 100;   // lebih besar di bawah karena box signature

    xPx = Math.max(bufferLeft, Math.min(xPx, canvasRect.width - boxWidth - bufferRight));
    yPx = Math.max(bufferTop, Math.min(yPx, canvasRect.height - boxHeight - bufferBottom));
    console.log({
    canvasWidth: canvasRect.width,
    canvasHeight: canvasRect.height,
    xPx,
    yPx
});

    const xPercent = Math.round((xPx / canvasRect.width) * 100);
    const yPercent = Math.round((yPx / canvasRect.height) * 100);

    console.log(`📍 Drop di Canvas Page ${currentPage}: x=${xPercent}%, y=${yPercent}%`);

    // Simpan data signature dengan page yang benar
    const signatureData = {
        signer_id: draggedSigner.id,
        signer_name: draggedSigner.name,
        tier: parseInt(draggedSigner.tier || 1),
        page: currentPage,  // 🔥 PENTING: Pakai currentPage global
        x_percent: xPercent,
        y_percent: yPercent,
        pos_x: Math.round(xPx),
        pos_y: Math.round(yPx)
    };

    currentFile.signatures.push(signatureData);
    updateSignerUIForCurrentFile();

    // ===== BUAT VISUAL BOX =====
    // ===== BUAT VISUAL BOX =====
const box = document.createElement('div');
box.className = 'signature-box';
box.dataset.signerId = draggedSigner.id;
box.dataset.signerName = draggedSigner.name;
box.dataset.page = currentPage;
box.dataset.x = xPercent;
box.dataset.y = yPercent;
box.dataset.tier = draggedSigner.tier || 1;
box.dataset.fileIndex = activeFileIndex;

// Format: Approved by Name at Date Time (memanjanghorizontal)
box.innerHTML = `
    <div class="delete-signature">×</div>
    <div class="signature-text">
        <span class="approved-by">Approved by</span>
        <span class="approver-name">${draggedSigner.name}</span>
        <span class="at">at</span>
        <span class="datetime">${new Date().toLocaleString('id-ID', { 
            day: '2-digit', month: 'short', year: 'numeric', 
            hour: '2-digit', minute: '2-digit' 
        }).replace(',', '')}</span>
    </div>
    <div class="resize-handle"></div>
`;

    positionSignatureBoxAtCanvas(box, xPx, yPx);
    makeSignatureBoxDraggable(box);
    makeResizable(box);
    
    pdfArea.appendChild(box);

    console.log(`📦 Box ditambahkan untuk ${draggedSigner.name} di page ${currentPage}`);

    draggedSigner = null;
});
// ===== POSITION BOX RELATIVE TO CANVAS =====
// ===== POSITION BOX RELATIVE TO CANVAS =====
function positionSignatureBoxAtCanvas(box, xPx, yPx) {
    const pdfAreaRect = pdfArea.getBoundingClientRect();
    const canvasRect = pdfCanvas.getBoundingClientRect();

    box.style.position = 'absolute';
    box.style.left = (canvasRect.left - pdfAreaRect.left + xPx) + 'px';
    box.style.top = (canvasRect.top - pdfAreaRect.top + yPx) + 'px';
    box.style.zIndex = 100;
}

// ===== MAKE SIGNATURE BOX DRAGGABLE =====
function makeSignatureBoxDraggable(box) {
    let isDragging = false;
    let startX, startY, originalLeft, originalTop;

    box.addEventListener('pointerdown', function (e) {
        if (e.target.classList.contains('delete-signature')) return;
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        originalLeft = parseFloat(box.style.left) || 0;
        originalTop = parseFloat(box.style.top) || 0;

        box.style.cursor = 'grabbing';
        box.setPointerCapture(e.pointerId);
    });

    box.addEventListener('pointermove', function (e) {
        if (!isDragging) return;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        
        let newLeft = originalLeft + dx;
        let newTop = originalTop + dy;

        const pdfAreaRect = document.getElementById('pdfArea').getBoundingClientRect();
        const canvasRect = pdfCanvas.getBoundingClientRect();

        const minX = canvasRect.left - pdfAreaRect.left;
        const minY = canvasRect.top - pdfAreaRect.top;

        const maxX = minX + canvasRect.width - box.offsetWidth - 10;
        const maxY = minY + canvasRect.height - box.offsetHeight - 60;

        newLeft = Math.max(minX + 10, Math.min(newLeft, maxX));
        newTop = Math.max(minY + 10, Math.min(newTop, maxY));

        box.style.left = newLeft + 'px';
        box.style.top = newTop + 'px';

        // Update data
        const xPercent = Math.round(((newLeft - minX) / canvasRect.width) * 100);
        const yPercent = Math.round(((newTop - minY) / canvasRect.height) * 100);
        // Setelah update data signatures...
box.dataset.x = xPercent;
box.dataset.y = yPercent;

        const file = uploadedFiles[parseInt(box.dataset.fileIndex || activeFileIndex)];
        if (file) {
            file.signatures = file.signatures.map(sig => {
                if (sig.signer_id == box.dataset.signerId && sig.page == parseInt(box.dataset.page)) {
                    return { ...sig, x_percent: xPercent, y_percent: yPercent };
                }
                return sig;
            });
        }
    });

    box.addEventListener('pointerup', () => {
        isDragging = false;
        box.style.cursor = 'move';
    });
}
// ================= UPDATED: Per File Disable Logic =================
// ================= UPDATE SIGNER UI (PERBAIKAN) =================
// ================= UPDATE SIGNER UI - FIXED: CEK PER HALAMAN =================
// ✅ FIXED: Check if signer is used on CURRENT PAGE, not globally
// function updateSignerUIForCurrentFile() {
//     const currentFile = uploadedFiles[activeFileIndex];
//     const currentPageNum = currentPage; // Halaman yang sedang dilihat
    
//     document.querySelectorAll('.signer-item').forEach(item => {
//         const signerId = item.dataset.signerId;
        
//         // Cek apakah signer sudah digunakan di HALAMAN INI (bukan file secara keseluruhan)
//         const isUsedOnCurrentPage = currentFile && currentFile.signatures.some(
//             sig => sig.signer_id == signerId && sig.page === currentPageNum
//         );

//         if (isUsedOnCurrentPage) {
//             item.classList.add('disabled');
//             item.style.opacity = '0.5';
//             item.style.pointerEvents = 'none';
//         } else {
//             item.classList.remove('disabled');
//             item.style.opacity = '1';
//             item.style.pointerEvents = 'auto';
//         }
//     });
// }

// Panggil fungsi ini setiap kali switch file atau ada perubahan signature

    // Update usedSigners tracking untuk dynamic signers
// 🔥 PERBAIKI: Check usedSigners di SEMUA files
// function disableSignerUI(signerId) {
//     // Disable UI jika signer digunakan di file MANAPUN
//     const isUsedAnywhere = uploadedFiles.some(file => file.usedSigners.has(signerId));
    
//     document.querySelectorAll('.signer-item').forEach(item => {
//         if (item.dataset.signerId === signerId) {
//             if (isUsedAnywhere) {
//                 item.classList.add('disabled');
//                 item.style.opacity = '0.5';
//                 item.style.pointerEvents = 'none';
//             }
//         }
//     });
// }

// function enableSignerUI(signerId) {
//     // Enable UI jika signer TIDAK digunakan di file MANAPUN
//     const isUsedAnywhere = uploadedFiles.some(file => file.usedSigners.has(signerId));
    
//     document.querySelectorAll('.signer-item').forEach(item => {
//         if (item.dataset.signerId === signerId && !isUsedAnywhere) {
//             item.classList.remove('disabled');
//             item.style.opacity = '1';
//             item.style.pointerEvents = 'auto';
//         }
//     });
// }

    // ================= NEXT BUTTON HANDLER - FULL VERSION =================
    document.querySelectorAll('.nextBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            // ================= STEP 1 VALIDATION =================
            if (currentStep === 0) {
                // File validation
                if (uploadedFiles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No File Uploaded',
                        text: 'Please upload PDF file first',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Form validation
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
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Form',
                        html: `
                            <div class="text-left">
                                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                <strong>Required fields:</strong><br>
                                - Organization<br>
                                - Division<br>
                                - Document Type<br>
                                - Folder
                            </div>
                        `,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Save Step 1 data
                step1Data = collectStep1Data();
                console.log('✅ Step 1 Data:', step1Data);
                
                // Preload Step 2 (approvers & CC)
                loadWorkflowApprovers(orgId, docType, divId);
                loadCC(orgId, docType, divId);
            }

            // ================= STEP 2 VALIDATION - APPROVERS =================
            if (currentStep === 1) {
                const validation = validateApproversPerTier();
                
                if (!validation.valid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Approvers',
                        html: `<div class="text-left">${validation.message}</div>`,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const step2DataTemp = collectStep2Data();
                
                // Check minimal 1 approver total
                if (step2DataTemp.approvers.length === 0 || 
                    step2DataTemp.approvers.every(tier => tier.approvers.length === 0)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Approver Selected',
                        text: 'Please select at least one approver in each tier!',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // 🔥 Count show_on_doc approvers untuk Step 3 preview
                const showOnDocCount = step2DataTemp.approvers
                    .flatMap(tier => tier.approvers)
                    .filter(a => a.show_on_document).length;

                // Save Step 2 data
                step1Data.approvers_data = step2DataTemp;
                step1Data.cc_users = step2DataTemp.cc_users;
                
                console.log('✅ Step 2 Data:', step2DataTemp);
                console.log(`📋 Show on Doc Approvers: ${showOnDocCount}`);

                // Warning jika no show_on_doc
                if (showOnDocCount === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Signatures on Document',
                        html: `
                            <div class="text-left">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                No approvers selected to show on document.<br>
                                <small class="text-muted">You can still continue to Step 3 for other placement types.</small>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Continue to Step 3',
                        cancelButtonText: 'Back to Step 2',
                        confirmButtonColor: '#1cc88a'
                    }).then(result => {
                        if (result.isConfirmed) {
                            currentStep++;
                            updateStep();
                        }
                    });
                    return;
                }

                // Auto proceed ke Step 3
                currentStep++;
                updateStep();
                return;
            }

            // ================= STEP 3 VALIDATION - SIGNATURES =================
// ================= STEP 3 VALIDATION - SIGNATURES =================
if (currentStep === 2) {
    const signatureData = collectSignatureData();
    
    // Check minimal 1 signature jika ada show_on_doc approvers
    const hasShowOnDoc = step1Data.approvers_data?.approvers
        ?.flatMap(tier => tier.approvers)
        ?.some(a => a.show_on_document) || false;

    // 🔥🔥 NEW: VALIDATION - EVERY APPROVER MUST BE ON EVERY FILE 🔥🔥
    if (hasShowOnDoc) {
        // Get all show_on_document approvers from Step 2
        const showOnDocApprovers = [];
        step1Data.approvers_data.approvers.forEach(tierData => {
            tierData.approvers.forEach(approver => {
                if (approver.show_on_document) {
                    showOnDocApprovers.push({
                        user_id: approver.user_id,
                        name: approver.name,
                        tier: tierData.tier
                    });
                }
            });
        });

        // Validation per file
        let missingSignatures = [];
        
        uploadedFiles.forEach((file, fileIdx) => {
            // Get signers used in this specific file
            const fileSignerIds = new Set(file.signatures.map(s => s.signer_id));
            
            showOnDocApprovers.forEach(approver => {
                if (!fileSignerIds.has(approver.user_id)) {
                    missingSignatures.push({
                        file_name: file.name,
                        file_index: fileIdx,
                        approver_name: approver.name,
                        approver_id: approver.user_id,
                        tier: approver.tier
                    });
                }
            });
        });

        // If there are missing signatures, show error
        if (missingSignatures.length > 0) {
            // Group by file for cleaner display
            const groupedByFile = {};
            missingSignatures.forEach(m => {
                if (!groupedByFile[m.file_name]) {
                    groupedByFile[m.file_name] = [];
                }
                groupedByFile[m.file_name].push(`${m.approver_name} (Tier ${m.tier})`);
            });

            let errorHtml = `
                <div class="text-left">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                    <strong>All approvers must have signatures on ALL files!</strong><br><br>
            `;

            Object.keys(groupedByFile).forEach(fileName => {
                errorHtml += `
                    <div class="mb-2">
                        <strong>📄 ${fileName}</strong><br>
                        <span class="text-danger">Missing:</span> 
                        ${groupedByFile[fileName].join(', ')}
                    </div>
                `;
            });

            errorHtml += `
                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-info-circle text-info mr-1"></i>
                            Drag approvers from right panel to each PDF file<br>
                            Or switch to <strong>Standard</strong> / <strong>Fixed</strong> mode for auto-placement
                        </small>
                    </div>
                </div>
            `;

            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Signature Placement',
                html: errorHtml,
                confirmButtonText: 'OK'
            });
            return;
        }
    }

    let totalSignatures = 0;
    signatureData.forEach(file => {
        totalSignatures += file.signatures.length;
    });

    if (hasShowOnDoc && totalSignatures === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Signature Placed',
            html: `
                <div class="text-left">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                    No signature positions placed on document.<br>
                    <strong>Drag approvers from right panel to PDF</strong><br><br>
                    <div class="mt-2 p-2 bg-light rounded">
                        <small class="text-muted">
                            Or switch to <strong>Standard</strong> / <strong>Fixed</strong> mode for auto-placement
                        </small>
                    </div>
                </div>
            `,
            confirmButtonText: 'OK'
        });
        return;
    }

    console.log('✅ Step 3 Signature Data:', signatureData);
    
    // Save signature data
    step1Data.signature_data = signatureData;
    
    // Auto proceed ke Step 4
    currentStep++;
    updateStep();
    return;
}

            // ================= STEP 4 - FINAL VALIDATION =================
            if (currentStep === 3) {
                // Final validation & preview
                const completePayload = collectCompletePayload();
                
                Swal.fire({
                    title: 'Review & Send',
                    html: `
                        <div class="p-3">
                            <div class="row text-left mb-3">
                                <div class="col-6">
                                    <strong>📁 Files:</strong><br>
                                    <small class="text-muted">${completePayload.files.join(', ')}</small>
                                </div>
                                <div class="col-6">
                                    <strong>📋 Approvers:</strong><br>
                                    <small class="text-muted">${completePayload.document_approvals.length} approvers</small>
                                </div>
                            </div>
                            <div class="row text-left mb-3">
                                <div class="col-6">
                                    <strong>✍️ Signatures:</strong><br>
                                    <small class="text-muted">${completePayload.approval_positions.length} positions</small>
                                </div>
                                <div class="col-6">
                                    <strong>👥 CC:</strong><br>
                                    <small class="text-muted">${completePayload.document_shares.length} recipients</small>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Email Subject</label>
                                <input type="text" class="form-control form-control-sm" 
                                    id="finalSubject" value="${completePayload.email_subject}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Email Message</label>
                                <textarea class="form-control" rows="3" id="finalMessage">${completePayload.email_message}</textarea>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-paper-plane"></i> Send Document',
                    cancelButtonText: 'Back',
                    confirmButtonColor: '#1cc88a',
                    preConfirm: () => {
                        completePayload.email_subject = $('#finalSubject').val();
                        completePayload.email_message = $('#finalMessage').val();
                        return completePayload;
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        // Trigger send
                        document.querySelector('.sendDocument').dispatchEvent(new Event('click'));
                    }
                });
                
                return; // Don't auto advance
            }

            // Advance step
            if (currentStep < contents.length - 1) {
                currentStep++;
                updateStep();
            }
        });
    });

    function validateApproversPerTier() {
        let isValid = true;
        let errorMessage = '';

        $('.tier-box').each(function() {
            const tier = $(this).data('tier');
            const selectedApprovers = $(this).find('.approver-select').filter(function() {
                return $(this).val() !== '';
            }).length;

            if (selectedApprovers === 0) {
                isValid = false;
                errorMessage += `• Tier ${tier} has no approver selected<br>`;
            }
        });

        if (!isValid) {
            return {
                valid: false,
                message: 'Each tier must have at least one approver:<br>' + errorMessage
            };
        }

        return { valid: true };
    }

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
            $('#tierContainer').html('<p class="text-muted">Loading approvers...</p>');
        },
        success: function(response) {
            $('#tierContainer').empty();

            if (!response.workflow_steps || response.workflow_steps.length === 0) {
                $('#tierContainer').html('<p class="text-warning">No approvers available.</p>');
                return;
            }

            response.workflow_steps.forEach(group => {
                const tier = parseInt(group.tier);
                const isTierZero = tier === 0;

                let tierHtml = `
                    <div class="tier-box border rounded p-3 mb-4 ${isTierZero ? 'bg-light' : ''}" 
                         data-tier="${tier}" data-sla-days="${group.sla_days || 0}">
                    <h6 class="text-primary mb-3">
                        ${isTierZero ? 
                            '<i class="fas fa-user-check text-success"></i> Tier 0 • ' : 
                            'Tier ' + tier + ' • '}
                        ${group.division_name || group.title || 'Approver'}
                        ${group.sla_days > 0 ? `<small class="text-muted">(${group.sla_days} days SLA)</small>` : ''}
                    </h6>
                    <div class="approvers-list" data-tier="${tier}" data-division-id="${group.division_id || ''}"></div>
                    
                    ${!isTierZero ? `
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2 add-approver-per-tier"
                            data-tier="${tier}" data-division-id="${group.division_id || ''}">
                        <i class="fas fa-plus"></i> Add Approver
                    </button>` : ''}
                </div>`;

                $('#tierContainer').append(tierHtml);
            });

            // Render approver dari backend
            response.workflow_steps.forEach(group => {
                const tier = parseInt(group.tier);
                if (group.users && group.users.length > 0) {
                    addApproverRow(tier, group.users, group.division_id || '');
                }
            });

            // ================= TAMBAHKAN REQUESTER DI TIER 0 =================
            if ($('.tier-box[data-tier="0"]').length > 0) {
                addRequesterToTierZero();
            }

            // Event listener Add Approver (hanya untuk tier > 0)
            $('.add-approver-per-tier').off('click').on('click', function() {
                const tier = $(this).data('tier');
                const divisionId = $(this).data('division-id') || '';
                const users = response.workflow_steps.find(g => parseInt(g.tier) === tier)?.users || [];
                addApproverRow(tier, users, divisionId);
            });
        },
        error: function() {
            $('#tierContainer').html('<p class="text-danger">Failed to load approvers.</p>');
        }
    });
}


function addRequesterToTierZero() {
    const tierZeroContainer = $('.tier-box[data-tier="0"] .approvers-list');
    
    if (tierZeroContainer.find('.requester-row').length > 0) return; // cegah duplikat

    const requesterHtml = `
        <div class="row align-items-center approver-row mb-3 requester-row" data-division="${requester.division_id}">
            <div class="col-md-5">
                <label class="small text-muted">Requester</label>
                <select class="form-control approver-select" data-tier="0" disabled>
                    <option value="${requester.user_id}" selected>${requester.name} (You)</option>
                </select>
            </div>
            
            <div class="col-md-4 mt-4">
                <div class="form-check">
                    <input class="form-check-input show-on-doc" type="checkbox" checked>
                    <label class="form-check-label small">Show on document</label>
                </div>
            </div>

            <div class="col-md-3 mt-4">
                <span class="text-success small"><i class="fas fa-lock"></i> Auto Approved</span>
            </div>
        </div>`;

    // Masukkan di paling atas Tier 0
    tierZeroContainer.prepend(requesterHtml);
}
    // Fungsi untuk menambah row approver di dalam tier
    function addApproverRow(tier, usersList, divisionId = '') {
        const container = $(`.approvers-list[data-tier="${tier}"]`);
        
        let options = '<option value="">-- Select Approver --</option>';
        usersList.forEach(user => {
            options += `<option value="${user.id}" data-division="${user.division_id || divisionId}">
                ${user.name}
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
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Selection',
                    text: 'At least 1 approver is required per tier!',
                    confirmButtonText: 'OK'
                });
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
                window.ccUsers = response.users || [];  
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
        let options = '<option value="">Select Copy Recipients</option>';
        
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

//     pdfArea.addEventListener('dragover', function (e) {
//         e.preventDefault();
//     });

// // ================= DROP EVENT - VERSI FIXED =================
// pdfArea.addEventListener('drop', function (e) {
//     e.preventDefault();
//     pdfArea.style.borderColor = '';

//     if (!draggedSigner || !draggedSigner.id) {
//         console.warn("⚠️ draggedSigner kosong");
//         return;
//     }

//     const currentFile = uploadedFiles[activeFileIndex];
//     if (!currentFile || currentFile.usedSigners.has(draggedSigner.id)) {
//         console.warn("⚠️ Signer sudah digunakan atau file tidak ditemukan");
//         return;
//     }

//    const canvasRect = pdfCanvas.getBoundingClientRect();
//     let xPx = e.clientX - canvasRect.left;
//     let yPx = e.clientY - canvasRect.top;

//     // === AMBIL POSISI TENGAH BOX ===
//     const boxWidth = 220;   // estimasi lebar box
//     const boxHeight = 70;   // estimasi tinggi box

//     xPx = xPx - (boxWidth / 2);   // geser ke tengah
//     yPx = yPx - (boxHeight / 2);

//     // Clamp
//     xPx = Math.max(10, Math.min(xPx, canvasRect.width - boxWidth - 10));
//     yPx = Math.max(10, Math.min(yPx, canvasRect.height - boxHeight - 10));

//     const xPercent = Math.round((xPx / canvasRect.width) * 100);
//     const yPercent = Math.round((yPx / canvasRect.height) * 100);

//     console.log(`📍 Drop Tengah Box: ${xPercent}% , ${yPercent}%`);

//     // Simpan data
//     const signatureData = {
//         signer_id: draggedSigner.id,
//         signer_name: draggedSigner.name,
//         tier: parseInt(draggedSigner.tier || 1),
//         page: currentPage,
//         x_percent: xPercent,
//         y_percent: yPercent,
//         pos_x: Math.round(xPx),
//         pos_y: Math.round(yPx)
//     };
//     console.log(signatureData);
    

//     currentFile.signatures.push(signatureData);
//     currentFile.usedSigners.add(draggedSigner.id);
//     updateSignerUIForCurrentFile();

//     // ================= BUAT BOX VISUAL =================
//     const box = document.createElement('div');
//     box.classList.add('signature-box');
//     box.dataset.signerId = draggedSigner.id;
//     box.dataset.signerName = draggedSigner.name;
//     box.dataset.page = currentPage;
//     box.dataset.x = xPercent;
//     box.dataset.y = yPercent;
//     box.dataset.tier = draggedSigner.tier || 1;
//     box.dataset.fileIndex = activeFileIndex;

//     box.innerHTML = `
//         <div class="delete-signature">×</div>
//         <div class="signer-info">
//             <span class="signer-name">${draggedSigner.name}</span><br>
//             <small class="tier-info">Tier ${box.dataset.tier} • Signature</small>
//         </div>
//     `;

//     // Position & Make draggable
//     positionSignatureBox(box);
//     makeDraggable(box);
    
//     // Tambahkan ke PDF Area
//     pdfArea.appendChild(box);

//     console.log(`📦 Box ditambahkan untuk ${draggedSigner.name}`);

//     draggedSigner = null;   // Reset
// });
    // Signer UI Controls
    // function disableSignerUI(name) {
    //     const currentFile = uploadedFiles[activeFileIndex];
    //     currentFile.usedSigners.add(name);
    //     document.querySelectorAll('.signer-item').forEach(item => {
    //         if (item.dataset.signer === name) {
    //             item.classList.add('disabled');
    //             item.style.opacity = 0.5;
    //             item.style.pointerEvents = 'none';
    //         }
    //     });
    // }

    // function enableSignerUI(name) {
    //     const currentFile = uploadedFiles[activeFileIndex];
    //     currentFile.usedSigners.delete(name);
    //     document.querySelectorAll('.signer-item').forEach(item => {
    //         if (item.dataset.signer === name) {
    //             item.classList.remove('disabled');
    //             item.style.opacity = 1;
    //             item.style.pointerEvents = 'auto';
    //         }
    //     });
    // }

    // Delete Signature
// ================= DELETE SIGNATURE (FIXED) =================
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-signature')) {
        const box = e.target.closest('.signature-box');
        if (!box) return;

        const signerId = box.dataset.signerId;
        const page = parseInt(box.dataset.page);
        const fileIndex = parseInt(box.dataset.fileIndex || activeFileIndex);

        box.remove();

        const file = uploadedFiles[fileIndex];
        if (file) {
            file.signatures = file.signatures.filter(sig => 
                !(sig.signer_id == signerId && sig.page == page)
            );
        }

        updateSignerUIForCurrentFile();
        renderSignaturesForPage(currentPage);
    }
});
    // Draggable Signature Boxes
function makeDraggable(element) {
    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;
    const fileIndex = parseInt(element.dataset.fileIndex || activeFileIndex);

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

        const xPercent = Math.round((x / canvasRect.width) * 100);
        const yPercent = Math.round((y / canvasRect.height) * 100);

        // 🔥 UPDATE DATA signature di file
        element.dataset.x = xPercent;
        element.dataset.y = yPercent;

        // Update di array signatures
        const file = uploadedFiles[fileIndex];
        if (file) {
            file.signatures = file.signatures.map(sig => {
                if (sig.signer_id === element.dataset.signerId && 
                    sig.page === parseInt(element.dataset.page)) {
                    return {
                        ...sig,
                        x_percent: xPercent,
                        y_percent: yPercent
                    };
                }
                return sig;
            });
        }

        positionSignatureBox(element);
    });

    element.addEventListener('pointerup', function () {
        isDragging = false;
    });
}

function positionSignatureBox(box) {
    const canvasRect = pdfCanvas.getBoundingClientRect();
    const xPx = (parseFloat(box.dataset.x) / 100) * canvasRect.width;
    const yPx = (parseFloat(box.dataset.y) / 100) * canvasRect.height;

    box.style.left = `${xPx}px`;
    box.style.top = `${yPx}px`;
    box.style.position = 'absolute';
    box.style.zIndex = 10;
}
    // ================= PDF UPLOAD - DRAG & DROP + CLICK AREA =================
    const uploadArea = document.getElementById('uploadArea');
    const pdfInput = document.getElementById('pdfInput');

    // Klik di seluruh area upload
    uploadArea.addEventListener('click', function () {
        pdfInput.click();
    });

    // Drag & Drop functionality
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.borderColor = '#2a5cff';
        this.style.backgroundColor = '#f0f4ff';
    });

    uploadArea.addEventListener('dragleave', function () {
        this.style.borderColor = '#4e73df';
        this.style.backgroundColor = '#f8fbff';
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.borderColor = '#4e73df';
        this.style.backgroundColor = '#f8fbff';

        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // Fungsi utama untuk memproses file (dipakai oleh click dan drop)
// ================= PDF UPLOAD - DRAG & DROP + CLICK AREA =================
function handleFiles(files) {
    const currentTotal = uploadedFiles.length;
    const remaining = MAX_FILES - currentTotal;

    if (files.length > remaining) {
        Swal.fire({
            icon: 'warning',
            html: `You can only upload a maximum of <strong>${MAX_FILES}</strong> files.`,
            confirmButtonText: 'OK'
        });
        // Hanya proses sebanyak yang masih boleh
        files = Array.from(files).slice(0, remaining);
    }

    if (files.length === 0) return;

    files.forEach(file => {
        if (file.type !== "application/pdf") {
            Swal.fire({
               icon: 'error',
                title: 'Invalid File Type',
                text: 'Only PDF files are permitted. Please upload a PDF document.',
            });
            return;
        }

        const reader = new FileReader();

        const MAX_SIZE_MB = 25;   // ← Ubah di sini

if (file.size > MAX_SIZE_MB * 1024 * 1024) {
    Swal.fire({
        icon: 'warning',
        title: 'File Too Large',
        html: `Maximum file size is <strong>${MAX_SIZE_MB} MB</strong>.<br>
               Your file: <strong>${(file.size / 1024 / 1024).toFixed(2)} MB</strong>`,
        confirmButtonText: 'OK'
    });
    return;
}

        reader.onload = function () {
            const pdfData = new Uint8Array(this.result);
            const pdfBlob = new Blob([pdfData], { type: 'application/pdf' });

            pdfjsLib.getDocument(pdfData).promise.then(pdf => {
                uploadedFiles.push({
                    name: file.name,
                    pdfData: pdfData,
                    pdfBlob: pdfBlob,
                    totalPages: pdf.numPages,
                    signatures: [],
                    usedSigners: new Set()
                });

                renderFileList();
                renderFileTabs();

                if (uploadedFiles.length === 1) {
                    switchFile(0);
                }

                // Disable upload area jika sudah mencapai batas
                updateUploadAreaState();
            }).catch(err => {
                console.error("Error loading PDF:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memproses PDF',
                    text: 'File PDF rusak atau tidak dapat dibaca.'
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });
}

function updateUploadAreaState() {
    const uploadArea = document.getElementById('uploadArea');
    const isMax = uploadedFiles.length >= MAX_FILES;

    if (isMax) {
    uploadArea.style.opacity = '0.6';
    uploadArea.style.pointerEvents = 'none';
    uploadArea.innerHTML = `
        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
        <h6 class="mb-1 text-success">Maximum ${MAX_FILES} Files Reached</h6>
        <p class="text-muted small">You have uploaded ${uploadedFiles.length} file(s).</p>
    `;
} else {
    uploadArea.style.opacity = '1';
    uploadArea.style.pointerEvents = 'auto';
    uploadArea.innerHTML = `
        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
        <h6 class="mb-1">Drag & Drop PDF here</h6>
        <p class="text-muted small mb-3">or</p>
        <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('pdfInput').click()">
            <i class="fas fa-folder-open"></i> Choose File
        </button>
        <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
            Max ${MAX_FILES} files • Max 25 MB per file • PDF only
        </p>
    `;
}
}
    // Input file change (backup)
    pdfInput.addEventListener('change', function (e) {
        if (e.target.files.length > 0) {
            handleFiles(Array.from(e.target.files));
            pdfInput.value = ''; // Reset agar bisa upload file sama lagi
        }
    });

function renderPDF(pageNumber = 1) {
    if (!uploadedFiles[activeFileIndex]) return;

    const file = uploadedFiles[activeFileIndex];

    pdfjsLib.getDocument(file.pdfData).promise.then(pdf => {
        pdfDoc = pdf;
        totalPages = pdf.numPages;

        pdf.getPage(pageNumber).then(page => {
            const viewport = page.getViewport({ scale: 1.5 }); // Naikkan scale awal

            // Scale agar pas di area besar
            const containerWidth = pdfArea.clientWidth - 40;
            const scale = Math.min(
                containerWidth / viewport.width,
                1.8  // batas maksimal zoom
            );

            const scaledViewport = page.getViewport({ scale });

            pdfCanvas.width = scaledViewport.width;
            pdfCanvas.height = scaledViewport.height;

            ctx.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height);

            page.render({
                canvasContext: ctx,
                viewport: scaledViewport
            }).promise.then(() => {
                $('#pageInfo').text(`Page ${currentPage} of ${totalPages}`);
                renderSignaturesForPage(currentPage);
                updateSignerUIForCurrentFile();
            });
        });
    });
}
function renderSignaturesForPage(page) {
    // Hapus semua box lama
    document.querySelectorAll('.signature-box').forEach(box => box.remove());

    const file = uploadedFiles[activeFileIndex];
    if (!file) return;

    const placementType = document.querySelector('input[name="placementType"]:checked').value;

    if (placementType !== 'custom') return;

    const pageSignatures = file.signatures.filter(sig => sig.page === page);
    
    pageSignatures.forEach(sig => {
        const box = document.createElement('div');
        box.className = 'signature-box';
        box.dataset.signerId = sig.signer_id;
        box.dataset.signerName = sig.signer_name;
        box.dataset.page = sig.page;
        box.dataset.x = sig.x_percent;
        box.dataset.y = sig.y_percent;
        box.dataset.tier = sig.tier || 1;
        box.dataset.fileIndex = activeFileIndex;

        box.innerHTML = `
            <div class="delete-signature">×</div>
            <div class="signature-text">
                <span class="approved-by">Approved by</span>
                <span class="approver-name">${sig.signer_name}</span>
                <span class="at">at</span>
                <span class="datetime">${new Date().toLocaleString('id-ID', { 
                    day: '2-digit', month: 'short', year: 'numeric', 
                    hour: '2-digit', minute: '2-digit' 
                }).replace(',', '')}</span>
            </div>
            <div class="resize-handle"></div>
        `;

        const xPx = (parseFloat(sig.x_percent) / 100) * pdfCanvas.width;
        const yPx = (parseFloat(sig.y_percent) / 100) * pdfCanvas.height;

        positionSignatureBoxAtCanvas(box, xPx, yPx);
        makeSignatureBoxDraggable(box);
        makeResizable(box);
        
        pdfArea.appendChild(box);
    });
}

// ================= MAKE RESIZABLE =================
function makeResizable(box) {
    const handle = box.querySelector('.resize-handle');
    if (!handle) return;

    let isResizing = false;

    handle.addEventListener('pointerdown', (e) => {
        e.stopPropagation();
        isResizing = true;
        document.body.style.cursor = 'nwse-resize';
    });

    document.addEventListener('pointermove', (e) => {
        if (!isResizing) return;

        const rect = box.getBoundingClientRect();
        
        let newWidth = e.clientX - rect.left;
        newWidth = Math.max(220, Math.min(newWidth, 420)); // batas ukuran

        box.style.width = newWidth + 'px';
    });

    document.addEventListener('pointerup', () => {
        isResizing = false;
        document.body.style.cursor = 'default';
    });
}
// ================= UPDATE SIGNER UI - FIXED =================
function updateSignerUIForCurrentFile() {
    const currentFile = uploadedFiles[activeFileIndex];
    if (!currentFile) return;

    document.querySelectorAll('#dynamicSignerList .signer-item').forEach(item => {
        const signerId = item.dataset.signerId;
        const isUsedOnCurrentPage = currentFile.signatures.some(
            sig => sig.signer_id == signerId && sig.page === currentPage
        );

        if (isUsedOnCurrentPage) {
            item.classList.add('disabled');
            item.style.opacity = '0.5';
            item.style.pointerEvents = 'none';
        } else {
            item.classList.remove('disabled');
            item.style.opacity = '1';
            item.style.pointerEvents = 'auto';
        }
    });
}

function resetAllSignerUI() {
    // Reset semua file usedSigners
    uploadedFiles.forEach(file => {
        file.usedSigners.clear();
    });
    
    // Reset UI
    document.querySelectorAll('.signer-item').forEach(item => {
        item.style.opacity = '1';
        item.style.pointerEvents = 'auto';
        item.classList.remove('disabled');
    });
}

    // Page Navigation
// Page Navigation - Re-render signatures
// Page Navigation
document.getElementById('prevPage').addEventListener('click', function () {
    if (currentPage <= 1) return;
    currentPage--;
    console.log('Switching to page:', currentPage);
    renderPDF(currentPage);
});

document.getElementById('nextPage').addEventListener('click', function () {
    if (currentPage >= totalPages) return;
    currentPage++;
    console.log('Switching to page:', currentPage);
    renderPDF(currentPage);
});

    // ================= DATA COLLECTION =================
    // ================= CARI DAN GANTI collectSignatureData() =================

function collectSignatureData() {
    const step2Data = collectStep2Data();
    
    const showOnDocApprovers = [];
    
    // Kumpulkan semua approver yang show_on_document = true
    step2Data.approvers.forEach(tierData => {
        tierData.approvers.forEach(approver => {
            if (approver.show_on_document) {
                showOnDocApprovers.push({
                    user_id: approver.user_id,
                    division_id: approver.division_id,
                    name: approver.name,
                    tier: tierData.tier,
                    tier_division_id: tierData.division_id
                });
            }
        });
    });
    
    return uploadedFiles.map(file => {
        const fileData = {
            file_name: file.name,
            total_pages: file.totalPages,
            signatures: []
        };
        
        const type = document.querySelector('input[name="placementType"]:checked').value;

        if(type === 'custom') {
    fileData.signatures = file.signatures.map(sig => ({
        approver_id: sig.signer_id,
        page_number: sig.page,
        pos_x_percent: parseFloat(sig.x_percent.toFixed(2)),
        pos_y_percent: parseFloat((sig.y_percent).toFixed(2)),
        tier: sig.tier,
        mode: 'custom' 
    }));
} 
       // ================= CARI DAN GANTI bagian fixed di collectSignatureData() =================

else if(type === 'standard') {
    showOnDocApprovers.forEach((approver, index) => {
        const yPos = 90 - (index * 2);

        for(let p = 1; p <= file.totalPages; p++) {
            fileData.signatures.push({
                approver_id: approver.user_id,
                division_id: approver.division_id,
                tier: approver.tier,
                page_number: p,
                pos_x_percent: 2,
                pos_y_percent: yPos,
                mode: 'standard'
            });
        }
    });
}
else if(type === 'fixed') {
    const lastPage = file.totalPages;
    
    // Konfigurasi offset - sama seperti applyStandardFixedSignatures
    const startY = 90;
    const stepY = 2;

    showOnDocApprovers.forEach((approver, index) => {
        let yPos = startY - (index * stepY);

        fileData.signatures.push({
            approver_id: approver.user_id,
            division_id: approver.division_id,
            tier: approver.tier,
            page_number: lastPage,
            pos_x_percent: 2,
            pos_y_percent: yPos,
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

    $('.tier-box').each(function() {
        const tier = parseInt($(this).data('tier'));
        const tierApprovers = [];
        
        $(this).find('.approver-row').each(function() {
            const approverSelect = $(this).find('.approver-select');
            const showOnDoc = $(this).find('.show-on-doc');
            const isRequester = $(this).hasClass('requester-row');
            
            const approverId = approverSelect.val();
            const selectedOption = approverSelect.find('option:selected');
            
            if (approverId) {
                tierApprovers.push({
                    user_id: approverId,
                    name: selectedOption.text().trim() || 'Unknown',
                    division_id: selectedOption.data('division') || $(this).data('division') || '',
                    show_on_document: showOnDoc.is(':checked'),
                    is_requester: isRequester,
                    status: isRequester ? 'APPROVED' : 'PENDING'
                });
            }
        });
        
        if (tierApprovers.length > 0) {
            approvers.push({
                tier: tier,
                division_id: $(this).find('.approvers-list').data('division-id') || '',
                sla_days: parseInt($(this).data('sla-days') || 0),
                approvers: tierApprovers
            });
        }
    });

    // CC tetap sama...
    const ccUsers = [];
    $('.cc-row').each(function() {
        const ccSelect = $(this).find('.ccDropdown');
        const ccUserId = ccSelect.val();
        if (ccUserId) {
            const selected = ccSelect.find('option:selected');
            ccUsers.push({
                user_id: ccUserId,
                division_id: selected.data('division') || '',
                name: selected.text().trim() || ''
            });
        }
    });
    
    return {
        approvers: approvers,
        cc_users: ccUsers
    };
}

    // ================= PLACEMENT MODE =================
// ================= CARI DAN GANTI BAGIAN INI =================

// Placement type change handler
document.querySelectorAll('input[name="placementType"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const type = this.value;

        // Clear visual boxes
        document.querySelectorAll('.signature-box').forEach(b => b.remove());
        
        if (type === 'custom') {
            // 🔥 CUSTOM MODE: Reset signatures DAN usedSigners!
            uploadedFiles.forEach(file => {
                file.signatures = [];     // Reset signatures
                file.usedSigners.clear(); // ✅ INI YANG PERLU DITAMBAHKAN!
            });
            
            // ✅ Enable signer panel
            enableSignerPanel();
        } 
        else if (type === 'standard' || type === 'fixed') {
            // 🔥 STANDARD/FIXED: Generate auto signatures
            disableSignerPanel();
            applyStandardFixedSignatures(type);
        }

        if (pdfDoc && totalPages > 0) {
            renderPDF(currentPage);
        }
    });
});

// Tambahkan fungsi enableSignerPanel()
function enableSignerPanel() {
    document.querySelectorAll('.signer-item').forEach(item => {
        const signerId = item.dataset.signerId;
        const isUsedAnywhere = uploadedFiles.some(file => 
            file.usedSigners.has(signerId)
        );
        
        if (!isUsedAnywhere) {
            item.classList.remove('disabled');
            item.style.opacity = '1';
            item.style.pointerEvents = 'auto';
        }
    });
}
// ================= CARI DAN GANTI FUNGSI INI =================

// ================= CARI DAN GANTI di applyStandardFixedSignatures =================

function applyStandardFixedSignatures(mode) {
    const step2Data = collectStep2Data();
    const showOnDocApprovers = [];
    
    step2Data.approvers.forEach(tierData => {
        tierData.approvers.forEach(approver => {
            if (approver.show_on_document) {
                showOnDocApprovers.push({
                    id: approver.user_id,
                    name: approver.name,
                    tier: tierData.tier,
                    division_id: approver.division_id
                });
            }
        });
    });

    uploadedFiles.forEach(file => {
        file.signatures = [];
        file.usedSigners.clear();

        if (mode === 'standard') {
            // STANDARD: Semua halaman
            for (let p = 1; p <= file.totalPages; p++) {
                showOnDocApprovers.forEach(approver => {
                    file.signatures.push({
                        signer_id: approver.id,
                        signer_name: approver.name,
                        tier: approver.tier,
                        page: p,
                        x_percent: 70,      // Ubah dari 85 → 70
                        y_percent: 90,
                        mode: 'standard'
                    });
                    file.usedSigners.add(approver.id);
                });
            }
        } 
        else if (mode === 'fixed') {
            const lastPage = file.totalPages;
            
            // =================================================
            // VERTIKAL STACKING - Mulai dari tengah-kiri
            // =================================================
           const baseX = 78;   // lebih ke kanan   // Ubah dari 85 → 70 (30% dari kanan)
            const startY = 90;        // 10% dari bawah
            const stepY = 12;         // jarak vertikal antar signature
            const stepX = 15;        // jarak horizontal jika sudah penegak
            
            showOnDocApprovers.forEach((approver, index) => {
                let xPos = baseX;
                let yPos = startY - (index * stepY);
                
                // Kalo sudah melampaui batas bawah (yPos < 20), geser kiri dan mulai dari atas lagi
                if (yPos < 20) {
                    xPos = baseX - (Math.floor(index / 6) * stepX);
                    yPos = 90 - ((index % 6) * stepY);
                }

                file.signatures.push({
                    signer_id: approver.id,
                    signer_name: approver.name,
                    tier: approver.tier,
                    page: lastPage,
                    x_percent: xPos,
                    y_percent: yPos,
                    mode: 'fixed'
                });
                file.usedSigners.add(approver.id);
            });
        }
    });

    updateSignerUIForCurrentFile();
}
    function disableSignerPanel() {
        document.querySelectorAll('.signer-item').forEach(item => {
            item.style.opacity = 0.5;
            item.style.pointerEvents = 'none';
            item.classList.add('disabled');
        });
    }

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
                updateUploadAreaState();
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
        
        document.querySelectorAll('.signature-box').forEach(b => b.remove());
        
        renderFileTabs();
        renderPDF();
        
        // ✅ ADD THIS: Refresh signer UI for new file
        updateSignerUIForCurrentFile();
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

    // ================= COLLECT COMPLETE PAYLOAD FOR BACKEND =================
    // ================= COLLECT COMPLETE PAYLOAD FOR BACKEND =================
function collectCompletePayload() {
    const step1 = collectStep1Data();
    const step2 = collectStep2Data();
    const signatureData = collectSignatureData();

    const payload = {
        document: {
            organization_id: step1.organization_id,
            folder_id: step1.folder_id,
            requester_division_id: step1.division_id,
            workflow_id: parseInt(step1.document_type_id),
            status: 'WAITING APPROVAL'
        },
        files: uploadedFiles.map(f => ({
            name: f.name,
            size_mb: (f.pdfBlob.size / 1024 / 1024).toFixed(2)
        })),
        document_approvals: [], 
        file_positions: [],
        document_shares: step2.cc_users.map(cc => ({
            share_to: cc.user_id
        })),
        email_subject: document.getElementById('emailSubject').value.trim(),
        email_message: document.getElementById('emailMessage').value.trim(),
        placement_type: document.querySelector('input[name="placementType"]:checked')?.value || 'custom'
    };

    // ================= BUAT APPROVAL TEMPLATE =================
    let approvalIdCounter = 1;
    const approvalTemplate = [];

    step2.approvers.forEach(tierData => {
        tierData.approvers.forEach((approver, idx) => {
            const isRequester = approver.is_requester === true;

            approvalTemplate.push({
                temp_id: approvalIdCounter++,
                division_id: approver.division_id || tierData.division_id,
                approver_id: approver.user_id,
                approver_order: idx + 1,
                show_on_doc: approver.show_on_document,
                status: isRequester ? 'APPROVED' : 'PENDING',
                tier: tierData.tier,
                workflow_step_id: parseInt(step1.document_type_id),
                sla_days: tierData.sla_days || 0,
                is_requester: isRequester   // ← INI YANG DITAMBAHKAN
            });
        });
    });

    payload.document_approvals = approvalTemplate;

    // Group positions per file
    payload.file_positions = signatureData.map(fileData => ({
        file_name: fileData.file_name,
        signatures: fileData.signatures.map(sig => {
            const approverIndex = approvalTemplate.findIndex(a => 
                parseInt(a.approver_id) === parseInt(sig.approver_id)
            );
            return {
                approver_temp_id: approverIndex !== -1 ? approvalTemplate[approverIndex].temp_id : null,
                page_number: sig.page_number,
                pos_x_percent: parseFloat(sig.pos_x_percent || 0),
                pos_y_percent: parseFloat(sig.pos_y_percent || 0),
                mode: sig.mode
            };
        }).filter(sig => sig.approver_temp_id !== null)
    }));

    console.log('🔍 Approval Template (dengan is_requester):', approvalTemplate);
    return payload;
}
    // Di dalam $(document).ready() atau di akhir script
    document.getElementById('btnSendDocument').addEventListener('click', function () {
        const subject = document.getElementById('emailSubject').value.trim();
        const message = document.getElementById('emailMessage').value.trim();

        if (!subject || !message) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Failed',
                text: 'Email Subject and Email Message are required!',
                confirmButtonText: 'OK'
            });
            return;
        }

        let payload = collectCompletePayload();
        payload.email_subject = subject;
        payload.email_message = message;
        payload._token = $('meta[name="csrf-token"]').attr('content');

        const formData = new FormData();
        
        uploadedFiles.forEach((file, index) => {
            formData.append(`files[]`, file.pdfBlob, file.name);
        });
        
        formData.append('payload', JSON.stringify(payload));

        // 🔥🔥 CONSOLE LOG SEMUA DATA YANG DIKIRIM 🔥🔥
        console.log('🚀 === COMPLETE DATA TO BACKEND ===');
        
        // 1. RAW PAYLOAD JSON
        console.log('📋 PAYLOAD JSON:', payload);
        console.log('📋 PAYLOAD STRINGIFIED:', JSON.stringify(payload, null, 2));
        
        // 2. FILES INFO
        console.log('\n📁 UPLOADED FILES:');
        uploadedFiles.forEach((file, index) => {
            console.log(`  ${index + 1}. ${file.name}`);
            console.log(`     Size: ${(file.pdfBlob.size / 1024 / 1024).toFixed(2)} MB`);
            console.log(`     Pages: ${file.totalPages}`);
            console.log(`     Signatures: ${file.signatures.length}`);
            console.log(`     Used Signers:`, Array.from(file.usedSigners));
        });
        
        // 3. FORM DATA ENTRIES (semua yang dikirim)
        console.log('\n📦 FORM DATA ENTRIES:');
        for (let pair of formData.entries()) {
            if (pair[0] === 'payload') {
                console.log(`${pair[0]}:`, JSON.parse(pair[1])); // Parse payload untuk readability
            } else {
                console.log(`${pair[0]}: ${pair[1].name || pair[1].size} bytes`);
            }
        }
        
        // 4. DETAIL APPROVALS & POSITIONS
        console.log('\n👥 DOCUMENT APPROVALS:', payload.document_approvals);
        console.log('✍️ APPROVAL POSITIONS:', payload.approval_positions);
        console.log('👥 CC/SHARES:', payload.document_shares);
        
        // 5. STEP DATA RAW
        console.log('\n📊 STEP 1 DATA:', step1Data);
        console.log('📊 STEP 2 DATA:', step1Data.approvers_data);
        console.log('📊 SIGNATURE DATA:', step1Data.signature_data);
        
        console.log('\n🎯 PLACEMENT TYPE:', payload.placement_type);
        console.log('📧 EMAIL SUBJECT:', payload.email_subject);
        console.log('📧 EMAIL MESSAGE:', payload.email_message.substring(0, 100) + '...');
        
        console.log('✅ === END DATA LOG ===');

        // Sisanya AJAX call sama...
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Sending...`;

        $.ajax({
            url: '/documents/store',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        let percent = (evt.loaded / evt.total) * 100;
                        console.log(`📤 Upload: ${percent.toFixed(1)}%`);
                    }
                });
                return xhr;
            },
            
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/upload';
                });
            },
            
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    html: xhr.responseJSON?.message || 'An error occurred',
                });
            },
            
            complete: function() {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
        });
    });
</script>

@endpush
@push('styles')
<style>
    /* ================= PDF AREA - SUBTLE BORDER ================= */
    .pdf-area {
        position: relative;
        width: 100%;
        min-height: 700px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 2px;
        /* Subtle border aja - tidak tebal */
        border: 1px solid #e0e4f0;
        border-radius: 8px;
        background: #000;
    }

    .signature-box .resize-handle {
    position: absolute;
    bottom: -6px;
    right: -6px;
    width: 14px;
    height: 14px;
    background: white;
    border: 2px solid #1a5c1a;
    border-radius: 50%;
    cursor: nwse-resize;
    z-index: 101;
}

    #pdfCanvas {
        display: block;
        max-width: 100%;
        height: auto;
    }

  .signature-box {
    position: absolute;
    padding: 8px 14px;
    background: linear-gradient(135deg, #1a5c1a 0%, #0d3d0d 100%);
    color: white;
    font-size: 11px;
    line-height: 1.4;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 6px;
    cursor: move;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
    z-index: 100;
    white-space: nowrap;
    min-width: 240px;           /* dikecilkan sedikit */
    max-width: 380px;           /* biar tidak terlalu lebar */
    text-align: left;
    user-select: none;
}

/* Buat lebih compact & mudah diatur posisi kanan */
.signature-box .signature-text {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: nowrap;
}

.signature-box .approved-by,
.signature-box .at {
    font-size: 9.5px;
    opacity: 0.9;
}

.signature-box .approver-name {
    font-weight: 700;
    font-size: 11.8px;
}

.signature-box .datetime {
    font-size: 9.8px;
    color: #a8d8a8;
    font-family: 'Courier New', monospace;
}

/* Delete button */
.signature-box .delete-signature {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    width: 20px;
    height: 20px;
    font-size: 14px;
    line-height: 18px;
    border-radius: 50%;
    text-align: center;
    cursor: pointer;
    border: 2px solid white;
}
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .signature-box {
            min-width: 220px;
            padding: 4px 10px;
            font-size: 10px;
        }
        
        .signature-text .approver-name {
            font-size: 10px;
        }
        
        .signature-text .datetime {
            font-size: 9px;
        }
    }

    /* Signer Item Panel */
    .signer-item {
        transition: all 0.2s ease;
        cursor: grab;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .signer-item:hover {
        background: #f0f4ff;
        border-left: 3px solid #4e73df;
    }

    .signer-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ================= LAINNYA ================= */
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

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
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
    }

    .tier-box {
        background: linear-gradient(145deg, #f8f9ff, #ffffff);
        border: 1px solid #e0e4f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 1.5rem;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #d1d5e0;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    @media (max-width: 992px) {
        .pdf-area {
            min-height: 500px;
            padding: 10px;
        }
    }
</style>
@endpush