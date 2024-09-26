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
use App\Http\Controllers\FeeController;
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

$UserAccount = 1;
$Libraries = 2;
$Customers = 3;
$Employee = 4;
$FactorRate = 5;
$PaymentDuration = 6;
$PaymentFrequency = 7;

$create = 'create';
$view = 'view';
$delete = 'delete';
$update = 'update';

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/createUser', [UserController::class, 'store'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('libraries')->group(function ()  use ($Libraries)  {
    Route::get('/{modeltype}', [DBLibraryController::class, 'index'])->middleware("document_access:$Libraries, view");
    Route::get('/findOne/{id}', [DBLibraryController::class, 'show'])->middleware("document_access:$Libraries, view");
    Route::post('/', [DBLibraryController::class, 'store'])->middleware("document_access:$Libraries, create");
    Route::put('/{id}', [DBLibraryController::class, 'update'])->middleware("document_access:$Libraries, update");
    Route::delete('/{id}', [DBLibraryController::class, 'destroy'])->middleware("document_access:$Libraries, delete");
});

// Employee routes with authentication and document access
Route::middleware('auth:sanctum')->prefix('employees')->group(function () use($Employee) {
    Route::get('/', [EmployeePersonalityController::class, 'index'])->middleware("document_access:$Employee, view");// Apply document access only to this route
    Route::get('/{id}', [EmployeePersonalityController::class, 'show'])->middleware("document_access:$Employee, view");
    Route::post('/', [EmployeePersonalityController::class, 'store'])->middleware("document_access:$Employee, create"); // Adjust as necessary
    Route::put('/{id}', [EmployeePersonalityController::class, 'update'])->middleware("document_access:$Employee, update"); // Dynamic ID
    Route::delete('/{id}', [EmployeePersonalityController::class, 'destroy'])->middleware("document_access:$Employee, delete"); // Dynamic ID
});

Route::middleware('auth:sanctum')->prefix('customers')->group(function () use ($Customers) {
    Route::get('/', [CustomerPersonalityController::class, 'index'])->middleware("document_access:$Customers, view");
    Route::get('/{id}', [CustomerPersonalityController::class, 'show'])->middleware("document_access:$Customers, view");
    Route::post('/', [CustomerPersonalityController::class, 'store'])->middleware("document_access:$Customers, create");
    Route::put('/{id}', [CustomerPersonalityController::class,'update'])->middleware("document_access:$Customers, update");
    Route::delete('/{id}', [CustomerPersonalityController::class, 'destroy'])->middleware("document_access:$Customers, delete");
});

Route::middleware('auth:sanctum')->prefix('personalities')->group(function () {
    Route::get('/', [PersonalityController::class, 'index']);
    Route::get('/{id}', [PersonalityController::class,'show']);
    Route::post('/', [PersonalityController::class, 'store']);
    Route::put('/{id}', [PersonalityController::class,'update']);
    Route::delete('/{id}', [PersonalityController::class,'destroy']);
});
        /////////--------------------MEDYO LIBOG DRI NA PART SAS  kay permission ang prefix sa documap
                Route::middleware('auth:sanctum')->prefix('permission')->group(function () {
                    Route::get('/', [DocumentPermissionMapController::class, 'index'])->middleware('document_access');
                    Route::get('/{id}', [DocumentPermissionMapController::class,'show'])->middleware('document_access');
                    Route::post('/', [DocumentPermissionMapController::class, 'store'])->middleware('document_access');
                    Route::put('/{id}', [DocumentPermissionMapController::class,'update'])->middleware('document_access');
                    Route::delete('/{id}', [DocumentPermissionMapController::class,'destroy'])->middleware('document_access');
                });

                Route::middleware('auth:sanctum')->prefix('documentMap')->group(function () {
                    Route::get('/', [DocumentMapController::class, 'index'])->middleware('document_access');
                    Route::get('/{id}', [DocumentMapController::class, 'show'])->middleware('document_access');
                    Route::post('/', [DocumentMapController::class, 'store'])->middleware('document_access');
                    Route::put('/{id}', [DocumentMapController::class, 'update'])->middleware('document_access');
                    Route::delete('/{id}', [DocumentMapController::class, 'destroy'])->middleware('document_access');
                });
        ////////-------------------- tas sa documap documap

                Route::middleware('auth:sanctum')->prefix('documentpermission')->group(function () {
                    Route::get('/', [DocumentPermissionController::class, 'index'])->middleware('document_access');
                    Route::get('/{id}', [DocumentPermissionController::class, 'show'])->middleware('document_access');
                    Route::post('/', [DocumentPermissionController::class, 'store'])->middleware('document_access');
                    Route::put('/{id}', [DocumentPermissionController::class, 'update'])->middleware('document_access');
                    Route::delete('/{id}', [DocumentPermissionController::class, 'destroy'])->middleware('document_access');
                });


Route::middleware('auth:sanctum')->prefix('loancount')->group(function () {
    Route::get('/', [LoanCountController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [LoanCountController::class, 'show'])->middleware('document_access');
    Route::post('/', [LoanCountController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [LoanCountController::class,'update'])->middleware('document_access');
    Route::delete('/{id}', [LoanCountController::class, 'destroy'])->middleware('document_access');
});

Route::middleware('auth:sanctum')->prefix('factorRate')->group(function () {
    Route::get('/', [FactorRateController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [FactorRateController::class, 'show'])->middleware('document_access');
    Route::post('/', [FactorRateController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [FactorRateController::class, 'update'])->middleware('document_access');
});

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

Route::middleware('auth:sanctum')->prefix('fee')->group(function () {
    Route::get('/', [FeeController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [FeeController::class, 'show'])->middleware('document_access');
    Route::post('/', [FeeController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [FeeController::class, 'update'])->middleware('document_access');
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [UserController::class, 'show'])->middleware('document_access');
    Route::post('/', [UserController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [UserController::class, 'update'])->middleware('document_access');
});

Route::middleware('auth:sanctum')->prefix('group')->group(function () {
    Route::get('/', [CustomerGroupController::class, 'index'])->middleware('document_access');
    Route::get('/{id}', [CustomerGroupController::class, 'show'])->middleware('document_access');
    Route::post('/', [CustomerGroupController::class, 'store'])->middleware('document_access');
    Route::put('/{id}', [CustomerGroupController::class, 'update'])->middleware('document_access');
});


    Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
        Route::post('/', function () {
            // You are authenticated, return a response or perform logic here
            return response()->json([
                'message' => 'Authenticated!', // Optionally, return the authenticated user data
            ]);
        })->middleware('document_access');
    });

    Route::middleware('auth:sanctum')->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
    });
    
    Route::get('/emplooyeid', [EmployeeController::class, 'findEmpIDnotExist']); //user creation
    Route::get('libraries/customer_group', [CustomerGroupController::class, 'index'])->middleware('document_access');
