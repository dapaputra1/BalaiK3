<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BapController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\BillingController;
// Auth
use App\Http\Controllers\BillingGuideController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\DokumenSptController;
use App\Http\Controllers\DraftLhuController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KajiUlangController;
use App\Http\Controllers\KodingController;
use App\Http\Controllers\MaApprovalController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\OrderReviewController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PenawaranController;
use App\Http\Controllers\PenerbitanSuketController;
use App\Http\Controllers\PengujianController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\PenyerahanLhuController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PrepanalisaController;
use App\Http\Controllers\QcLhuController;
use App\Http\Controllers\RiwayatPelayananController;
use App\Http\Controllers\SuperadminAbsorbansiController;
use App\Http\Controllers\SuperadminBeritaController;
use App\Http\Controllers\SuperadminDashboardController;
use App\Http\Controllers\SuperadminFeedbackController;
use App\Http\Controllers\SuperadminJejaringController;
use App\Http\Controllers\SuperadminLoginBackgroundController;
use App\Http\Controllers\SuperadminMedsosController;
use App\Http\Controllers\SuperadminParameterLodController;
use App\Http\Controllers\SuperadminPermohonanController;
use App\Http\Controllers\SuperadminServiceCategoryController;
use App\Http\Controllers\SuperadminServiceParameterController;
use App\Http\Controllers\SuperadminUserController;
use App\Http\Controllers\SuratTagihanController;
use App\Http\Controllers\TtdLhuController;
use App\Http\Controllers\UlasanPermohonanController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\VerifikasiPengujianController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])
    ->withoutMiddleware([
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ])
    ->name('sitemap');

// ---------- PUBLIC ROUTES ----------
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');
});

// ---------- OPEN PAGES ----------
Route::get('/kontak', [FeedbackController::class, 'index'])->name('kontak');
Route::get('/daftar_pelayanan', [HomeController::class, 'daftarPelayanan'])->name('daftar_pelayanan');
Route::get('/jejaring/universitas', [HomeController::class, 'jejaringUniversitas'])->name('jejaring.universitas');
Route::get('/jejaring/pjk3', [HomeController::class, 'jejaringPjk3'])->name('jejaring.pjk3');
Route::get('/jejaring/perusahaan', [HomeController::class, 'jejaringPerusahaan'])->name('jejaring.perusahaan');
Route::get('/jejaring/instansi-wilayah-kerja', [HomeController::class, 'jejaringInstansiWilayahKerja'])->name('jejaring.instansi-wilayah-kerja');
Route::get('/jejaring/instansi', [HomeController::class, 'jejaringInstansi'])->name('jejaring.instansi');
Route::get('/berita', [BeritaController::class, 'index'])->name('berita');
Route::get('/berita/{slug}', [BeritaController::class, 'show'])->name('berita.show');
Route::view('/visi_misi', 'visi_misi')->name('visi_misi');
Route::view('/alur_pelayanan', 'alur_pelayanan')->name('alur_pelayanan');
Route::view('/struktur', 'struktur')->name('struktur');
Route::view('/sarana_prasarana', 'sarana_prasarana')->name('sarana_prasarana');
Route::view('/video_profil', 'video_profil')->name('video_profil');

