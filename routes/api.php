<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerPersonalityController;
use App\Http\Controllers\DBLibraryController;
use App\Http\Controllers\DocumentMapController;
use App\Http\Controllers\DocumentPermissionController;
use App\Http\Controllers\DocumentPermissionMapController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePersonalityController;
use App\Http\Controllers\FactorRateController;
use App\Http\Controllers\LoanCountController;
use App\Http\Controllers\PaymentDurationController;
use App\Http\Controllers\PaymentFrequencyController;
use App\Http\Controllers\PersonalityController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserController2;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/createUser', [UserController::class, 'store'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('libraries')->group(function () {
    Route::get('/{modeltype}', [DBLibraryController::class, 'index'])->middleware('document_access:2, view');
    Route::get('/findOne/{id}', [DBLibraryController::class, 'show'])->middleware('document_access:2, view');
    Route::post('/', [DBLibraryController::class, 'store'])->middleware('document_access:2, create');
    Route::put('/{id}', [DBLibraryController::class, 'update'])->middleware('document_access:2, update');
    Route::delete('/{id}', [DBLibraryController::class, 'destroy'])->middleware('document_access:2, delete');
});

Route::middleware('auth:sanctum')->prefix('personalities')->group(function () {
    Route::get('/', [PersonalityController::class, 'index']);
    Route::get('/{id}', [PersonalityController::class,'show']);
    Route::post('/', [PersonalityController::class, 'store']);
    Route::put('/{id}', [PersonalityController::class,'update']);
    Route::delete('/{id}', [PersonalityController::class,'destroy']);
});

// Employee routes with authentication and document access
Route::middleware('auth:sanctum')->prefix('employees')->group(function () {
    Route::get('/', [EmployeePersonalityController::class, 'index'])->middleware('document_access:4, view');// Apply document access only to this route
    Route::get('/{id}', [EmployeePersonalityController::class, 'show'])->middleware('document_access:4, view');
    Route::post('/', [EmployeePersonalityController::class, 'store'])->middleware('document_access:4, create'); // Adjust as necessary
    Route::put('/{id}', [EmployeePersonalityController::class, 'update'])->middleware('document_access:4, update'); // Dynamic ID
    Route::delete('/{id}', [EmployeePersonalityController::class, 'destroy'])->middleware('document_access:4, delete'); // Dynamic ID
});


Route::middleware('auth:sanctum')->prefix('customers')->group(function () {
    Route::get('/', [CustomerPersonalityController::class, 'index'])->middleware('document_access:3, view');
    Route::get('/{id}', [CustomerPersonalityController::class, 'show'])->middleware('document_access:3, view');
    Route::post('/', [CustomerPersonalityController::class, 'store'])->middleware('document_access:3, create');
    Route::put('/{id}', [CustomerPersonalityController::class,'update'])->middleware('document_access:3, update');
    Route::delete('/{id}', [CustomerPersonalityController::class, 'destroy'])->middleware('document_access:3, delete');
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

Route::get('/emplooyeid', [EmployeeController::class, 'findEmpIDnotExist']);
Route::get('get-user', [UserController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('loancount')->group(function () {
    Route::get('/', [LoanCountController::class, 'index'])->middleware('document_access:3, view');
    Route::get('/{id}', [LoanCountController::class, 'show'])->middleware('document_access:3, view');
    Route::post('/', [LoanCountController::class, 'store'])->middleware('document_access:3, create');
    Route::put('/{id}', [LoanCountController::class,'update'])->middleware('document_access:3, update');
    Route::delete('/{id}', [LoanCountController::class, 'destroy'])->middleware('document_access:3, delete');
});


Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
});

//Route::post('factorRate', [FactorRateController::class, 'store'])->middleware('document_access:5,create'); //five (5) means its factorate that should be access by the user and has should be create permission

Route::middleware('auth:sanctum')->prefix('factorRate')->group(function () {
    Route::get('/', [FactorRateController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [FactorRateController::class, 'show'])->middleware('document_access');
    Route::post('/', [FactorRateController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [FactorRateController::class, 'update'])->middleware('document_access');
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/', function () {
        // You are authenticated, return a response or perform logic here
        return response()->json([
            'message' => 'Authenticated!', // Optionally, return the authenticated user data
        ]);
    })->middleware('document_access');
});


// Route::get('frequency', [PaymentFrequencyController::class, 'index']);
// Route::post('frequency', [PaymentFrequencyController::class, 'store']);
// Route::put('/frequency/{id}', [PaymentFrequencyController::class, 'update']);

Route::middleware('auth:sanctum')->prefix('duration')->group(function () {
    Route::get('/', [PaymentDurationController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [PaymentDurationController::class, 'show'])->middleware('document_access');
    Route::post('/', [PaymentDurationController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [PaymentDurationController::class, 'update'])->middleware('document_access');
});

Route::middleware('auth:sanctum')->prefix('frequency')->group(function () {
    Route::get('/', [PaymentFrequencyController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [PaymentFrequencyController::class, 'show'])->middleware('document_access');
    Route::post('/', [PaymentFrequencyController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [PaymentFrequencyController::class, 'update'])->middleware('document_access');
});

Route::get('group', [CustomerGroupController::class, 'index']);



// Route::prefix('users')->group(function () {
//     Route::get('/', [UserController::class, 'index']);
//     Route::get('/{id}', [UserController::class, 'show']);
//     Route::post('/', [UserController::class, 'store']);
//     Route::put('/{id}', [UserController::class,'update']);
//     Route::delete('/{id}', [UserController::class, 'destroy']);
// });
