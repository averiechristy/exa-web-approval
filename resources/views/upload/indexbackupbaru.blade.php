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
                    <h5 class="mb-1 text-primary">Upload Documrnt</h5>
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
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-user-check mr-2"></i>Approvers (Show on Doc)
                    </h6>
                    <div class="list-group mb-3" id="dynamicSignerList">
                        <!-- Diisi dinamis dari Step 2 data -->
                        <div class="list-group-item text-muted small p-3 text-center">
                            <i class="fas fa-info-circle"></i> Complete Step 2 first
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>Drag approvers to PDF
                    </small>
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
        
        // Kumpulkan semua approver yang "Show on document"
        step2Data.approvers.forEach(tier => {
            tier.approvers.forEach(approver => {
                if (approver.show_on_document) {
                    // Ambil nama dari option text atau fallback
                    const tierBox = $(`.tier-box[data-tier="${tier.tier}"]`);
                    const select = tierBox.find('.approver-select').filter(function() {
                        return $(this).val() == approver.user_id;
                    });
                    
                    let approverName = 'Unknown User';
                    if (select.length > 0) {
                        approverName = select.find('option:selected').text() || select.find(`option[value="${approver.user_id}"]`).text();
                    }
                    
                    showOnDocSigners.push({
                        id: approver.user_id,
                        name: approverName,
                        tier: tier.tier,
                        division_id: approver.division_id
                    });
                }
            });
        });
        
        // Render signer list
        const signerList = document.getElementById('dynamicSignerList');
        if (showOnDocSigners.length === 0) {
            signerList.innerHTML = `
                <div class="list-group-item text-muted small p-3 text-center">
                    <i class="fas fa-info-circle"></i> No approvers selected for document
                </div>
            `;
            return [];
        }
        
        signerList.innerHTML = '';
        showOnDocSigners.forEach(signer => {
            const item = document.createElement('div');
            item.className = 'list-group-item signer-item px-3 py-2';
            item.draggable = true;
            item.dataset.signerId = signer.id;
            item.dataset.signerName = signer.name;
            item.dataset.tier = signer.tier;
            item.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-user text-primary mr-2"></i>
                    <div>
                        <div class="font-weight-medium" style="font-size: 13px;">${signer.name}</div>
                        <small class="text-muted">Tier ${signer.tier}</small>
                    </div>
                    <i class="fas fa-grip-vertical text-muted ml-auto cursor-grab" style="font-size: 12px;"></i>
                </div>
            `;
            signerList.appendChild(item);
        });
        
        // Attach drag events
        document.querySelectorAll('#dynamicSignerList .signer-item').forEach(item => {
            item.addEventListener('dragstart', handleSignerDragStart);
        });
        
        return showOnDocSigners;
    }

    // Drag handler untuk dynamic signers
    function handleSignerDragStart(e) {
        const signerId = this.dataset.signerId;
        const currentFile = uploadedFiles[activeFileIndex];

        // Hanya cek file saat ini (bukan semua file)
        if (!currentFile || currentFile.usedSigners.has(signerId)) {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Sudah Digunakan',
                text: 'Approver ini sudah ditempatkan di file ini.',
                timer: 1500
            });
            return false;
        }

        draggedSigner = {
            id: signerId,
            name: this.dataset.signerName,
            tier: this.dataset.tier
        };

        this.style.opacity = '0.5';
    }

// ================= UPDATED: Per File Disable Logic =================
// ================= UPDATE SIGNER UI (PERBAIKAN) =================
// ✅ FIXED: Check per-file, not globally
function updateSignerUIForCurrentFile() {
    const currentFile = uploadedFiles[activeFileIndex];
    
    document.querySelectorAll('.signer-item').forEach(item => {
        const signerId = item.dataset.signerId;
        
        // Only disable if used in THIS SPECIFIC FILE
        const isUsedInThisFile = currentFile && currentFile.usedSigners.has(signerId);

        if (isUsedInThisFile) {
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
                $('#tierContainer').html('<p class="text-muted">Loading approver list based on workflow...</p>');
            },
            success: function(response) {
                $('#tierContainer').empty();

                if (!response.workflow_steps || response.workflow_steps.length === 0) {
                    $('#tierContainer').html('<p class="text-warning">No approvers available.</p>');
                    return;
                }

                response.workflow_steps.forEach(group => {
                    let tierHtml = `
                    <div class="tier-box border rounded p-3 mb-4" data-tier="${group.tier}">
                        <h6 class="text-primary mb-3">
                            ${group.title || 'Tier ' + group.tier} • ${group.division_name}
                            ${group.sla_days > 0 ? `<small class="text-muted">(${group.sla_days} days SLA)</small>` : ''}
                        </h6>
                        <div class="approvers-list" data-tier="${group.tier}" data-division-id="${group.division_id || ''}"></div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 add-approver-per-tier"
                                data-tier="${group.tier}" data-division-id="${group.division_id || ''}">
                            <i class="fas fa-plus"></i> Add Approver
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
                $('#tierContainer').html('<p class="text-danger">Failed to load approvers. Please try again.</p>');
            }
        });
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

    pdfArea.addEventListener('dragover', function (e) {
        e.preventDefault();
    });

    pdfArea.addEventListener('drop', function (e) {
    e.preventDefault();
    if (!draggedSigner || !draggedSigner.id) return;

    const currentFile = uploadedFiles[activeFileIndex];
    if (!currentFile.usedSigners.has(draggedSigner.id)) { // Perbaiki kondisi
        const canvasRect = pdfCanvas.getBoundingClientRect();
        const xPx = e.clientX - canvasRect.left;
        const yPx = e.clientY - canvasRect.top;
        const xPercent = xPx / canvasRect.width;
        const yPercent = yPx / canvasRect.height;

        // 🔥 SIMPAN signature KE FILE AKTIF
        const signatureData = {
            signer_id: draggedSigner.id,
            signer_name: draggedSigner.name,
            tier: parseInt(draggedSigner.tier || 1),
            page: currentPage,
            x_percent: xPercent,
            y_percent: yPercent
        };

        currentFile.signatures.push(signatureData);
        currentFile.usedSigners.add(draggedSigner.id);
        updateSignerUIForCurrentFile();
        
        // Buat visual box
        const box = document.createElement('div');
        box.classList.add('signature-box');
        box.dataset.signerId = draggedSigner.id;
        box.dataset.signerName = draggedSigner.name;
        box.dataset.page = currentPage;
        box.dataset.x = xPercent;
        box.dataset.y = yPercent;
        box.dataset.tier = draggedSigner.tier || 1;
        box.dataset.fileIndex = activeFileIndex; // 🔥 Track file

        box.innerHTML = `
            <div class="delete-signature">&times;</div>
            <div class="signer-info">
                <span class="signer-name">${draggedSigner.name}</span><br>
                <small class="tier-info">Tier ${box.dataset.tier} • Signature</small>
            </div>
        `;

        positionSignatureBox(box);
        makeDraggable(box);
        pdfArea.appendChild(box);

        currentFile.usedSigners.add(draggedSigner.id);
        updateSignerUIForCurrentFile();
        
        // Update UI global
        // disableSignerUI(draggedSigner.id);
        draggedSigner = null;
    }
});

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

        // Hapus box dari DOM
        box.remove();

        // Hapus dari data file terkait
        const file = uploadedFiles[fileIndex];
        if (file) {
            // Hapus signature dari array
            file.signatures = file.signatures.filter(sig => 
                !(sig.signer_id == signerId && sig.page == page)
            );

            // Cek apakah signer masih digunakan di **semua file**
            const isStillUsedAnywhere = uploadedFiles.some((f, idx) => {
                return f.signatures.some(sig => sig.signer_id == signerId);
            });

            // Jika tidak digunakan di mana pun → enable kembali
            if (!isStillUsedAnywhere) {
                file.usedSigners.delete(signerId); // bersihkan juga di file ini
                updateSignerUIForCurrentFile();   // refresh UI
            }
        }

        // Re-render signatures di halaman saat ini
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

        const xPercent = x / canvasRect.width;
        const yPercent = y / canvasRect.height;

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
        const x = box.dataset.x * canvasRect.width;
        const y = box.dataset.y * canvasRect.height;
        box.style.left = x + 'px';
        box.style.top = y + 'px';
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
                // 🔥 AUTO RENDER SIGNATURES SETELAH PDF RENDER
                renderSignaturesForPage(currentPage);
            });
        });
    });
}

