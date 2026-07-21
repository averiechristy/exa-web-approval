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
    
     Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    Route::get('/audit-trail/{id}', [AuditTrailController::class, 'show'])->name('audit-trail.show');
    Route::resource('organization', OrganizationController::class);
    Route::resource('division', DivisionController::class);
    Route::resource('role', RoleController::class);
    Route::resource('user', UserController::class);
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.resetPassword');
    Route::post('/user/{user}/inactive', [UserController::class, 'inactive'])->name('user.inactive');

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
    Route::get('/inbox/documents/{document}/preview', [InboxController::class, 'preview'])->name('inbox.preview');
    Route::post('/documents/{document}/approve', [InboxController::class, 'approve'])->name('document.approve');
    Route::post('/documents/{document}/reject', [InboxController::class, 'reject'])->name('document.reject');
    Route::post('/inbox/bulk-approve', [InboxController::class, 'bulkApprove'])->name('inbox.bulkApprove');
    Route::get('/inbox/documents/{document}/download', [InboxController::class, 'download'])->name('inbox.download');
    Route::post('/inbox/bulk-export', [InboxController::class, 'bulkExport'])->name('inbox.bulkExport');
    Route::post('/documents/move-folder', [InboxController::class, 'moveFolder'])->name('documents.move-folder');
    Route::post('/documents/share', [InboxController::class, 'share'])->name('documents.share');

    //Shared
    Route::get('/shared', [SharedController::class, 'index'])->name('shared.index');
    Route::get('shared/{folder}', [SharedController::class, 'showFolder'])->name('shared.show');
    Route::get('/shared/documents/{document}/download', [SharedController::class, 'download'])->name('shared.download');
    Route::post('/shared/bulk-export', [SharedController::class, 'bulkExport'])->name('shared.bulkExport');
        Route::get('/shared/documents/{document}/preview', [SharedController::class, 'preview'])->name('shared.preview');


    //Sent
    Route::get('/sent', [SentController::class, 'index'])->name('sent.index');
        Route::get('/sent/documents/{document}/download', [SentController::class, 'download'])->name('sent.download');
    Route::post('/sent/bulk-export', [SentController::class, 'bulkExport'])->name('sent.bulkExport');
        Route::get('/sent/documents/{document}/preview', [SentController::class, 'preview'])->name('sent.preview');


    //MyDoc
    Route::get('/mydoc', [MyDocumentController::class, 'index'])->name('mydoc.index');
    Route::get('/mydoc/{folder}', [MyDocumentController::class, 'showFolder'])->name('mydoc.show');
    Route::get('/mydoc/documents/{document}/preview', [MyDocumentController::class, 'preview'])->name('mydoc.preview');
    Route::get('/mydoc/documents/{document}/download', [MyDocumentController::class, 'download'])->name('mydoc.download');
    Route::post('/mydoc/bulk-export', [MyDocumentController::class, 'bulkExport'])->name('mydoc.bulkExport');


    Route::get('/dashboard/sla', [SlaDashboardController::class, 'index'])->name('dashboard.sla');


});

