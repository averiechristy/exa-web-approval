<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\MyDocumentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SentController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\SlaDashboardController;
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

    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');
    
    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index')->middleware('superadmin');
    Route::get('/audit-trail/{id}', [AuditTrailController::class, 'show'])->name('audit-trail.show')->middleware('superadmin');
    Route::resource('organization', OrganizationController::class)->middleware('superadmin');
    Route::resource('division', DivisionController::class)->middleware('superadmin');
    Route::resource('role', RoleController::class)->middleware('superadmin');
    Route::resource('user', UserController::class)->middleware('superadmin');
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.resetPassword')->middleware('superadmin');
    Route::post('/user/{user}/inactive', [UserController::class, 'inactive'])->name('user.inactive')->middleware('superadmin');
    Route::post('/user/{user}/active', [UserController::class, 'active'])
    ->name('user.active')->middleware('superadmin');

    Route::get('/get-data/{id}', [UserController::class, 'getData'])->middleware('superadmin');
    Route::get('/get-managers', [UserController::class, 'getManagers'])->middleware('superadmin');

    Route::resource('workflow', WorkflowController::class)->middleware('superadmin');
    Route::resource('folders', FolderController::class)->middleware('superadmin');
    Route::get('/folders/by-org/{id}')->middleware('superadmin');

    Route::get('upload', [UploadController::class, 'index'])->name('upload.index');

    Route::get('/folders/by-organization/{orgId}', [UploadController::class, 'getByOrganization']);
    Route::get('/workflows/by-organization/{orgId}', [UploadController::class, 'getDocumentTypesByOrganization']);

    Route::get('/users/carboncopy', [UploadController::class, 'getCC'])->name('users.carboncopy');

    Route::get('/api/workflow-approvers/{workflow_id}', [UploadController::class, 'getWorkflowApprovers']);
    Route::post('/documents/store', [UploadController::class, 'store'])->name('documents.store');
    
    // Inbox
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index')->middleware('user');
    Route::get('/inbox/{folder}', [InboxController::class, 'showFolder'])->name('inbox.show')->middleware('user');
    Route::get('/inbox/documents/{document}/preview', [InboxController::class, 'preview'])->name('inbox.preview')->middleware('user');
    Route::post('/documents/{document}/approve', [InboxController::class, 'approve'])->name('document.approve')->middleware('user');
    Route::post('/documents/{document}/reject', [InboxController::class, 'reject'])->name('document.reject')->middleware('user');
    Route::post('/inbox/bulk-approve', [InboxController::class, 'bulkApprove'])->name('inbox.bulkApprove')->middleware('user');
    Route::get('/inbox/documents/{document}/download', [InboxController::class, 'download'])->name('inbox.download')->middleware('user');
    Route::post('/inbox/bulk-export', [InboxController::class, 'bulkExport'])->name('inbox.bulkExport')->middleware('user');
    Route::post('/documents/move-folder', [InboxController::class, 'moveFolder'])->name('documents.move-folder')->middleware('user');
    Route::post('/documents/share', [InboxController::class, 'share'])->name('documents.share')->middleware('user');

    //Shared
    Route::get('/shared', [SharedController::class, 'index'])->name('shared.index')->middleware('user');
    Route::get('shared/{folder}', [SharedController::class, 'showFolder'])->name('shared.show')->middleware('user');
    Route::get('/shared/documents/{document}/download', [SharedController::class, 'download'])->name('shared.download')->middleware('user');
    Route::post('/shared/bulk-export', [SharedController::class, 'bulkExport'])->name('shared.bulkExport')->middleware('user');
        Route::get('/shared/documents/{document}/preview', [SharedController::class, 'preview'])->name('shared.preview')->middleware('user');


    //Sent
    Route::get('/sent', [SentController::class, 'index'])->name('sent.index')->middleware('user');
    Route::post('/sent/documents/{document}/cancel', [SentController::class, 'cancel'])->name('sent.cancel')->middleware('user');
        Route::get('/sent/documents/{document}/download', [SentController::class, 'download'])->name('sent.download')->middleware('user');
    Route::post('/sent/bulk-export', [SentController::class, 'bulkExport'])->name('sent.bulkExport')->middleware('user');
        Route::get('/sent/documents/{document}/preview', [SentController::class, 'preview'])->name('sent.preview')->middleware('user');


    //MyDoc
    Route::get('/mydoc', [MyDocumentController::class, 'index'])->name('mydoc.index')->middleware('user');
    Route::get('/mydoc/{folder}', [MyDocumentController::class, 'showFolder'])->name('mydoc.show')->middleware('user');
    Route::get('/mydoc/documents/{document}/preview', [MyDocumentController::class, 'preview'])->name('mydoc.preview')->middleware('user');
    Route::get('/mydoc/documents/{document}/download', [MyDocumentController::class, 'download'])->name('mydoc.download')->middleware('user');
    Route::post('/mydoc/bulk-export', [MyDocumentController::class, 'bulkExport'])->name('mydoc.bulkExport')->middleware('user');


    Route::get('/dashboard/sla', [SlaDashboardController::class, 'index'])->name('dashboard.sla')->middleware('user');


});