// ---------- PROTECTED ROUTES ----------
Route::middleware('auth')->group(function () {
    // Route::get('home', [HomeController::class,'index']);

    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::put('/feedback/{feedback}', [FeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    Route::view('/keranjang', 'keranjang')->name('keranjang');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::post('/cart/packages', [CartController::class, 'storePackage'])->name('cart.packages.store');
    Route::delete('/cart/items', [CartController::class, 'clear'])->name('cart.items.clear');
    Route::patch('/cart/items/{service_parameter}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{service_parameter}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart/packages/{packageKey}', [CartController::class, 'destroyPackage'])->name('cart.packages.destroy');
    Route::post('/permohonan', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/riwayat_pelayanan', [RiwayatPelayananController::class, 'index'])->name('riwayat_pelayanan.index');
    Route::post('/riwayat_pelayanan/{permohonan}/cancel', [RiwayatPelayananController::class, 'cancel'])->name('riwayat_pelayanan.cancel');
    Route::post('/riwayat_pelayanan/{permohonan}/jadwal/approve', [RiwayatPelayananController::class, 'approveSchedule'])->name('riwayat_pelayanan.jadwal.approve');
    Route::post('/riwayat_pelayanan/{permohonan}/jadwal/reject', [RiwayatPelayananController::class, 'rejectSchedule'])->name('riwayat_pelayanan.jadwal.reject');
    Route::post('/riwayat_pelayanan/{permohonan}/penawaran/accept', [RiwayatPelayananController::class, 'acceptPenawaran'])->name('riwayat_pelayanan.penawaran.accept');
    Route::post('/riwayat_pelayanan/{permohonan}/penawaran/reject', [RiwayatPelayananController::class, 'rejectPenawaran'])->name('riwayat_pelayanan.penawaran.reject');
    Route::post('/riwayat_pelayanan/{permohonan}/bap/approve', [RiwayatPelayananController::class, 'approveBap'])->name('riwayat_pelayanan.bap.approve');
    Route::get('/riwayat_pelayanan/{permohonan}/billing', [RiwayatPelayananController::class, 'showBilling'])->name('riwayat_pelayanan.billing.show');
    Route::get('/riwayat_pelayanan/{permohonan}/billing/payment-proof', [RiwayatPelayananController::class, 'showBillingPaymentProof'])->name('riwayat_pelayanan.billing.payment-proof.show');
    Route::get('/riwayat_pelayanan/{permohonan}/order-proof', [RiwayatPelayananController::class, 'showOrderProof'])->name('riwayat_pelayanan.order-proof.show');
    Route::get('/riwayat_pelayanan/{permohonan}/invoice', [RiwayatPelayananController::class, 'showInvoice'])->name('riwayat_pelayanan.invoice.show');
    Route::get('/riwayat_pelayanan/{permohonan}/surat-tagihan', [RiwayatPelayananController::class, 'showSuratTagihan'])->name('riwayat_pelayanan.surat-tagihan.show');
    Route::get('/riwayat_pelayanan/{permohonan}/suket', [RiwayatPelayananController::class, 'showSuket'])->name('riwayat_pelayanan.suket.show');
    Route::post('/riwayat_pelayanan/{permohonan}/invoice/verify', [RiwayatPelayananController::class, 'verifyInvoice'])->name('riwayat_pelayanan.invoice.verify');
    Route::post('/riwayat_pelayanan/{permohonan}/billing/confirm-payment', [RiwayatPelayananController::class, 'confirmBillingPayment'])->name('riwayat_pelayanan.billing.confirm-payment');
    Route::post('/riwayat_pelayanan/{permohonan}/billing/request-renewal', [RiwayatPelayananController::class, 'requestBillingRenewal'])->name('riwayat_pelayanan.billing.request-renewal');
    Route::get('/billing-guide/active', [BillingGuideController::class, 'active'])->name('billing-guide.active');
    Route::get('/billing-guide/preview', [BillingGuideController::class, 'preview'])->name('billing-guide.preview');
    Route::get('/billing-guide/download', [BillingGuideController::class, 'download'])->name('billing-guide.download');
    Route::get('/riwayat_pelayanan/{permohonan}/lhu', [RiwayatPelayananController::class, 'showLhu'])->name('riwayat_pelayanan.lhu.show');
    Route::post('/riwayat_pelayanan/{permohonan}/lhu/ulasan', [RiwayatPelayananController::class, 'storeLhuUlasan'])->name('riwayat_pelayanan.lhu.ulasan.store');
    Route::post('/riwayat_pelayanan/{permohonan}/lhu/approve', [RiwayatPelayananController::class, 'approveLhu'])->name('riwayat_pelayanan.lhu.approve');
    Route::post('/riwayat_pelayanan/{permohonan}/lhu/revise', [RiwayatPelayananController::class, 'reviseLhu'])->name('riwayat_pelayanan.lhu.revise');
    Route::post('/riwayat_pelayanan/{permohonan}/reorder', [RiwayatPelayananController::class, 'reorder'])->name('riwayat_pelayanan.reorder');
    Route::post('/riwayat_pelayanan/{permohonan}/order-review/approve', [OrderReviewController::class, 'approveByCustomer'])->name('riwayat_pelayanan.order-review.approve');

    Route::middleware('role:superadmin,pcu,penyelia,admin')
        ->get('/pengujian/files/{file}', [PengujianController::class, 'downloadFile'])
        ->name('pengujian.files.show');

    Route::middleware('role:superadmin,admin,pcu')
        ->get('/dokumen-spt/{permohonan}/signed', [DokumenSptController::class, 'signed'])
        ->name('dokumen-spt.signed');

    Route::get('/permohonan/{permohonan}/signature', [PermohonanController::class, 'signature'])
        ->name('permohonan.signature');
    Route::get('/petugas/{petuga}/signature', [PetugasController::class, 'signature'])
        ->name('petugas.signature');
    Route::get('/petugas-signature/me', [PetugasController::class, 'mySignature'])
        ->name('petugas.signature.me');
    Route::middleware('role:superadmin,admin,ma,pcu,penyelia,analis,qc,mp,mt')
        ->get('/profil-petugas', [PetugasController::class, 'editProfile'])
        ->name('petugas.profile.edit');
    Route::middleware('role:superadmin,admin,ma,pcu,penyelia,analis,qc,mp,mt')
        ->put('/profil-petugas', [PetugasController::class, 'updateProfile'])
        ->name('petugas.profile.update');
    Route::get('/penawaran/dokumen/{dokumen}', [PenawaranController::class, 'showDocument'])
        ->name('penawaran.documents.show');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/mark-all-read', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.mark-all-read');
    Route::delete('/notifikasi/clear-all', [NotifikasiController::class, 'clearAll'])->name('notifikasi.clear-all');
    Route::get('/notifikasi/{notifikasi}', [NotifikasiController::class, 'open'])->name('notifikasi.open');

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::match(['post', 'put'], '/billing-guide/upload', [BillingGuideController::class, 'upload'])->name('billing-guide.upload');
        Route::delete('/billing-guide', [BillingGuideController::class, 'destroy'])->name('billing-guide.destroy');
    });

    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Portal Pemohon: Permohonan Suket K3 (Tahap 1 Mandiri Khusus Pelanggan)
Route::middleware(['auth', 'role:user'])
    ->prefix('permohonan-suket')
    ->name('user.suket.')
    ->group(function () {
        Route::get('/', [PenerbitanSuketController::class, 'userIndex'])->name('index');
        Route::post('/store', [PenerbitanSuketController::class, 'userStore'])->name('store');
        Route::post('/{suket}/submit-revision', [PenerbitanSuketController::class, 'userSubmitRevision'])->name('submit-revision');
    });

// Menu Internal Petugas: Penerbitan Suket K3 Lingkungan Kerja (Mulai Tahap 2 s/d 6)
Route::middleware(['auth', 'role:superadmin,admin,mp,pcu,kepala_balai,penguji_k3,qc,user'])
    ->prefix('suket-k3')
    ->name('suket.')
    ->group(function () {
        Route::get('/', [PenerbitanSuketController::class, 'index'])->name('index');
        Route::post('/store-by-order', [PenerbitanSuketController::class, 'storeByOrder'])->name('store-order');
        Route::post('/{suket}/advance', [PenerbitanSuketController::class, 'advanceStage'])->name('advance');
        Route::post('/{suket}/qc-review', [PenerbitanSuketController::class, 'qcReview'])->name('qc-review');
        Route::get('/{suket}/generate-draft', [PenerbitanSuketController::class, 'generateDraft'])->name('generate-draft');
        Route::post('/{suket}/upload-doc', [PenerbitanSuketController::class, 'uploadDocument'])->name('upload-doc');
        Route::post('/{suket}/comment', [PenerbitanSuketController::class, 'addComment'])->name('comment');
        Route::delete('/{suket}/comment/{comment}', [PenerbitanSuketController::class, 'deleteComment'])->name('comment.delete');
        Route::get('/{suket}/preview/{type}', [PenerbitanSuketController::class, 'previewDocument'])->name('preview-doc');
        Route::get('/{suket}/download/{type}', [PenerbitanSuketController::class, 'downloadDocument'])->name('download-doc');
    });

// Akses Download & Preview untuk Pemohon User
Route::middleware(['auth', 'role:user'])
    ->prefix('permohonan-suket')
    ->name('user.suket.')
    ->group(function () {
        Route::get('/{suket}/preview/{type}', [PenerbitanSuketController::class, 'previewDocument'])->name('preview-doc');
        Route::get('/{suket}/download/{type}', [PenerbitanSuketController::class, 'downloadDocument'])->name('download-doc');
    });

// Route::middleware('auth')->group(function () {
//     Route::get('/home', [HomeController::class, 'index']);
//     Route::get('/profile', [ProfileController::class, 'index']);
//     // route lain yang hanya untuk user login
// });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dokumen-spt', [DokumenSptController::class, 'index'])->name('dokumen-spt.index');
        Route::post('/dokumen-spt/{permohonan}', [DokumenSptController::class, 'store'])->name('dokumen-spt.store');
        Route::get('/dokumen-spt/{permohonan}/preview', [DokumenSptController::class, 'preview'])->name('dokumen-spt.preview');
        Route::post('/dokumen-spt/{permohonan}/signed', [DokumenSptController::class, 'uploadSigned'])->name('dokumen-spt.signed.store');
        Route::post('/dokumen-spt/{permohonan}/send-to-pcu', [DokumenSptController::class, 'sendToPcu'])->name('dokumen-spt.send-to-pcu');
        Route::post('/dokumen-spt/{permohonan}/return-to-penjadwalan', [DokumenSptController::class, 'returnToPenjadwalan'])->name('dokumen-spt.return-to-penjadwalan');
        Route::get('/bap', [BapController::class, 'index'])->name('bap.index');
        Route::post('/bap/{permohonan}/draft', [BapController::class, 'saveDraft'])->name('bap.draft');
        Route::post('/bap/{permohonan}/submit', [BapController::class, 'submitToVerifikasi'])->name('bap.submit');
        Route::post('/bap/{permohonan}/mark-viewed', [BapController::class, 'markViewed'])->name('bap.mark-viewed');
        Route::post('/pengujian/{permohonan}/forward-to-bap', [PengujianController::class, 'forwardToBap'])->name('pengujian.forward-bap');
        Route::get('/koding', [KodingController::class, 'index'])->name('koding.index');
        Route::post('/koding/{permohonan}/draft', [KodingController::class, 'saveDraft'])->name('koding.draft');
        Route::post('/koding/{permohonan}/submit', [KodingController::class, 'submitToPrepanalisa'])->name('koding.submit');
        Route::get('/draft-lhu', [DraftLhuController::class, 'index'])->name('draft-lhu.index');
        Route::post('/draft-lhu/{permohonan}/draft', [DraftLhuController::class, 'saveDraft'])->name('draft-lhu.draft');
        Route::get('/draft-lhu/{permohonan}/word', [DraftLhuController::class, 'downloadWord'])->name('draft-lhu.word');
        Route::get('/draft-lhu/{permohonan}/word-all', [DraftLhuController::class, 'downloadAllWord'])->name('draft-lhu.word-all');
        Route::post('/draft-lhu/{permohonan}/ai-summary', [DraftLhuController::class, 'generateAiSummary'])->name('draft-lhu.ai-summary');
        Route::post('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'uploadFinal'])->name('draft-lhu.final.upload');
        Route::get('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'showFinal'])->name('draft-lhu.final.show');
        Route::get('/draft-lhu/{permohonan}/qc-revision', [DraftLhuController::class, 'showQcRevision'])->name('draft-lhu.qc-revision.show');
        Route::post('/draft-lhu/{permohonan}/submit', [DraftLhuController::class, 'submitToQc'])->name('draft-lhu.submit');
        Route::get('/surat-tagihan', [SuratTagihanController::class, 'index'])->name('surat-tagihan.index');
        Route::get('/surat-tagihan/{permohonan}/show', [SuratTagihanController::class, 'show'])->name('surat-tagihan.show');
        Route::post('/surat-tagihan/{permohonan}/submit', [SuratTagihanController::class, 'submitToInvoice'])->name('surat-tagihan.submit');
        Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('/invoice/{permohonan}/show', [InvoiceController::class, 'show'])->name('invoice.show');
       
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/{permohonan}/upload', [BillingController::class, 'upload'])->name('billing.upload');
        Route::post('/billing/{permohonan}/send', [BillingController::class, 'sendToUser'])->name('billing.send');
        Route::post('/billing/{permohonan}/verify', [BillingController::class, 'verifyPayment'])->name('billing.verify');
        Route::get('/billing/{permohonan}/show', [BillingController::class, 'show'])->name('billing.show');
        Route::get('/billing/{permohonan}/payment-proof', [BillingController::class, 'showPaymentProof'])->name('billing.payment-proof.show');
        Route::get('/suket', [PenerbitanSuketController::class, 'index'])->name('suket.index');
        Route::post('/suket/{permohonan}/upload', [PenerbitanSuketController::class, 'upload'])->name('suket.upload');
        Route::post('/suket/{permohonan}/submit', [PenerbitanSuketController::class, 'submitToPenyerahan'])->name('suket.submit');
        Route::get('/suket/{permohonan}/show', [PenerbitanSuketController::class, 'show'])->name('suket.show');
        Route::get('/ulasan-permohonan', [UlasanPermohonanController::class, 'index'])->name('ulasan-permohonan.index');
        Route::post('/ulasan-permohonan', [UlasanPermohonanController::class, 'store'])->name('ulasan-permohonan.store');
        Route::put('/ulasan-permohonan/{ulasanPermohonanQuestion}', [UlasanPermohonanController::class, 'update'])->name('ulasan-permohonan.update');
        Route::delete('/ulasan-permohonan/{ulasanPermohonanQuestion}', [UlasanPermohonanController::class, 'destroy'])->name('ulasan-permohonan.destroy');
        Route::get('/penyerahan_lhu', [PenyerahanLhuController::class, 'index'])->name('penyerahan-lhu.index');
        Route::post('/penyerahan_lhu/{permohonan}/upload', [PenyerahanLhuController::class, 'uploadRevised'])->name('penyerahan-lhu.upload');
        Route::post('/penyerahan_lhu/{permohonan}/send', [PenyerahanLhuController::class, 'sendToUser'])->name('penyerahan-lhu.send');
        Route::get('/penyerahan_lhu/{permohonan}/signed', [PenyerahanLhuController::class, 'showSigned'])->name('penyerahan-lhu.signed.show');
    });

Route::middleware('auth')
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::middleware('role:superadmin,admin,ma,pcu,penyelia,analis,qc,mp,mt')->group(function () {
            Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/permohonan', [SuperadminPermohonanController::class, 'index'])->name('permohonan.index');
            Route::get('/permohonan/export', [SuperadminPermohonanController::class, 'export'])->name('permohonan.export');
        });

        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/order-review', [OrderReviewController::class, 'index'])->name('order-review.index');
            Route::put('/order-review/{permohonan}/send', [OrderReviewController::class, 'sendToCustomer'])->name('order-review.send');
            Route::post('/order-review/{permohonan}/approve', [OrderReviewController::class, 'approveDirectly'])->name('order-review.approve');
            Route::resource('berita', SuperadminBeritaController::class)->except(['show', 'create', 'edit']);
            Route::get('/jejaring', [SuperadminJejaringController::class, 'index'])->name('jejaring.index');
            Route::post('/jejaring', [SuperadminJejaringController::class, 'store'])->name('jejaring.store');
            Route::put('/jejaring/{jejaringEntry}', [SuperadminJejaringController::class, 'update'])->name('jejaring.update');
            Route::delete('/jejaring/{jejaringEntry}', [SuperadminJejaringController::class, 'destroy'])->name('jejaring.destroy');
            Route::get('/medsos', [SuperadminMedsosController::class, 'index'])->name('medsos.index');
            Route::post('/medsos', [SuperadminMedsosController::class, 'store'])->name('medsos.store');
            Route::put('/medsos/{socialMediaPost}', [SuperadminMedsosController::class, 'update'])->name('medsos.update');
            Route::delete('/medsos/{socialMediaPost}', [SuperadminMedsosController::class, 'destroy'])->name('medsos.destroy');
            Route::get('/login-backgrounds', [SuperadminLoginBackgroundController::class, 'index'])->name('login-backgrounds.index');
            Route::post('/login-backgrounds', [SuperadminLoginBackgroundController::class, 'store'])->name('login-backgrounds.store');
            Route::post('/login-backgrounds/home-popups', [SuperadminLoginBackgroundController::class, 'storeHomePopup'])->name('login-backgrounds.home-popups.store');
            Route::put('/login-backgrounds/home-popups/{homePopup}', [SuperadminLoginBackgroundController::class, 'updateHomePopup'])->name('login-backgrounds.home-popups.update');
            Route::put('/login-backgrounds/home-popups/{homePopup}/toggle', [SuperadminLoginBackgroundController::class, 'toggleHomePopup'])->name('login-backgrounds.home-popups.toggle');
            Route::delete('/login-backgrounds/home-popups/{homePopup}', [SuperadminLoginBackgroundController::class, 'destroyHomePopup'])->name('login-backgrounds.home-popups.destroy');
            Route::put('/login-backgrounds/{loginBackground}', [SuperadminLoginBackgroundController::class, 'update'])->name('login-backgrounds.update');
            Route::put('/login-backgrounds/{loginBackground}/activate', [SuperadminLoginBackgroundController::class, 'activate'])->name('login-backgrounds.activate');
            Route::delete('/login-backgrounds/{loginBackground}', [SuperadminLoginBackgroundController::class, 'destroy'])->name('login-backgrounds.destroy');
        });

        Route::middleware('role:superadmin')->group(function () {
            Route::delete('/permohonan/{permohonan}', [SuperadminPermohonanController::class, 'destroy'])->name('permohonan.destroy');
            Route::put('/petugas/kepala-balai', [PetugasController::class, 'updateKepalaBalai'])->name('petugas.kepala-balai.update');
            Route::put('/suket/settings/availability', [PenerbitanSuketController::class, 'updateAvailability'])->name('suket.settings.availability');
            Route::resource('petugas', PetugasController::class)->except(['show', 'create', 'edit']);
            Route::resource('users', SuperadminUserController::class)->only(['index', 'destroy']);
            Route::get('/service-parameters', [SuperadminServiceParameterController::class, 'index'])->name('service-parameters.index');
            Route::resource('service-parameters', SuperadminServiceParameterController::class)->only(['store', 'update', 'destroy']);
            Route::post('/service-packages', [SuperadminServiceParameterController::class, 'storePackage'])->name('service-packages.store');
            Route::put('/service-packages/{service_package}', [SuperadminServiceParameterController::class, 'updatePackage'])->name('service-packages.update');
            Route::delete('/service-packages/{service_package}', [SuperadminServiceParameterController::class, 'destroyPackage'])->name('service-packages.destroy');
            Route::resource('service-categories', SuperadminServiceCategoryController::class)->only(['store', 'update', 'destroy']);
            Route::get('/parameter-lods', [SuperadminParameterLodController::class, 'index'])->name('parameter-lods.index');
            Route::resource('parameter-lods', SuperadminParameterLodController::class)->only(['store', 'update', 'destroy']);
            Route::get('/absorbansi', [SuperadminAbsorbansiController::class, 'index'])->name('absorbansi.index');
            Route::get('/absorbansi/reference/{parameter}', [SuperadminAbsorbansiController::class, 'reference'])->name('absorbansi.reference');
            Route::post('/absorbansi', [SuperadminAbsorbansiController::class, 'store'])->name('absorbansi.store');
            Route::put('/absorbansi/{absorbansi}', [SuperadminAbsorbansiController::class, 'update'])->name('absorbansi.update');
            Route::delete('/absorbansi/{absorbansi}', [SuperadminAbsorbansiController::class, 'destroy'])->name('absorbansi.destroy');
            Route::get('/koding', [KodingController::class, 'index'])->name('koding.index');
            Route::post('/koding/{permohonan}/draft', [KodingController::class, 'saveDraft'])->name('koding.draft');
            Route::post('/koding/{permohonan}/submit', [KodingController::class, 'submitToPrepanalisa'])->name('koding.submit');
            Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
            Route::post('/verifikasi/submit', [VerifikasiController::class, 'submit'])->name('verifikasi.submit');
            Route::get('/verifikasi/hasil/{item}', [VerifikasiController::class, 'previewHasil'])->name('verifikasi.hasil-preview');
            Route::get('/pengujian', [PengujianController::class, 'index'])->name('pengujian.index');
            Route::get('/verifikasi-pcu', [PengujianController::class, 'revisionIndex'])->name('verifikasi-pcu.index');
            Route::post('/pengujian/{permohonan}/draft', [PengujianController::class, 'saveDraft'])->name('pengujian.draft');
            Route::post('/pengujian/{permohonan}/submit', [PengujianController::class, 'submitToBap'])->name('pengujian.submit');
            Route::post('/pengujian/{permohonan}/forward-to-bap', [PengujianController::class, 'forwardToBap'])->name('pengujian.forward-bap');
            Route::post('/pengujian/{permohonan}/return-to-penjadwalan', [PengujianController::class, 'returnToPenjadwalan'])->name('pengujian.return-to-penjadwalan');
            Route::get('/bap', [BapController::class, 'index'])->name('bap.index');
            Route::post('/bap/{permohonan}/draft', [BapController::class, 'saveDraft'])->name('bap.draft');
            Route::post('/bap/{permohonan}/submit', [BapController::class, 'submitToVerifikasi'])->name('bap.submit');
            Route::post('/bap/{permohonan}/mark-viewed', [BapController::class, 'markViewed'])->name('bap.mark-viewed');
            Route::get('/verifikasi-pengujian', [VerifikasiPengujianController::class, 'index'])->name('verifikasi-pengujian.index');
            Route::post('/verifikasi-pengujian/{permohonan}/submit', [VerifikasiPengujianController::class, 'verify'])->name('verifikasi-pengujian.submit');
            Route::get('/prepanalisa', [PrepanalisaController::class, 'index'])->name('prepanalisa.index');
            Route::get('/prepanalisa/formula-references', [PrepanalisaController::class, 'formulaReferences'])->name('prepanalisa.formula-references');
            Route::get('/prepanalisa/riwayat', [PrepanalisaController::class, 'history'])->name('prepanalisa.history');
            Route::get('/prepanalisa/riwayat/export', [PrepanalisaController::class, 'exportHistory'])->name('prepanalisa.history.export');
            Route::get('/prepanalisa/riwayat/hasil/{item}', [PrepanalisaController::class, 'previewHistoryHasil'])->name('prepanalisa.history.hasil');
            Route::get('/prepanalisa/riwayat/hasil/{item}/data', [PrepanalisaController::class, 'historyHasilData'])->name('prepanalisa.history.hasil.data');
            Route::post('/prepanalisa/std-kalibrasi/export-excel', [PrepanalisaController::class, 'exportStdKalibrasiExcel'])->name('prepanalisa.std-kalibrasi.export-excel');
            Route::post('/prepanalisa/{permohonan}/draft', [PrepanalisaController::class, 'saveDraft'])->name('prepanalisa.draft');
            Route::post('/prepanalisa/{permohonan}/assign', [PrepanalisaController::class, 'assign'])->name('prepanalisa.assign');
            Route::post('/prepanalisa/{permohonan}/reset-action', [PrepanalisaController::class, 'resetAction'])->name('prepanalisa.reset-action');
            Route::get('/prepanalisa/penyerahan-preview', [PrepanalisaController::class, 'previewPenyerahan'])->name('prepanalisa.penyerahan-preview');
            Route::post('/prepanalisa/{permohonan}/item-done', [PrepanalisaController::class, 'updateItemDone'])->name('prepanalisa.item-done');
            Route::post('/prepanalisa/submit', [PrepanalisaController::class, 'submitToVerifikasi'])->name('prepanalisa.submit');
            Route::get('/draft-lhu', [DraftLhuController::class, 'index'])->name('draft-lhu.index');
            Route::post('/draft-lhu/{permohonan}/draft', [DraftLhuController::class, 'saveDraft'])->name('draft-lhu.draft');
            Route::get('/draft-lhu/{permohonan}/word', [DraftLhuController::class, 'downloadWord'])->name('draft-lhu.word');
            Route::get('/draft-lhu/{permohonan}/word-all', [DraftLhuController::class, 'downloadAllWord'])->name('draft-lhu.word-all');
            Route::post('/draft-lhu/{permohonan}/ai-summary', [DraftLhuController::class, 'generateAiSummary'])->name('draft-lhu.ai-summary');
            Route::post('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'uploadFinal'])->name('draft-lhu.final.upload');
            Route::get('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'showFinal'])->name('draft-lhu.final.show');
            Route::get('/draft-lhu/{permohonan}/qc-revision', [DraftLhuController::class, 'showQcRevision'])->name('draft-lhu.qc-revision.show');
            Route::post('/draft-lhu/{permohonan}/submit', [DraftLhuController::class, 'submitToQc'])->name('draft-lhu.submit');
            Route::get('/qc-lhu', [QcLhuController::class, 'index'])->name('qc-lhu.index');
            Route::post('/qc-lhu/{permohonan}/submit', [QcLhuController::class, 'submit'])->name('qc-lhu.submit');
            Route::get('/qc-lhu/{permohonan}/final', [QcLhuController::class, 'showFinal'])->name('qc-lhu.final.show');
            Route::get('/qc-lhu/{permohonan}/revision', [QcLhuController::class, 'showRevision'])->name('qc-lhu.revision.show');
            Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
            Route::post('/billing/{permohonan}/upload', [BillingController::class, 'upload'])->name('billing.upload');
            Route::post('/billing/{permohonan}/send', [BillingController::class, 'sendToUser'])->name('billing.send');
            Route::post('/billing/{permohonan}/verify', [BillingController::class, 'verifyPayment'])->name('billing.verify');
            Route::get('/billing/{permohonan}/show', [BillingController::class, 'show'])->name('billing.show');
            Route::get('/billing/{permohonan}/payment-proof', [BillingController::class, 'showPaymentProof'])->name('billing.payment-proof.show');
            Route::get('/suket', [PenerbitanSuketController::class, 'index'])->name('suket.index');
            Route::post('/suket/{permohonan}/upload', [PenerbitanSuketController::class, 'upload'])->name('suket.upload');
            Route::post('/suket/{permohonan}/submit', [PenerbitanSuketController::class, 'submitToPenyerahan'])->name('suket.submit');
            Route::get('/suket/{permohonan}/show', [PenerbitanSuketController::class, 'show'])->name('suket.show');
            Route::get('/ulasan-permohonan', [UlasanPermohonanController::class, 'index'])->name('ulasan-permohonan.index');
            Route::post('/ulasan-permohonan', [UlasanPermohonanController::class, 'store'])->name('ulasan-permohonan.store');
            Route::put('/ulasan-permohonan/{ulasanPermohonanQuestion}', [UlasanPermohonanController::class, 'update'])->name('ulasan-permohonan.update');
            Route::delete('/ulasan-permohonan/{ulasanPermohonanQuestion}', [UlasanPermohonanController::class, 'destroy'])->name('ulasan-permohonan.destroy');
            Route::get('/dokumen-spt', [DokumenSptController::class, 'index'])->name('dokumen-spt.index');
            Route::post('/dokumen-spt/{permohonan}', [DokumenSptController::class, 'store'])->name('dokumen-spt.store');
            Route::get('/dokumen-spt/{permohonan}/preview', [DokumenSptController::class, 'preview'])->name('dokumen-spt.preview');
            Route::post('/dokumen-spt/{permohonan}/signed', [DokumenSptController::class, 'uploadSigned'])->name('dokumen-spt.signed.store');
            Route::post('/dokumen-spt/{permohonan}/send-to-pcu', [DokumenSptController::class, 'sendToPcu'])->name('dokumen-spt.send-to-pcu');
            Route::post('/dokumen-spt/{permohonan}/return-to-penjadwalan', [DokumenSptController::class, 'returnToPenjadwalan'])->name('dokumen-spt.return-to-penjadwalan');
            Route::get('/approval-ma', [MaApprovalController::class, 'index'])->name('approval-ma.index');
            Route::post('/approval-ma/{permohonan}/approve', [MaApprovalController::class, 'approve'])->name('approval-ma.approve');
            Route::post('/approval-ma/{permohonan}/return-to-penjadwalan', [MaApprovalController::class, 'returnToPenjadwalan'])->name('approval-ma.return-to-penjadwalan');
        });

        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/feedback', [SuperadminFeedbackController::class, 'index'])->name('feedback.index');
            Route::post('/feedback/{feedback}/reply', [SuperadminFeedbackController::class, 'reply'])->name('feedback.reply');
        });

        Route::middleware('role:superadmin,mp,mt')->group(function () {
            Route::get('/disposisi', [DisposisiController::class, 'index'])->name('disposisi.index');
            Route::post('/disposisi/{permohonan}/approve', [DisposisiController::class, 'approve'])->name('disposisi.approve');
            Route::get('/disposisi/{permohonan}/customer-document/{type}', [DisposisiController::class, 'showCustomerDocument'])->name('disposisi.customer-document');
        });

        Route::middleware('role:superadmin,mt')->group(function () {
            Route::get('/ttd_lhu', [TtdLhuController::class, 'index'])->name('ttd-lhu.index');
            Route::post('/ttd_lhu/{permohonan}/upload', [TtdLhuController::class, 'uploadSigned'])->name('ttd-lhu.upload');
            Route::post('/ttd_lhu/{permohonan}/submit', [TtdLhuController::class, 'submitToSuratTagihan'])->name('ttd-lhu.submit');
            Route::get('/ttd_lhu/{permohonan}/final', [TtdLhuController::class, 'showFinal'])->name('ttd-lhu.final.show');
            Route::get('/ttd_lhu/{permohonan}/signed', [TtdLhuController::class, 'showSigned'])->name('ttd-lhu.signed.show');
        });

        Route::middleware('role:superadmin,mt')->group(function () {
            Route::get('/kajiulang', [KajiUlangController::class, 'index'])->name('kajiulang.index');
            Route::post('/kajiulang/{permohonan}/approve', [KajiUlangController::class, 'approve'])->name('kajiulang.approve');
            Route::post('/kajiulang/{permohonan}/reject', [KajiUlangController::class, 'reject'])->name('kajiulang.reject');
            Route::post('/kajiulang/{permohonan}/send-penawaran', [KajiUlangController::class, 'sendPenawaran'])->name('kajiulang.send-penawaran');
        });

        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/penawaran', [PenawaranController::class, 'index'])->name('penawaran.index');
            Route::post('/penawaran/{permohonan}/pdf', [PenawaranController::class, 'downloadPdf'])->name('penawaran.pdf');
            Route::post('/penawaran/{permohonan}/send', [PenawaranController::class, 'send'])->name('penawaran.send');
            Route::post('/penawaran/{permohonan}/draft', [PenawaranController::class, 'saveDraft'])->name('penawaran.draft');
            Route::get('/surat-tagihan', [SuratTagihanController::class, 'index'])->name('surat-tagihan.index');
            Route::get('/surat-tagihan/{permohonan}/show', [SuratTagihanController::class, 'show'])->name('surat-tagihan.show');
            Route::post('/surat-tagihan/{permohonan}/submit', [SuratTagihanController::class, 'submitToInvoice'])->name('surat-tagihan.submit');
            Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
            Route::get('/invoice/{permohonan}/show', [InvoiceController::class, 'show'])->name('invoice.show');
            Route::post('/invoice/{permohonan}/submit', [InvoiceController::class, 'submitToBilling'])->name('invoice.submit');
            Route::get('/penyerahan_lhu', [PenyerahanLhuController::class, 'index'])->name('penyerahan-lhu.index');
            Route::post('/penyerahan_lhu/{permohonan}/upload', [PenyerahanLhuController::class, 'uploadRevised'])->name('penyerahan-lhu.upload');
            Route::post('/penyerahan_lhu/{permohonan}/send', [PenyerahanLhuController::class, 'sendToUser'])->name('penyerahan-lhu.send');
            Route::get('/penyerahan_lhu/{permohonan}/signed', [PenyerahanLhuController::class, 'showSigned'])->name('penyerahan-lhu.signed.show');
        });

        Route::middleware('role:superadmin,penyelia')->group(function () {
            Route::get('/penjadwalan', [PenjadwalanController::class, 'index'])->name('penjadwalan.index');
            Route::post('/penjadwalan/{permohonan}/assign', [PenjadwalanController::class, 'assign'])->name('penjadwalan.assign');
            Route::post('/penjadwalan/{permohonan}/send-to-user', [PenjadwalanController::class, 'sendToUser'])->name('penjadwalan.send-to-user');
            Route::post('/penjadwalan/{permohonan}/send-to-spt', [PenjadwalanController::class, 'sendToSpt'])->name('penjadwalan.send-to-spt');
            Route::post('/penjadwalan/{permohonan}/cancel', [PenjadwalanController::class, 'cancel'])->name('penjadwalan.cancel');
        });
    });

