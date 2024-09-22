<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPersonalityController;
use App\Http\Controllers\DBLibraryController;
use App\Http\Controllers\DocumentMapController;
use App\Http\Controllers\DocumentPermissionController;
use App\Http\Controllers\DocumentPermissionMapController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePersonalityController;
use App\Http\Controllers\PersonalityController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\User_Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//registration
Route::post('/register', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->prefix('libraries')->group(function () {
    Route::get('/{modeltype}', [DBLibraryController::class, 'index']);
    Route::get('/findOne/{id}', [DBLibraryController::class, 'show']);
    Route::post('/', [DBLibraryController::class, 'store']);
    Route::put('/{id}', [DBLibraryController::class, 'update']);
    Route::delete('/{id}', [DBLibraryController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('personalities')->group(function () {
    Route::get('/', [PersonalityController::class, 'index']);
    Route::get('/{id}', [PersonalityController::class,'show']);
    Route::post('/', [PersonalityController::class, 'store']);
    Route::put('/{id}', [PersonalityController::class,'update']);
    Route::delete('/{id}', [PersonalityController::class,'destroy']);
});

Route::middleware('auth:sanctum')->prefix('employees')->group(function () {
    Route::get('/', [EmployeePersonalityController::class, 'index']);
    Route::get('/{id}', [EmployeePersonalityController::class,'show']);
    Route::post('/', [EmployeePersonalityController::class, 'store']);
    Route::put('/{id}', [EmployeePersonalityController::class,'update']);
    Route::delete('/{id}', [EmployeePersonalityController::class,'destroy']);
});

Route::middleware('auth:sanctum')->prefix('customers')->group(function () {
    Route::get('/', [CustomerPersonalityController::class, 'index']);
    Route::get('/{id}', [CustomerPersonalityController::class, 'show']);
    Route::post('/', [CustomerPersonalityController::class, 'store']);
    Route::put('/{id}', [CustomerPersonalityController::class,'update']);
    Route::delete('/{id}', [CustomerPersonalityController::class, 'destroy']);
});


Route::get('/permission', [DocumentPermissionMapController::class, 'index']);
Route::get('/documentMap', [DocumentMapController::class, 'index']);
Route::get('/documentpermission', [DocumentPermissionController::class, 'index']);
Route::get('/documentpermission/{id}', [DocumentPermissionController::class, 'show']);
Route::post('/documentpermission', [DocumentPermissionController::class, 'store']);
Route::put('/documentpermission/{id}', [DocumentPermissionController::class, 'update']);
Route::delete('/documentpermission/{id}', [DocumentPermissionController::class, 'destroy']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
});


// Route::prefix('users')->group(function () {
//     Route::get('/', [UserController::class, 'index']);
//     Route::get('/{id}', [UserController::class, 'show']);
//     Route::post('/', [UserController::class, 'store']);
//     Route::put('/{id}', [UserController::class,'update']);
//     Route::delete('/{id}', [UserController::class, 'destroy']);
// });
