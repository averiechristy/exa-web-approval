<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\FolderController;
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

    Route::get('/users/approvers', [UploadController::class, 'getApprovers'])->name('users.approvers');
    Route::get('/users/carboncopy', [UploadController::class, 'getCC'])->name('users.carboncopy');
    
});