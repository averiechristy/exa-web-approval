<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/', function () {
    return redirect('/login');
});

Route::post('/switch-context', [AuthController::class, 'switchContext'])
    ->middleware('auth');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth');
    
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::resource('organization', OrganizationController::class);
    Route::resource('division', DivisionController::class);
    Route::resource('role', RoleController::class);
    Route::resource('user', UserController::class);

    Route::get('/get-data/{id}', [UserController::class, 'getData']);
    Route::get('/get-managers', [UserController::class, 'getManagers']);

    Route::resource('workflow', WorkflowController::class);
    Route::resource('folders', FolderController::class);
    Route::get('/folders/by-org/{id}');

    Route::get('upload', [UploadController::class, 'index'])->name('upload.index');

    Route::get('/folders/by-organization/{orgId}', [UploadController::class, 'getByOrganization']);
    Route::get('/workflows/by-organization/{orgId}', [UploadController::class, 'getDocumentTypesByOrganization']);

    Route::get('/users/carboncopy', [UploadController::class, 'getCC'])->name('users.carboncopy');

    Route::get('/api/workflow-approvers/{workflow_id}', [UploadController::class, 'getWorkflowApprovers']);
    Route::post('/documents/store', [UploadController::class, 'store'])->name('documents.store');
    
// Inbox
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
Route::get('/inbox/{folder}', [InboxController::class, 'showFolder'])->name('inbox.show');
Route::get('/inbox/documents/{document}/preview', [InboxController::class, 'preview'])
    ->name('inbox.preview');

    Route::post('/documents/{document}/approve', [InboxController::class, 'approve'])
     ->name('document.approve');
     // ===== ROUTES UNTUK DOCUMENT =====

Route::prefix('documents')->name('documents.')->group(function () {

    Route::get('/{document}/view', [DocumentController::class, 'view'])
         ->name('view');

    Route::get('/{document}/download', [DocumentController::class, 'download'])
         ->name('download');

    // Optional: Share, Move, Void, dll
    Route::post('/{document}/share', [DocumentController::class, 'share'])->name('share');
});
});

