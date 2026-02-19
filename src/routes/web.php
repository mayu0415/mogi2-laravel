<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;

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

// 一覧（トップ）
Route::get('/', [ItemController::class, 'index'])->name('index');

// 詳細
Route::get('/item/{item}', [ItemController::class, 'show'])->name('item');

//いいね
Route::post('/item/{item}', [ItemController::class, 'favorite'])->name('item.favorite');

// 出品
Route::get('/sell', [ItemController::class, 'create'])->middleware('auth')->name('sell');
Route::post('/sell', [ItemController::class, 'store'])->middleware('auth')->name('sell.store');

// 購入
Route::get('/purchase/{item}', [OrderController::class, 'confirm'])->name('purchase');

// 住所変更
Route::get('/purchase/address/{item}', [OrderController::class, 'editAddress'])->name('address');

// マイページ
Route::get('/mypage', [ProfileController::class, 'index'])->middleware('auth')->name('mypage');

// プロフィール編集
Route::get('/mypage/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('profile.edit');
Route::post('/mypage/profile', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');