function renderSignaturesForPage(page) {
    // Hapus SEMUA signature box lama (global)
    document.querySelectorAll('.signature-box').forEach(box => box.remove());

    const file = uploadedFiles[activeFileIndex];
    if (!file) return;

    const placementType = document.querySelector('input[name="placementType"]:checked').value;

    // 🔥 IMPORTANT: HANYA render visual box untuk CUSTOM mode
    if (placementType === 'custom') {
        file.signatures.forEach(sig => {
            if (sig.page === page) {
                const box = document.createElement('div');
                box.classList.add('signature-box');
                box.dataset.signerId = sig.signer_id;
                box.dataset.signerName = sig.signer_name;
                box.dataset.page = sig.page;
                box.dataset.x = sig.x_percent;
                box.dataset.y = sig.y_percent;
                box.dataset.tier = sig.tier || 1;
                box.dataset.fileIndex = activeFileIndex;

                box.innerHTML = `
                    <div class="delete-signature">&times;</div>
                    <div class="signer-info">
                        <span class="signer-name">${sig.signer_name}</span><br>
                        <small class="tier-info">Tier ${sig.tier} • Signature</small>
                    </div>
                `;

                positionSignatureBox(box);
                makeDraggable(box);
                pdfArea.appendChild(box);
                updateSignerUIForCurrentFile();
            }
        });
    }
    // Untuk standard/fixed: TIDAK render visual box sama sekali
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
                // Custom: gunakan yang sudah di-drag
                // Di dalam collectSignatureData(), bagian custom:
                fileData.signatures = file.signatures.map(sig => ({
                    approver_id: sig.signer_id,
                    page_number: sig.page,
                    pos_x_percent: parseFloat(sig.x_percent.toFixed(2)),
                    pos_y_percent: parseFloat(sig.y_percent.toFixed(2)),
                    tier: sig.tier
                }));
            } 
            else if(type === 'standard') {
                showOnDocApprovers.forEach(approver => {
                    for(let p = 1; p <= file.totalPages; p++) {
                        fileData.signatures.push({
                            approver_id: approver.user_id,
                            division_id: approver.division_id,
                            tier: approver.tier,
                            page_number: p,
                            pos_x_percent: 0.85,  // Bottom Right
                            pos_y_percent: 0.90,
                            mode: 'standard'
                        });
                    }
                });
            } 
            else if(type === 'fixed') {
                const summaryPage = file.totalPages; // Tambah halaman summary
                showOnDocApprovers.forEach((approver, index) => {
                    const positions = [
                        {x: 0.15, y: 0.20}, // Top Left
                        {x: 0.55, y: 0.20}, // Top Right  
                        {x: 0.15, y: 0.50}, // Middle Left
                        {x: 0.55, y: 0.50}  // Middle Right
                    ];
                    
                    const pos = positions[index % positions.length];
                    fileData.signatures.push({
                        approver_id: approver.user_id,
                        division_id: approver.division_id,
                        tier: approver.tier,
                        page_number: summaryPage,
                        pos_x_percent: pos.x,
                        pos_y_percent: pos.y,
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
            const tier = $(this).data('tier');
            const tierApprovers = [];
            
            $(this).find('.approver-row').each(function() {
                const approverSelect = $(this).find('.approver-select');
                const showOnDoc = $(this).find('.show-on-doc');
                
                const approverId = approverSelect.val();
                const selectedOption = approverSelect.find('option:selected');
                
                if (approverId) {
                    tierApprovers.push({
                        user_id: approverId,
                        name: selectedOption.text().trim() || 'Unknown Approver',   // ← TAMBAHKAN INI
                        division_id: selectedOption.data('division') || $(this).data('division') || '',
                        show_on_document: showOnDoc.is(':checked')
                    });
                }
            });
            
            if (tierApprovers.length > 0) {
                approvers.push({
                    tier: parseInt(tier),
                    division_id: $('.approvers-list[data-tier="' + tier + '"]').data('division-id') || '',
                    approvers: tierApprovers
                });
            }
        });

        // CC tetap sama
        const ccUsers = [];
        $('.cc-row').each(function() {
            const ccSelect = $(this).find('.ccDropdown');
            const ccUserId = ccSelect.val();
            const selectedOption = ccSelect.find('option:selected');
            
            if (ccUserId) {
                ccUsers.push({
                    user_id: ccUserId,
                    division_id: selectedOption.data('division') || '',
                    name: selectedOption.text().trim() || ''
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

        // Clear visual boxes
        document.querySelectorAll('.signature-box').forEach(b => b.remove());
        
        if (type === 'custom') {
            // 🔥 CUSTOM MODE: Clear signatures (user drag manual), keep usedSigners
            uploadedFiles.forEach(file => {
                file.signatures = []; // Reset signatures untuk drag baru
                // usedSigners TIDAK diubah - tracking approver yang sudah dipakai
            });
            enableSignerPanel(); // Enable drag panel
        } 
        else if (type === 'standard' || type === 'fixed') {
            // 🔥 STANDARD/FIXED: Generate auto signatures, disable panel
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
            for (let p = 1; p <= file.totalPages; p++) {
                showOnDocApprovers.forEach(approver => {
                    file.signatures.push({
                        signer_id: approver.id,
                        signer_name: approver.name,
                        tier: approver.tier,
                        page: p,
                        x_percent: 0.85,
                        y_percent: 0.90,
                        mode: 'standard'
                    });
                    file.usedSigners.add(approver.id);
                });
            }
        } 
        else if (mode === 'fixed') {
            const lastPage = file.totalPages;
            showOnDocApprovers.forEach((approver, index) => {
                const positions = [
                    {x: 0.15, y: 0.20}, {x: 0.55, y: 0.20},
                    {x: 0.15, y: 0.50}, {x: 0.55, y: 0.50}
                ];
                const pos = positions[index % positions.length];

                file.signatures.push({
                    signer_id: approver.id,
                    signer_name: approver.name,
                    tier: approver.tier,
                    page: lastPage,
                    x_percent: pos.x,
                    y_percent: pos.y,
                    mode: 'fixed'
                });
                file.usedSigners.add(approver.id);
            });
        }
    });

    // ✅ HAPUS renderSignaturesForPage() - tidak perlu visual box
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
    function collectCompletePayload() {
        const step1 = collectStep1Data();
        const step2 = collectStep2Data();
        const signatureData = collectSignatureData();

        const payload = {
            document: {
                organization_id: step1.organization_id,
                folder_id: step1.folder_id,
                requester_division_id: step1.division_id,
                workflow_id: parseInt(step1.document_type_id),   // Penting!
                status: 'DRAFT'
            },
            files: uploadedFiles.map(f => ({
                name: f.name,
                size_mb: (f.pdfBlob.size / 1024 / 1024).toFixed(2)
            })),
            document_approvals: [], 
            file_positions: [],           // ← Perbaikan utama
            document_shares: step2.cc_users.map(cc => ({
                share_to: cc.user_id
            })),
            email_subject: document.getElementById('emailSubject').value.trim(),
            email_message: document.getElementById('emailMessage').value.trim(),
            placement_type: document.querySelector('input[name="placementType"]:checked')?.value || 'custom'
        };

        // Buat Approval Template
        let approvalIdCounter = 1;
        const approvalTemplate = [];

        step2.approvers.forEach(tierData => {
            tierData.approvers.forEach((approver, idx) => {
                approvalTemplate.push({
                    temp_id: approvalIdCounter++,
                    division_id: approver.division_id || tierData.division_id,
                    approver_id: approver.user_id,
                    approver_order: idx + 1,
                    show_on_doc: approver.show_on_document,
                    status: 'PENDING',
                    tier: tierData.tier,
                    workflow_step_id: parseInt(step1.document_type_id),
                    sla_days: tierData.sla_days || 0,
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
                    pos_y_percent: parseFloat(sig.pos_y_percent || 0)
                };
            }).filter(sig => sig.approver_temp_id !== null)
        }));

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

                /* === PERBAIKAN STEP 2 === */
        .tier-box {
            background: linear-gradient(145deg, #f8f9ff, #ffffff);
            border: 1px solid #e0e4f0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .tier-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(78, 115, 223, 0.12);
            border-color: #4e73df;
        }

        .tier-box h6 {
            border-bottom: 2px solid #f0f2f9;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .approver-row {
            background: #fff;
            border: 1px solid #e9ecf4;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .approver-row:hover {
            border-color: #4e73df;
            background: #f8fbff;
        }

        .cc-row {
            background: #fff;
            border: 1px solid #e9ecf4;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
        }

        .cc-row:hover {
            border-color: #4e73df;
        }

        /* Improve Select Styling */
        .form-control-lg, .form-control {
            border-radius: 10px;
            border: 1px solid #d1d5e0;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        /* Button Styling */
        .btn-outline-primary {
            border-radius: 50px;
            padding: 8px 20px;
        }

        /* Upload Area Enhancement */
        .upload-box-modern {
            transition: all 0.3s ease;
            user-select: none;
        }

        .upload-box-modern:hover {
            background: #f0f4ff !important;
            border-color: #2a5cff !important;
            transform: translateY(-4px);
        }

        .upload-box-modern.dragover {
            background: #e8f0ff !important;
            border-color: #2a5cff !important;
            box-shadow: 0 0 0 4px rgba(42, 92, 255, 0.2);
        }
        
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

.signature-box {
    padding: 10px 30px 10px 15px !important;
    background: linear-gradient(135deg, rgba(78,115,223,0.15), rgba(78,115,223,0.08)) !important;
    border: 2px dashed #4e73df !important;
    box-shadow: 0 4px 12px rgba(78,115,223,0.2);
}

.signature-box .signer-name {
    font-weight: 600;
    color: #4e73df;
    font-size: 13px;
}

.signature-box .tier-info {
    color: #858796;
    font-size: 11px;
}

.cursor-grab {
    cursor: grab;
}

.cursor-grab:active {
    cursor: grabbing;
}
    </style>
@endpush