<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StripeController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BusinessTypeController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\SettingController;
use App\Http\Controllers\Web\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/', [AuthController::class, 'login_form'])->name('login');

Route::post('/login-submit', [AuthController::class, 'login'])->name('login.submit');

Route::middleware(['admin'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/delete/{id}', [UserController::class, 'delete'])->name('users.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::post('/categories/delete/{id}', [CategoryController::class, 'delete'])->name('category.destroy');


    Route::get('/businesstypes', [BusinessTypeController::class, 'index'])->name('businesstypes.index');
    Route::get('/businesstypes/create', [BusinessTypeController::class, 'create'])->name('businesstypes.create');
    Route::post('/businesstypes/store', [BusinessTypeController::class, 'store'])->name('businesstypes.store');
    Route::get('/businesstypes/{id}/edit', [BusinessTypeController::class, 'edit'])->name('businesstypes.edit');
    Route::put('/businesstypes/update/{id}', [BusinessTypeController::class, 'update'])->name('businesstypes.update');
    Route::post('/businesstypes/delete/{id}', [BusinessTypeController::class, 'delete'])->name('businesstypes.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/logo/store', [SettingController::class, 'logo'])->name('settings.logo');
    Route::put('/settings/secscreen/store', [SettingController::class, 'updateSecondSplash'])->name('settings.secscreen');
    Route::put('/settings/busconscreen/store', [SettingController::class, 'updateBusinessConsumerSplash'])->name('settings.busconscreen');
    Route::put('/settings/botd/store', [SettingController::class, 'updatedBotd'])->name('settings.botd');
});

Route::get('/subscribe', [StripeController::class, 'showCheckoutForm'])->name('subscribe.form');
Route::post('/create-subscription', [StripeController::class, 'subscribeWithCard'])->name('subscribe.submit');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
