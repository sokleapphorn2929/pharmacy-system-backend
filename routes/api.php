<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BrandsController;
use App\Http\Controllers\Api\CardsController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\FavouritesController;
use App\Http\Controllers\Api\InvoicesController;
use App\Http\Controllers\Api\OrderItemsController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\UserController;
use App\Models\Categories;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/users', [UserController::class, 'store']);
Route::post('/users/login', [UserController::class, 'login']);
Route::post('/users/verify-otp', [UserController::class, 'verify']);

Route::post('/admins', [AdminController::class, 'store']);
Route::post('/admins/login', [AdminController::class, 'login']);

Route::post('/users/password-request/{id}', [UserController::class, 'requestPasswordUpdate']);
Route::post('/users/password-confirm/{id}', [UserController::class, 'confirmPasswordUpdate']);
Route::post('/users/find-id', [UserController::class, 'findIdByEmail']);

Route::post('/users/delete-request/{id}', [UserController::class, 'initiateDelete']);
Route::delete('/users/confirm-delete/{id}', [UserController::class, 'confirmDelete']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('users')->group(function(){
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::put('/password-with-oldpw/{id}', [UserController::class, 'updatePassword']);
        // Route::post('/password-request/{id}', [UserController::class, 'requestPasswordUpdate']);
        // Route::post('/password-confirm/{id}', [UserController::class, 'confirmPasswordUpdate']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/logout', [UserController::class, 'logout']);
    });

    Route::prefix('admins')->group(function (){
        Route::get('/', [AdminController::class, 'index']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
        Route::post('/logout', [AdminController::class, 'logout']);
    });

    Route::prefix('categories')->group(function (){
        // Route::get('/', [CategoriesController::class, 'index']);
        Route::post('/', [CategoriesController::class, 'store']);
        // Route::get('/{id}', [CategoriesController::class, 'show']);
        Route::put('/{id}', [CategoriesController::class, 'update']);
        Route::delete('/{id}', [CategoriesController::class, 'destroy']);
    });

    Route::prefix('brands')->group(function (){
        // Route::get('/', [BrandsController::class, 'index']);
        Route::post('/', [BrandsController::class, 'store']);
        // Route::get('/{id}', [BrandsController::class, 'show']);
        Route::put('/{id}', [BrandsController::class, 'update']);
        Route::delete('/{id}', [BrandsController::class, 'destroy']);
    });
    
    Route::prefix('products')->group(function (){
        // Route::get('/', [ProductsController::class, 'index']);
        Route::post('/', [ProductsController::class, 'store']);
        // Route::get('/{id}', [ProductsController::class, 'show']);
        Route::put('/{id}', [ProductsController::class, 'update']);
        Route::delete('/{id}', [ProductsController::class, 'destroy']);
    });
    
    Route::prefix('orders')->group(function (){
        // Route::get('/', [OrdersController::class, 'index']);
        Route::post('/', [OrdersController::class, 'store']);
        // Route::get('/{id}', [OrdersController::class, 'show']);
        Route::put('/{id}', [OrdersController::class, 'update']);
        Route::delete('/{id}', [OrdersController::class, 'destroy']);
    });

    Route::prefix('order-items')->group(function (){
        // Route::get('/', [OrderItemsController::class, 'index']);
        Route::post('/', [OrderItemsController::class, 'store']);
        // Route::get('/{id}', [OrderItemsController::class, 'show']);
        Route::put('/{id}', [OrderItemsController::class, 'update']);
        Route::delete('/{id}', [OrderItemsController::class, 'destroy']);
    });

    Route::prefix('payments')->group(function (){
        Route::get('/', [PaymentsController::class, 'index']);
        Route::post('/', [PaymentsController::class, 'store']);
        Route::get('/{id}', [PaymentsController::class, 'show']);
        Route::put('/{id}', [PaymentsController::class, 'update']);
        Route::delete('/{id}', [PaymentsController::class, 'destroy']);
    });

    Route::prefix('invoices')->group(function (){
        Route::get('/', [InvoicesController::class, 'index']);
        Route::post('/', [InvoicesController::class, 'store']);
        Route::get('/{id}', [InvoicesController::class, 'show']);
        Route::put('/{id}', [InvoicesController::class, 'update']);
        Route::delete('/{id}', [InvoicesController::class, 'destroy']);
    });

    Route::prefix('favourites')->group(function (){
        Route::get('/', [FavouritesController::class, 'index']);
        Route::post('/', [FavouritesController::class, 'store']);
        Route::get('/{id}', [FavouritesController::class, 'show']);
        Route::put('/{id}', [FavouritesController::class, 'update']);
        Route::delete('/{id}', [FavouritesController::class, 'destroy']);
    });

    Route::prefix('cards')->group(function (){
        Route::get('/', [CardsController::class, 'index']);
        Route::post('/', [CardsController::class, 'store']);
        Route::get('/{id}', [CardsController::class, 'show']);
        Route::put('/{id}', [CardsController::class, 'update']);
        Route::delete('/{id}', [CardsController::class, 'destroy']);
    });
});

Route::prefix('categories')->group(function (){
    Route::get('/', [CategoriesController::class, 'index']);
    Route::get('/{id}', [CategoriesController::class, 'show']);
});

Route::prefix('brands')->group(function (){
    Route::get('/', [BrandsController::class, 'index']);
    Route::get('/{id}', [BrandsController::class, 'show']);
});

Route::prefix('products')->group(function (){
    Route::get('/', [ProductsController::class, 'index']);
    Route::get('/{id}', [ProductsController::class, 'show']);
});

Route::prefix('orders')->group(function (){
    Route::get('/', [OrdersController::class, 'index']);
    Route::get('/{id}', [OrdersController::class, 'show']);
});

Route::prefix('order-items')->group(function (){
    Route::get('/', [OrderItemsController::class, 'index']);
    Route::get('/{id}', [OrderItemsController::class, 'show']);
});