Route::middleware(['auth', 'role:pcu'])
    ->prefix('pcu')
    ->name('pcu.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/penjadwalan', [PenjadwalanController::class, 'index'])->name('penjadwalan.index');
        Route::get('/pengujian', [PengujianController::class, 'index'])->name('pengujian.index');
        Route::get('/verifikasi-pcu', [PengujianController::class, 'revisionIndex'])->name('verifikasi-pcu.index');
        Route::post('/pengujian/{permohonan}/draft', [PengujianController::class, 'saveDraft'])->name('pengujian.draft');
        Route::post('/pengujian/{permohonan}/submit', [PengujianController::class, 'submitToBap'])->name('pengujian.submit');
        Route::post('/pengujian/{permohonan}/forward-to-bap', [PengujianController::class, 'forwardToBap'])->name('pengujian.forward-bap');
        Route::post('/pengujian/{permohonan}/return-to-penjadwalan', [PengujianController::class, 'returnToPenjadwalan'])->name('pengujian.return-to-penjadwalan');
        Route::get('/bap', [BapController::class, 'index'])->name('bap.index');
        Route::post('/bap/{permohonan}/draft', [BapController::class, 'saveDraft'])->name('bap.draft');
        Route::post('/bap/{permohonan}/submit', [BapController::class, 'submitToVerifikasi'])->name('bap.submit');
        Route::post('/bap/{permohonan}/mark-viewed', [BapController::class, 'markViewed'])->name('bap.mark-viewed');
        Route::get('/draft-lhu', [DraftLhuController::class, 'index'])->name('draft-lhu.index');
        Route::post('/draft-lhu/{permohonan}/draft', [DraftLhuController::class, 'saveDraft'])->name('draft-lhu.draft');
        Route::get('/draft-lhu/{permohonan}/word', [DraftLhuController::class, 'downloadWord'])->name('draft-lhu.word');
        Route::get('/draft-lhu/{permohonan}/word-all', [DraftLhuController::class, 'downloadAllWord'])->name('draft-lhu.word-all');
        Route::post('/draft-lhu/{permohonan}/ai-summary', [DraftLhuController::class, 'generateAiSummary'])->name('draft-lhu.ai-summary');
        Route::post('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'uploadFinal'])->name('draft-lhu.final.upload');
        Route::get('/draft-lhu/{permohonan}/final', [DraftLhuController::class, 'showFinal'])->name('draft-lhu.final.show');
        Route::get('/draft-lhu/{permohonan}/qc-revision', [DraftLhuController::class, 'showQcRevision'])->name('draft-lhu.qc-revision.show');
        Route::post('/draft-lhu/{permohonan}/submit', [DraftLhuController::class, 'submitToQc'])->name('draft-lhu.submit');
    });

