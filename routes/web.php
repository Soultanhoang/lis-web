<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TestTypeController;
use App\Http\Controllers\TestRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestResultController;
use App\Http\Controllers\SampleController;


Auth::routes(['register' => false]);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Routes: middleware auth
Route::middleware(['auth'])->group(function () {
    // Cụ thể cho Bác sĩ & Admin
    Route::middleware(['can:is-doctor-or-admin'])->group(function () {
        
        Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');

        Route::get('/test_types/search', [TestTypeController::class, 'search'])->name('test_types.search');
    });

    // Cụ thể cho Bác sĩ
    Route::middleware(['can:is-doctor'])->group(function () {
        Route::get('/test_requests/search', [TestRequestController::class, 'search'])->name('test_requests.search');

        Route::get('test-types/get-by-category', [TestTypeController::class, 'getByCategory'])->name('test_types.get_by_category');
    });

    // Cụ thể cho Admin
    Route::middleware(['can:is-admin'])->group(function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::get('/users/{user}/create', [UserController::class, 'create'])->name('users.create');

        Route::put('/users/{user}', [UserController::class, 'store'])->name('users.store');

        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Cụ thể cho Kỹ thuật viên (Technician)
    Route::middleware(['auth', 'can:is-technician'])->group(function () {

    // 1. Trang danh sách chờ lấy mẫu
    Route::prefix('samples')->name('samples.')->group(function () {
        
        // 1. Trang danh sách phiếu chờ lấy mẫu
        Route::get('/', [SampleController::class, 'index'])->name('index');

        // 2. Trang giao diện thực hiện lấy mẫu (Form xác nhận)
        Route::get('/create/{test_request_id}', [SampleController::class, 'create'])->name('create');

        // 3. Xử lý lưu xác nhận đã lấy mẫu (Submit Form)
        Route::post('/store', [SampleController::class, 'store'])->name('store');

       Route::get('/print-label/{sample}', [SampleController::class, 'printLabel'])->name('print_label');
    });

        Route::prefix('test-results')->name('test_results.')->middleware('auth')->group(function () {
        // Trang danh sách chờ nhập KQ
        Route::get('/', [TestResultController::class, 'index'])->name('index');

        // Trang giao diện nhập kết quả (sẽ làm ở bước sau)
        Route::get('/enter/{test_request_id}', [TestResultController::class, 'enterResults'])->name('enter');
        
        // Lưu kết quả (sẽ làm ở bước sau)
        Route::post('/update/{test_request_id}', [TestResultController::class, 'updateResults'])->name('update');

        // Danh sách đã hoàn thành
        Route::get('/completed', [TestResultController::class, 'completedList'])->name('completed');
        
        // Route In phiếu kết quả (PDF)
        Route::get('/print/{test_request_id}', [TestResultController::class, 'printResult'])->name('print');
    });

    Route::get('/test-results/latest/{test_request_id}', [TestResultController::class, 'getLatestResults'])
     ->name('test_results.latest');
    
    });
    

    // Route::resource 
    Route::middleware(['can:is-admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    // (Resource cho 'patients'...)
    Route::middleware(['can:is-doctor-or-admin'])->group(function () {
        Route::resource('patients', PatientController::class);
    });

    // (Resource cho 'test_types'...)
    Route::middleware(['can:is-admin'])->group(function () {
        Route::resource('test_types', TestTypeController::class);
    });
    
    // (Resource cho 'test_requests'...)
    Route::middleware(['can:is-doctor'])->group(function () {
        Route::resource('test_requests', TestRequestController::class)->only([
            'index', 'create', 'store', 'show'
        ]);
    });
});