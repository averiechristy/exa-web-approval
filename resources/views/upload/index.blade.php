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

            <div class="alert alert-warning small">
                • Document must PDF <br>
                • Size max 50mb
            </div>

            <div class="form-group">
                <label>Upload File</label>
                <div class="upload-box" onclick="document.getElementById('pdfInput').click()">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                    <p>Click to Upload PDF</p>
                </div>
                <input type="file" id="pdfInput" accept="application/pdf" multiple hidden>
                <div id="fileList" class="mt-3"></div>
            </div>

            @if ($isSuperAdmin)
                <div class="form-group">
                    <label>Organization</label>
                    <select id="organizationSelect" class="form-control">
                    <option value="">-- Select Organization --</option>
                        @foreach($organizations as $org)
                            <option  value="{{ $org->id }}">{{ $org->organization_name }}</option>
                        @endforeach
                    </select>
                </div>

                 <div class="form-group">
                    <label>Requested Division</label>
                    <select id="divisionSelect" class="form-control">
                    <option value="">-- Select Division --</option>
                        @foreach($divisions as $div)
                            <option  value="{{ $div->id }}">{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(!$isSuperAdmin)
                <input type="hidden" id="organizationSelectHidden" value="{{ session('active_organization_id') }}">
                <input type="hidden" id="divisionSelectHidden" value="{{ session('active_division_id', '') }}">
            @endif

            <div class="form-group">
                <label>Document Type</label>
                <select id="documentTypeSelect" class="form-control">
                    <option value="">-- Select Organization First --</option>
                </select>
            </div>

            <!-- <div class="form-group">
                <label>File Name</label>
                <input type="text" class="form-control">
            </div>
             -->
            <div class="form-group">
                <label>Folder</label>
                <select id="folderSelect" class="form-control">
                    @if(!$isSuperAdmin)
                        @foreach($folderOptions as $folder)
                            <option value="{{ $folder['id'] }}">
                                {{ $folder['name'] }}
                            </option>
                        @endforeach
                    @else
                        <option value="">-- Select Organization First --</option>
                    @endif
                </select>
            </div>

            <div class="text-right">
                <button class="btn btn-primary nextBtn">Next</button>
            </div>

        </div>
    </div>

    <!-- STEP 2 -->
    <div class="card shadow mb-4 step-content">
        <div class="card-body">
            <div class="alert alert-info small">
                • Approver harus user terdaftar di sistem <br>
                • Urutan approver otomatis sesuai urutan penambahan <br>
                • Penerima salinan akan menerima salinan dokumen setelah selesai
            </div>

            <!-- ================= SIGNER ================= -->
            <h6 class="font-weight-bold text-primary mb-3">Approver</h6>

            <div id="signerContainer">

                <div class="row align-items-center signer-row mb-3">
                    <div class="col-md-6">
                        <label>Nama User</label>
                        <!-- Di STEP 2 - Signer Container -->
                        <select class="form-control userDropdown" disabled>
                            <option value="">Loading users...</option>
                        </select>
                        <input type="hidden" name="signer_order[]" value="1">
                    </div>

            <div class="col-md-4 mt-4">
                        <div class="form-check">
                            <input class="form-check-input showInDoc" type="checkbox">
                            <label class="form-check-label">
                                Show on document
                            </label>
                        </div>
                    </div>

                    <!-- Delete -->
                    <div class="col-md-2 mt-4">
                        <button type="button" class="btn btn-danger removeSigner w-100">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="addSigner">
                <i class="fas fa-plus"></i> Add Approver
            </button>
        
            <hr>

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

            <div class="text-right">
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

                const data = collectStep1Data();
                console.log("STEP 1 DATA:", data);
                loadUsersForApprovers(orgId, docType, divId);
                loadCC(orgId, docType, divId);
            }

            if (currentStep === 2) {
                const signatureData = collectSignatureData();
                console.log("Payload yang dikirim ke backend:", signatureData);
            }

            if (currentStep < contents.length - 1) {
                currentStep++;
                updateStep();
            }
        });
    });

     function loadUsersForApprovers(organizationId, documentTypeId, divisionId) {
        $.ajax({
            url: '/users/approvers',
            type: 'GET',
            data: {
                organization_id: organizationId,
                document_type_id: documentTypeId,
                division_id: divisionId
            },
            beforeSend: function() {
                $('.userDropdown').html('<option>Loading users...</option>').prop('disabled', true);
            },
            success: function(response) {
                updateUserDropdowns(response.users);
            },
            error: function(xhr) {
                console.error('Error loading users:', xhr);
                updateUserDropdowns([]);
                alert('Gagal memuat daftar user approver');
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
        $('.ccDropdown').each(function() {
            let options = '<option value="">Pilih User</option>';
            
            users.forEach(function(user) {
                options += `
                    <option value="${user.id}" 
                            data-org="${user.organization_id}"
                            data-division="${user.division_id || ''}">
                        ${user.name}
                    </option>
                `;
            });
            
            $(this).html(options).prop('disabled', false);
        });
    }
    function updateUserDropdowns(users) {
        $('.userDropdown').each(function() {
            let options = '<option value="">Pilih User</option>';
            
            users.forEach(function(user) {
                options += `
                    <option value="${user.id}" 
                            data-org="${user.organization_id}"
                            data-division="${user.division_id || ''}">
                        ${user.name}
                    </option>
                `;
            });
            
            $(this).html(options).prop('disabled', false);
        });
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

    // ================= SIGNER ORDER UPDATE =================
    function updateSignerOrder() {
        document.querySelectorAll('#signerContainer .signer-row').forEach((row, index) => {
            row.querySelector('input[type="hidden"]').value = index + 1;
        });
    }

    // ================= JQUERY DOCUMENT READY - FILTER & CC =================
    $(document).ready(function() {
        // INIT
        $(".userDropdown").prop("disabled", true);

        // CC HANDLERS
        $("#addCC").click(function(){
            let row = $(".cc-row:first").clone();
            row.find("select").val("");
            $("#ccContainer").append(row);
        });

        $(document).on("click",".removeCC",function(){
            if($("#ccContainer .cc-row").length > 1){
                $(this).closest(".cc-row").remove();
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

        uploadedFiles.forEach((f) => {
            list.innerHTML += `
                <div class="border rounded p-2 mb-2">
                    ${f.name}
                </div>
            `;
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
    </style>
@endpush