Route::middleware(['auth', 'role:penyelia'])
    ->prefix('penyelia')
    ->name('penyelia.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/penjadwalan', [PenjadwalanController::class, 'index'])->name('penjadwalan.index');
        Route::post('/penjadwalan/{permohonan}/send-to-user', [PenjadwalanController::class, 'sendToUser'])->name('penjadwalan.send-to-user');
        Route::post('/penjadwalan/{permohonan}/send-to-spt', [PenjadwalanController::class, 'sendToSpt'])->name('penjadwalan.send-to-spt');
        Route::post('/penjadwalan/{permohonan}/cancel', [PenjadwalanController::class, 'cancel'])->name('penjadwalan.cancel');
        Route::get('/verifikasi-pengujian', [VerifikasiPengujianController::class, 'index'])->name('verifikasi-pengujian.index');
        Route::post('/verifikasi-pengujian/{permohonan}/submit', [VerifikasiPengujianController::class, 'verify'])->name('verifikasi-pengujian.submit');
    });

Route::middleware(['auth', 'role:ma'])
    ->prefix('ma')
    ->name('ma.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/approval-ma', [MaApprovalController::class, 'index'])->name('approval-ma.index');
        Route::post('/approval-ma/{permohonan}/approve', [MaApprovalController::class, 'approve'])->name('approval-ma.approve');
        Route::post('/approval-ma/{permohonan}/return-to-penjadwalan', [MaApprovalController::class, 'returnToPenjadwalan'])->name('approval-ma.return-to-penjadwalan');
    });

