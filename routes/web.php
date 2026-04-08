<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BibSettingController as AdminBibSettingController;
use App\Http\Controllers\Admin\BibScanController as AdminBibScanController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistanceCategoryController as AdminDistanceCategoryController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\ParticipantController as AdminParticipantController;
use App\Http\Controllers\Admin\RaceTimingController as AdminRaceTimingController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PaymentController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\RaceReportController as AdminRaceReportController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\DatabaseManagementController as AdminDatabaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/login', '/admin/login')->name('login');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::get('/events/{event}/register', [RegistrationController::class, 'create'])->name('registrations.create');
Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('registrations.store');
Route::get('/registrations/payment/{participant}/{token}', [PaymentController::class, 'create'])->name('registrations.payment.create');
Route::post('/registrations/payment/{participant}/{token}', [PaymentController::class, 'store'])->name('registrations.payment.store');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::put('/contacts', [AdminContactController::class, 'update'])->name('contacts.update');
        
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::get('/bib-settings', [AdminBibSettingController::class, 'index'])->name('bib-settings.index');
        Route::put('/bib-settings', [AdminBibSettingController::class, 'update'])->name('bib-settings.update');
        Route::get('/bib-settings/layout-guide', [AdminBibSettingController::class, 'layoutGuide'])->name('bib-settings.layout-guide');
        Route::get('/bib-scan', [AdminBibScanController::class, 'index'])->name('bib-scan.index');
        Route::get('/bib-scan/kiosk', [AdminBibScanController::class, 'kiosk'])->name('bib-scan.kiosk');
        Route::get('/bib-scan/kiosk/lookup', [AdminBibScanController::class, 'kioskLookup'])->name('bib-scan.kiosk.lookup');

        Route::get('/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::post('/gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::put('/gallery/{gallery}', [AdminGalleryController::class, 'update'])->name('gallery.update');
        Route::delete('/gallery/{gallery}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::patch('/gallery/{gallery}/toggle', [AdminGalleryController::class, 'toggleActive'])->name('gallery.toggle');

        Route::get('/distance-categories', [AdminDistanceCategoryController::class, 'index'])->name('distance-categories.index');
        Route::post('/distance-categories', [AdminDistanceCategoryController::class, 'store'])->name('distance-categories.store');
        Route::put('/distance-categories/{distanceCategory}', [AdminDistanceCategoryController::class, 'update'])->name('distance-categories.update');
        Route::delete('/distance-categories/{distanceCategory}', [AdminDistanceCategoryController::class, 'destroy'])->name('distance-categories.destroy');
        Route::patch('/distance-categories/{distanceCategory}/toggle', [AdminDistanceCategoryController::class, 'toggleActive'])->name('distance-categories.toggle');

        Route::get('/events/data', [AdminEventController::class, 'data'])->name('events.data');
        Route::get('/events/{event}/quota', [AdminEventController::class, 'getQuota'])->name('events.quota.get');
        Route::post('/events/{event}/quota', [AdminEventController::class, 'updateQuota'])->name('events.quota.update');
        Route::resource('events', AdminEventController::class)->except(['show']);

        Route::get('/participants/data', [AdminParticipantController::class, 'data'])->name('participants.data');
        Route::get('/participants/export', [AdminParticipantController::class, 'export'])->name('participants.export');
        Route::get('/participants/export-pdf', [AdminParticipantController::class, 'exportPdf'])->name('participants.export_pdf');
        Route::get('/race-reports', [AdminRaceReportController::class, 'index'])->name('race-reports.index');
        Route::get('/race-reports/data', [AdminRaceReportController::class, 'data'])->name('race-reports.data');
        Route::get('/race-reports/export', [AdminRaceReportController::class, 'export'])->name('race-reports.export');
        Route::get('/race-reports/export-pdf', [AdminRaceReportController::class, 'exportPdf'])->name('race-reports.export-pdf');
        Route::get('/certificates/data', [AdminCertificateController::class, 'data'])->name('certificates.data');
        Route::get('/certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
        Route::post('/certificates/background', [AdminCertificateController::class, 'updateBackground'])->name('certificates.background.update');
        Route::post('/certificates/elements', [AdminCertificateController::class, 'saveElements'])->name('certificates.elements.save');
        Route::get('/certificates/preview', [AdminCertificateController::class, 'previewPdf'])->name('certificates.preview');
        Route::get('/participants/{participant}/certificate', [AdminCertificateController::class, 'downloadParticipant'])->name('participants.certificate');
        Route::post('/certificates/bulk', [AdminCertificateController::class, 'downloadBulk'])->name('certificates.bulk');
        Route::post('/participants/{participant}/certificate/send-email', [AdminCertificateController::class, 'sendEmail'])->name('participants.certificate.send-email');
        Route::post('/certificates/send-email-bulk', [AdminCertificateController::class, 'sendEmailBulk'])->name('certificates.send-email-bulk');
        Route::get('/race-timing', [AdminRaceTimingController::class, 'index'])->name('race-timing.index');
        Route::post('/race-timing', [AdminRaceTimingController::class, 'store'])->name('race-timing.store');
        Route::post('/participants/id-card/bulk', [AdminParticipantController::class, 'exportIdCardBulk'])->name('participants.id-card.bulk');
        Route::get('/participants/{participant}/id-card', [AdminParticipantController::class, 'exportIdCard'])->name('participants.id-card');
        Route::get('/participants', [AdminParticipantController::class, 'index'])->name('participants.index');
        Route::get('/participants/{participant}', [AdminParticipantController::class, 'show'])->name('participants.show');
        Route::patch('/participants/{participant}/verify', [AdminParticipantController::class, 'verify'])->name('participants.verify');
        Route::patch('/participants/{participant}/reject', [AdminParticipantController::class, 'reject'])->name('participants.reject');
        Route::patch('/participants/{participant}/payment/approve', [AdminParticipantController::class, 'approvePayment'])->name('participants.payment.approve');
        Route::patch('/participants/{participant}/payment/reject', [AdminParticipantController::class, 'rejectPayment'])->name('participants.payment.reject');

        // Database Management Routes
        Route::get('/database', [AdminDatabaseController::class, 'index'])->name('database.index');
        Route::post('/database/backup', [AdminDatabaseController::class, 'backup'])->name('database.backup');
        Route::post('/database/restore', [AdminDatabaseController::class, 'restore'])->name('database.restore');
        Route::post('/database/delete', [AdminDatabaseController::class, 'delete'])->name('database.delete');
        Route::get('/database/download/{filename}', [AdminDatabaseController::class, 'download'])->name('database.download');
        Route::delete('/database/backup/{filename}', [AdminDatabaseController::class, 'destroyBackup'])->name('database.backup.destroy');
    });
});
