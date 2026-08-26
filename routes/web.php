<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\FolderActionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\FolderAccessController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FolderController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ProjectController;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth');


Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'session.timeout'])->name('dashboard');


// Route::get('/folders/shared', function () {
//      return view('admin/folders/shared');
// })->middleware(['auth', 'verified','session.timeout'])->name('shared.folders');

// Route::get('/folders/favorite', function () {
//      return view('admin/folders/favorite');
// })->middleware(['auth', 'verified','session.timeout'])->name('favorite.folders');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Admin Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->group(function () {
    // Users CRUD
    Route::resource('users', AdminUserController::class);
    Route::post('/users/{id}/unlock', [AdminUserController::class, 'unlock'])->name('users.unlock');
    Route::post('/users/{id}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    Route::post('/users/bulk-delete', [AdminUserController::class, 'bulkDestroy'])->name('users.bulkDestroy');
    // Invitations
    Route::prefix('invitations')->name('invitations.')->group(function () {
        Route::get('/create', [AdminUserController::class, 'invititioncreate'])->name('create');
        Route::post('/create', [AdminUserController::class, 'invititionstore'])->name('store');
        Route::get('/list', [AdminUserController::class, 'invitationList'])->name('list');
    });
    Route::post('/users/{id}/change-role', [AdminUserController::class, 'changeRole'])->name('users.changeRole');
});

/*
|--------------------------------------------------------------------------
| Public Invitation Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::prefix('invite')->name('invite.')->group(function () {
    // Accept invitation link
    Route::get('/accept/{token}', [AdminUserController::class, 'invititionaccept'])
        ->name('accept');
    // Complete registration
    Route::post('/complete/{token}', [AdminUserController::class, 'invititioncomplete'])
        ->name('complete');
    Route::post('/resend/{id}', [AdminUserController::class, 'resend'])->name('resend');
    Route::delete('/delete/{id}', [AdminUserController::class, 'destroyinvite'])->name('destroyinvite');
});




//role & permission routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/update/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles/{id}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
    Route::post('/roles/{id}/permissions/update', [RoleController::class, 'permissionsUpdate'])->name('roles.permissions.update');
    Route::post('/roles/{id}/default', [RoleController::class, 'setDefault'])->name('roles.setDefault');
});

//activity log routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/my-activity', [ActivityLogController::class, 'myActivity'])->name('my-activity.index');
});

//group management routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups/store', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/update/{id}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/delete/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{id}/restore', [GroupController::class, 'restore'])->name('groups.restore');
});

//folder access routes (view+download grants, by user or group)
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/folder-access/bulk-users', [FolderAccessController::class, 'bulkEditUsers'])->name('folder-access.bulkEditUsers');
    Route::post('/folder-access/bulk-users', [FolderAccessController::class, 'bulkUpdateUsers'])->name('folder-access.bulkUpdateUsers');
    Route::get('/folder-access/{type}/{id}', [FolderAccessController::class, 'edit'])
        ->where('type', 'user|group')->name('folder-access.edit');
    Route::post('/folder-access/{type}/{id}', [FolderAccessController::class, 'update'])
        ->where('type', 'user|group')->name('folder-access.update');
});

// Analytics routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/analytics',                          [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/projects/{project}',       [AnalyticsController::class, 'project'])->name('analytics.project');
    Route::get('/analytics/users/{userId}',           [AnalyticsController::class, 'userDetail'])->name('analytics.userDetail');
});

// Project routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/projects/archived',                  [ProjectController::class, 'archived'])->name('projects.archived');
    Route::post('/projects/{project}/archive',        [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{project}/restore',        [ProjectController::class, 'restore'])->name('projects.restore');
    Route::post('/projects/{id}/restore-deleted',     [ProjectController::class, 'restoreDeleted'])->name('projects.restoreDeleted');
    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::get('/projects/{project}',                 [ProjectController::class, 'show'])->name('projects.show');
});

//folder routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/folders/shared/{parent_id?}', [FolderController::class, 'index'])->name('shared.folders');
    // Route::get('/folders/shared/{parent_id}', [FolderController::class, 'sharedIndex'])->name('shared.folders');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::put('/folders/{id}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{id}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::post('/toggle-favorite', [FolderController::class, 'toggleFavorite']);
    Route::get('/folders/favorite/{parent_id?}', [FolderController::class, 'favorites'])->name('favorite.folders');
    Route::post('/folders/share', [FolderController::class, 'share'])->name('folders.share');

    Route::post('/folders/rename', [FolderController::class, 'rename'])->name('folders.mrename');
    Route::post('/folders/move', [FolderController::class, 'move'])->name('folders.mmove');
    Route::post('/folders/copy-multiple', [FolderController::class, 'copyMultiple'])->name('folders.mcopyMultiple');
    Route::post('/folders/copy-item', [FolderController::class, 'copyItem'])->name('folders.copyItem');
    Route::post('/folders/move-item', [FolderController::class, 'moveItem'])->name('folders.moveItem');
    Route::get('/folders/download-multiples', [FolderController::class, 'downloadMultiple'])->name('folders.mdownloadMultiple');

    Route::get('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/files/store', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{id}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::post('/files/{id}/update', [FileController::class, 'updateFile'])->name('files.update');
    Route::get('/files/{id}/download/{type}', [FileController::class, 'download'])->name('files.download');
});

// //folder action
// Route::prefix('folders')->group(function () {
//     Route::post('/rename/{id}', [FolderActionController::class, 'rename'])->name('folders.rename');
//     Route::post('/move/{id}', [FolderActionController::class, 'move'])->name('folders.move');
//     Route::delete('/delete/{id}', [FolderActionController::class, 'delete'])->name('folders.delete');
//     Route::post('/copy/{id}', [FolderActionController::class, 'copy'])->name('folders.copy');
//     Route::get('/download/{id}', [FolderActionController::class, 'downloadZip'])->name('folders.download');
//     // Route::get('/download-multiple', [FolderActionController::class, 'downloadMultiple'])
//     // ->name('folders.download.multiple');
// });





Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/__test_login/{id}', function ($id) {
    auth()->loginUsingId($id);
    return redirect('/dashboard');
});

require __DIR__ . '/auth.php';
