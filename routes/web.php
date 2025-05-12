<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColourController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\ClaimReviewController;
use App\Http\Controllers\ClaimHistoryController;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware('guest')->group(function(){
    Route::get('/',function(){return redirect()->route('login');});
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout',[LoginController::class,'logout'])->name('logout');
Route::get('/dashboard',function(){
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/reset-password/{id}/{hash}', [PasswordResetController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->name('password.update');

// Add password request routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
    ->name('password.request');


Route::prefix('/students')->as('students.')->middleware('auth')->group(function(){
    $controller = StudentController::class;
    Route::get('/index',[$controller,'index'])->name('index');
    Route::delete('/delete/{id}',[$controller,'destroy'])->name('destroy');
});

Route::prefix('/category')->as('category.')->middleware('auth')->group(function(){
    $controller = CategoryController::class;
    Route::get('/index',[$controller,'index'])->name('index');
    Route::post('/store',[$controller,'store'])->name('store');
    Route::post('/update/{id}',[$controller,'update'])->name('update');
    Route::delete('/delete/{id}',[$controller,'destroy'])->name('destroy');
});

Route::prefix('/colour')->as('colour.')->middleware('auth')->group(function(){
    $controller = ColourController::class;
    Route::get('/index',[$controller,'index'])->name('index');
    Route::post('/store',[$controller,'store'])->name('store');
    Route::post('/update/{id}',[$controller,'update'])->name('update');
    Route::delete('/delete/{id}',[$controller,'destroy'])->name('destroy');
});

Route::prefix('/location')->as('location.')->middleware('auth')->group(function(){
    $controller = LocationController::class;
    Route::get('/index',[$controller,'index'])->name('index');
    Route::post('/store',[$controller,'store'])->name('store');
    Route::post('/update/{id}',[$controller,'update'])->name('update');
    Route::delete('/delete/{id}',[$controller,'destroy'])->name('destroy');
});

Route::prefix('/claim')->as('claim.')->middleware('auth')->group(function(){
    $controller = ClaimReviewController::class;
    Route::get('/index',[$controller,'index'] )->name('index');
    Route::get('/review/{id}',[$controller,'review'])->name('review');
    Route::get('/comparison/{claimId}',[$controller,'getComparisonData'])->name('comparison');
    Route::post('/reject',[$controller,'rejectClaim'])->name('reject');
    Route::post('/accept',[$controller,'acceptClaim'])->name('accept');
});

Route::prefix('/claim-history')->as('claim-history.')->middleware('auth')->group(function(){
    $controller = ClaimHistoryController::class;
    Route::get('index',[$controller,'index'])->name('index');
    Route::get('view/{id}',[$controller,'view'])->name('view');
});
