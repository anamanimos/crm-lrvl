<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WebhookLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AutoReplyController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// WhatsApp Webhook (Public)
// WhatsApp Webhook (Public)
Route::post('/webhook/wa', [\App\Http\Controllers\WebhookController::class, 'receive'])->name('webhook.receive');

Route::get('/auth/oidc/redirect', [App\Http\Controllers\Auth\OidcController::class, 'redirect'])->name('oidc.redirect');
Route::get('/auth/oidc/callback', [App\Http\Controllers\Auth\OidcController::class, 'callback'])->name('oidc.callback');
Route::get('/auth/claim-admin', [App\Http\Controllers\Auth\OidcController::class, 'showClaimForm'])->name('auth.claim-admin')->middleware('auth');
Route::post('/auth/claim-admin', [App\Http\Controllers\Auth\OidcController::class, 'claimAdmin'])->name('auth.claim-admin.post')->middleware('auth');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers
    Route::name('admin.')->group(function() {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers-search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers/import', [\App\Http\Controllers\ImportController::class, 'index'])->name('customers.import');
        Route::get('/customers/import/preview', [\App\Http\Controllers\ImportController::class, 'preview'])->name('customers.import.preview');
        Route::post('/customers/import/import', [\App\Http\Controllers\ImportController::class, 'import'])->name('customers.import.process');
        Route::get('/customers/import/batch', [\App\Http\Controllers\ImportController::class, 'batch'])->name('customers.import.batch');
        Route::get('/customers/import/reset_truncate', [\App\Http\Controllers\ImportController::class, 'resetTruncate'])->name('customers.import.reset_truncate');
        
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::post('/customers/{id}/update', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/{id}/delete', [CustomerController::class, 'destroy'])->name('customers.delete');
        Route::post('/customers/{id}/archive', [CustomerController::class, 'archive'])->name('customers.archive');
        Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete');
        Route::post('/users/toggle-status/{id}', [UserController::class, 'toggle_status'])->name('users.toggle-status');

        // Auto Replies
        Route::prefix('auto-replies')->name('auto-replies.')->group(function () {
            Route::get('/', [AutoReplyController::class, 'index'])->name('index');
            Route::get('/create', [AutoReplyController::class, 'create'])->name('create');
            Route::post('/store', [AutoReplyController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AutoReplyController::class, 'edit'])->name('edit');
            Route::post('/{id}/update', [AutoReplyController::class, 'update'])->name('update');
            Route::post('/{id}/delete', [AutoReplyController::class, 'destroy'])->name('delete');
            Route::post('/{id}/toggle-status', [AutoReplyController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Roles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::get('/roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::post('/roles/delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
        Route::get('/roles/permissions/{id}', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::post('/roles/save-permissions', [RoleController::class, 'save_permissions'])->name('roles.save-permissions');
        Route::post('/roles/toggle-status/{id}', [RoleController::class, 'toggle_status'])->name('roles.toggle-status');

        // Companies
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::get('/companies/edit/{id}', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::post('/companies/store', [CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/update/{id}', [CompanyController::class, 'update'])->name('companies.update');
        Route::post('/companies/delete/{id}', [CompanyController::class, 'destroy'])->name('companies.delete');

        // Reports
        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
        Route::get('/reports/daily/agent/{id}', [ReportController::class, 'agentDetail'])->name('reports.daily.agent');

        // Labels
        Route::get('/labels', [LabelController::class, 'index'])->name('labels.index');
        Route::get('/labels/create', [LabelController::class, 'create'])->name('labels.create');
        Route::get('/labels/edit/{id}', [LabelController::class, 'edit'])->name('labels.edit');
        Route::post('/labels/store', [LabelController::class, 'store'])->name('labels.store');
        Route::post('/labels/update/{id}', [LabelController::class, 'update'])->name('labels.update');
        Route::post('/labels/delete/{id}', [LabelController::class, 'destroy'])->name('labels.delete');
        Route::post('/labels/toggle-status/{id}', [LabelController::class, 'toggle_status'])->name('labels.toggle-status');

        // Templates
        Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [\App\Http\Controllers\TemplateController::class, 'create'])->name('templates.create');
        Route::get('/templates/edit/{id}', [\App\Http\Controllers\TemplateController::class, 'edit'])->name('templates.edit');
        Route::post('/templates/store', [\App\Http\Controllers\TemplateController::class, 'store'])->name('templates.store');
        Route::post('/templates/update/{id}', [\App\Http\Controllers\TemplateController::class, 'update'])->name('templates.update');
        Route::post('/templates/delete/{id}', [\App\Http\Controllers\TemplateController::class, 'destroy'])->name('templates.delete');
        Route::post('/templates/toggle-status/{id}', [\App\Http\Controllers\TemplateController::class, 'toggle_status'])->name('templates.toggle-status');

        // Template Categories
        Route::get('/templates/category', [\App\Http\Controllers\TemplateCategoryController::class, 'index'])->name('templates.category.index');
        Route::post('/templates/category/store', [\App\Http\Controllers\TemplateCategoryController::class, 'store'])->name('templates.category.store');
        Route::post('/templates/category/update/{id}', [\App\Http\Controllers\TemplateCategoryController::class, 'update'])->name('templates.category.update');
        Route::post('/templates/category/delete/{id}', [\App\Http\Controllers\TemplateCategoryController::class, 'destroy'])->name('templates.category.delete');

        // Broadcasts
        Route::get('/broadcasts', [\App\Http\Controllers\BroadcastController::class, 'index'])->name('broadcasts.index');
        Route::get('/broadcasts/create', [\App\Http\Controllers\BroadcastController::class, 'create'])->name('broadcasts.create');
        Route::post('/broadcasts/store', [\App\Http\Controllers\BroadcastController::class, 'store'])->name('broadcasts.store');
        Route::get('/broadcasts/view/{id}', [\App\Http\Controllers\BroadcastController::class, 'view'])->name('broadcasts.view');
        Route::get('/broadcasts/action/{id}/{action}', [\App\Http\Controllers\BroadcastController::class, 'action'])->name('broadcasts.action');
        Route::get('/broadcasts/stats/{id}', [\App\Http\Controllers\BroadcastController::class, 'stats'])->name('broadcasts.stats');
        Route::post('/broadcasts/delete/{id}', [\App\Http\Controllers\BroadcastController::class, 'destroy'])->name('broadcasts.delete');
        
        });

    // Deals (Root prefix)
    Route::prefix('deals')->name('deals.')->group(function () {
        Route::get('/', [DealController::class, 'index'])->name('index');
        Route::get('/board-data', [DealController::class, 'getBoardData'])->name('board-data');
        Route::post('/store', [DealController::class, 'store'])->name('store');
        Route::post('/update-stage', [DealController::class, 'updateStage'])->name('update-stage');
        Route::get('/detail/{uuid}', [DealController::class, 'detail'])->name('detail');
        Route::post('/{id}/activity', [DealController::class, 'addActivity'])->name('activity');
        Route::post('/{id}/archive', [DealController::class, 'archive'])->name('archive');

        // Stages Settings
        Route::get('/settings/stages', [DealStageController::class, 'index'])->name('stages.index');
        Route::post('/settings/stages/store', [DealStageController::class, 'store'])->name('stages.store');
        Route::post('/settings/stages/update/{id}', [DealStageController::class, 'update'])->name('stages.update');
        Route::post('/settings/stages/reorder', [DealStageController::class, 'reorder'])->name('stages.reorder');
        Route::post('/settings/stages/delete/{id}', [DealStageController::class, 'destroy'])->name('stages.delete');
    });

    // Settings (No /admin prefix)
    Route::name('settings.')->group(function() {
        Route::get('/settings/test/erp', [SettingController::class, 'testErp'])->name('test.erp');
        Route::get('/settings/whatsapp/status', [SettingController::class, 'waStatus'])->name('whatsapp.status');
        Route::get('/settings/whatsapp/pairing', [SettingController::class, 'waPairing'])->name('whatsapp.pairing');
        Route::get('/settings/whatsapp/logout', [SettingController::class, 'waLogout'])->name('whatsapp.logout');
        Route::post('/settings/whatsapp/webhook/delete/{id}', [SettingController::class, 'waWebhookDelete'])->name('whatsapp.webhook.delete');
        Route::post('/settings/whatsapp/webhook/clear', [SettingController::class, 'waWebhookClearOld'])->name('whatsapp.webhook.clear');
        Route::get('/settings/test/cloudinary', [SettingController::class, 'testCloudinary'])->name('test.cloudinary');
        Route::get('/settings/test/minio', [SettingController::class, 'testMinio'])->name('test.minio');
        Route::post('/settings/test/upload-cloudinary', [SettingController::class, 'testUploadCloudinary'])->name('test.upload-cloudinary');
        Route::post('/settings/test/delete-cloudinary', [SettingController::class, 'testDeleteCloudinary'])->name('test.delete-cloudinary');
        Route::post('/settings/test/upload-minio', [SettingController::class, 'testUploadMinio'])->name('test.upload-minio');
        Route::post('/settings/test/delete-minio', [SettingController::class, 'testDeleteMinio'])->name('test.delete-minio');
        Route::post('/settings/whatsapp/storage_sync/start', [SettingController::class, 'startStorageSync'])->name('whatsapp.storage_sync.start');
        Route::post('/settings/backup/test', [SettingController::class, 'testBackup'])->name('backup.test');

        Route::get('/settings', [SettingController::class, 'index'])->name('index');
        Route::get('/settings/{section}/{subsection?}', [SettingController::class, 'index'])->name('section');
        Route::post('/settings/store', [SettingController::class, 'store'])->name('store');
    });

    // Docs
    Route::get('/docs/api', [\App\Http\Controllers\ApiDocsController::class, 'index'])->name('docs.api');

    // Chat
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::get('/customers', [\App\Http\Controllers\ChatController::class, 'customers'])->name('customers');
        Route::get('/poll', [\App\Http\Controllers\ChatController::class, 'poll'])->name('poll');
        Route::post('/conversation', [\App\Http\Controllers\ChatController::class, 'conversation'])->name('conversation');
        Route::post('/send', [\App\Http\Controllers\ChatController::class, 'send'])->name('send');
        Route::post('/send_image', [\App\Http\Controllers\ChatController::class, 'sendImage'])->name('send_image');
        Route::post('/send_document', [\App\Http\Controllers\ChatController::class, 'sendDocument'])->name('send_document');
        Route::post('/edit_message', [\App\Http\Controllers\ChatController::class, 'editMessage'])->name('edit_message');
        Route::post('/mark_chat_as_read', [\App\Http\Controllers\ChatController::class, 'markAsRead'])->name('mark_as_read');
        Route::post('/mark_chat_as_unread', [\App\Http\Controllers\ChatController::class, 'markAsUnread'])->name('mark_as_unread');
        Route::post('/bulk_mark_read', [\App\Http\Controllers\ChatController::class, 'bulkMarkRead'])->name('bulk_mark_read');
        Route::post('/rename_chat', [\App\Http\Controllers\ChatController::class, 'renameChat'])->name('rename_chat');
        Route::post('/forward_message', [\App\Http\Controllers\ChatController::class, 'forwardMessage'])->name('forward_message');
        Route::post('/assign', [\App\Http\Controllers\ChatController::class, 'assign'])->name('assign');
        Route::post('/assign_labels', [\App\Http\Controllers\ChatController::class, 'assignLabels'])->name('assign_labels');
        Route::get('/get_customer_detail_api', [\App\Http\Controllers\ChatController::class, 'getCustomerDetailApi'])->name('get_customer_detail_api');
        
        Route::post('/refresh_group_info', [\App\Http\Controllers\ChatController::class, 'refreshGroupInfo'])->name('refresh_group_info');
        
        Route::get('/templates', [\App\Http\Controllers\ChatController::class, 'templates'])->name('templates');
        Route::post('/sync', [\App\Http\Controllers\ChatController::class, 'sync'])->name('sync');
        Route::post('/sync_contacts', [\App\Http\Controllers\ChatController::class, 'syncContacts'])->name('sync_contacts');
        Route::get('/check_whatsapp', [\App\Http\Controllers\ChatController::class, 'checkWhatsApp'])->name('check_whatsapp');
        Route::post('/refresh_avatar', [\App\Http\Controllers\ChatController::class, 'refreshAvatar'])->name('refresh_avatar');
        Route::post('/start_new_chat', [\App\Http\Controllers\ChatController::class, 'startNewChat'])->name('start_new_chat');
    });
});



require __DIR__.'/auth.php';