Route::middleware(['auth', 'role:analis'])
    ->prefix('analis')
    ->name('analis.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/prepanalisa', [PrepanalisaController::class, 'index'])->name('prepanalisa.index');
        Route::get('/prepanalisa/formula-references', [PrepanalisaController::class, 'formulaReferences'])->name('prepanalisa.formula-references');
        Route::get('/prepanalisa/riwayat', [PrepanalisaController::class, 'history'])->name('prepanalisa.history');
        Route::get('/prepanalisa/riwayat/export', [PrepanalisaController::class, 'exportHistory'])->name('prepanalisa.history.export');
        Route::get('/prepanalisa/riwayat/hasil/{item}', [PrepanalisaController::class, 'previewHistoryHasil'])->name('prepanalisa.history.hasil');
        Route::get('/prepanalisa/riwayat/hasil/{item}/data', [PrepanalisaController::class, 'historyHasilData'])->name('prepanalisa.history.hasil.data');
        Route::post('/prepanalisa/std-kalibrasi/export-excel', [PrepanalisaController::class, 'exportStdKalibrasiExcel'])->name('prepanalisa.std-kalibrasi.export-excel');
        Route::post('/prepanalisa/{permohonan}/draft', [PrepanalisaController::class, 'saveDraft'])->name('prepanalisa.draft');
        Route::post('/prepanalisa/{permohonan}/assign', [PrepanalisaController::class, 'assign'])->name('prepanalisa.assign');
        Route::post('/prepanalisa/{permohonan}/reset-action', [PrepanalisaController::class, 'resetAction'])->name('prepanalisa.reset-action');
        Route::get('/prepanalisa/penyerahan-preview', [PrepanalisaController::class, 'previewPenyerahan'])->name('prepanalisa.penyerahan-preview');
        Route::post('/prepanalisa/{permohonan}/item-done', [PrepanalisaController::class, 'updateItemDone'])->name('prepanalisa.item-done');
        Route::post('/prepanalisa/submit', [PrepanalisaController::class, 'submitToVerifikasi'])->name('prepanalisa.submit');
    });

Route::middleware(['auth', 'role:qc'])
    ->prefix('qc')
    ->name('qc.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi/submit', [VerifikasiController::class, 'submit'])->name('verifikasi.submit');
        Route::get('/verifikasi/hasil/{item}', [VerifikasiController::class, 'previewHasil'])->name('verifikasi.hasil-preview');
        Route::get('/qc-lhu', [QcLhuController::class, 'index'])->name('qc-lhu.index');
        Route::post('/qc-lhu/{permohonan}/submit', [QcLhuController::class, 'submit'])->name('qc-lhu.submit');
        Route::get('/qc-lhu/{permohonan}/final', [QcLhuController::class, 'showFinal'])->name('qc-lhu.final.show');
        Route::get('/qc-lhu/{permohonan}/revision', [QcLhuController::class, 'showRevision'])->name('qc-lhu.revision.show');
    });
