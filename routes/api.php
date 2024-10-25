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
use App\Http\Controllers\LoanApplicationCoMakerController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\LoanCountController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentDurationController;
use App\Http\Controllers\PaymentFrequencyController;
use App\Http\Controllers\PaymentLineController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\PersonalityController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserController2;
use App\Models\AuthPermission;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Initialized
if (AuthPermission::initialize())
{

// Define permission constants
$LIBRARIES = AuthPermission::LIBRARIES();
$USER_ACCOUNTS = AuthPermission::USER_ACCOUNTS();
$CUSTOMERS = AuthPermission::CUSTOMERS();
$CUSTOMER_GROUPS = AuthPermission::CUSTOMER_GROUPS();
$EMPLOYEES = AuthPermission::EMPLOYEES();
$FACTORRATES = AuthPermission::FACTOR_RATES();
$PAYMENT_DURATIONS = AuthPermission::PAYMENT_DURATIONS();
$PAYMENT_FREQUENCIES = AuthPermission::PAYMENT_FREQUENCIES();
$DOCUMENT_PERMISSIONS = AuthPermission::DOCUMENT_PERMISSIONS();
$DOCUMENT_MAPS = AuthPermission::DOCUMENT_MAPS();
$DOCUMENT_MAP_PERMISSIONS = AuthPermission::DOCUMENT_MAP_PERMISSIONS();
$LOAN_COUNTS = AuthPermission::LOAN_COUNTS();
$FEES = AuthPermission::FEES();
$PERSONALITIES = AuthPermission::PERSONALITIES();
$BUTTON_AUTHORIZATIONS = AuthPermission::BUTTON_AUTHORIZATIONS();
$LOAN_APPLICATIONS = AuthPermission::LOAN_APPLICATIONS();
$LOAN_APPLICATION_COMAKERS = AuthPermission::LOAN_APPLICATION_COMAKERS();

$VIEW = AuthPermission::VIEW_PERM();
$CREATE = AuthPermission::CREATE_PERM();
$UPDATE = AuthPermission::UPDATE_PERM();
$DELETE = AuthPermission::DELETE_PERM();


// User Auth routes
Route::middleware('auth:sanctum')->prefix('USER_AUTH')->group(function () use ($BUTTON_AUTHORIZATIONS, $CREATE, $UPDATE) {
    Route::post('/', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$BUTTON_AUTHORIZATIONS, $CREATE");

    Route::put('/', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$BUTTON_AUTHORIZATIONS, $UPDATE");

    Route::patch('/', function () {
        return response()->json(['message' => 'Checking authentication']);
    });
});

// User Auth routes
Route::middleware('auth:sanctum')->prefix('USER_AUTH')->group(function () use ($BUTTON_AUTHORIZATIONS, $CREATE, $UPDATE) {
    Route::post('/', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$BUTTON_AUTHORIZATIONS, $CREATE");

    Route::put('/', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$BUTTON_AUTHORIZATIONS, $UPDATE");
});

// Users routes
Route::middleware('auth:sanctum')->prefix('USERS')->group(function () use ($USER_ACCOUNTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [UserController::class, 'index'])->middleware("document_access:$USER_ACCOUNTS, $VIEW");
    Route::get('/NoUSERID', [UserController::class, '']);
    Route::get('/{id}', [UserController::class, 'show'])->middleware("document_access:$USER_ACCOUNTS, $VIEW");
    Route::post('/', [UserController::class, 'store'])->middleware("document_access:$USER_ACCOUNTS, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$USER_ACCOUNTS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$USER_ACCOUNTS, $CREATE");

    Route::put('/{id}', [UserController::class, 'update'])->middleware("document_access:$USER_ACCOUNTS, $UPDATE");
    Route::patch('/{id}', [UserController::class, 'update'])->middleware("document_access:$USER_ACCOUNTS, $UPDATE"); // PATCH method
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware("document_access:$USER_ACCOUNTS, $DELETE");
});

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/createUser', [UserController::class, 'store'])->middleware('auth:sanctum');

// Libraries routes
Route::middleware('auth:sanctum')->prefix('LIBRARIES')->group(function () use ($LIBRARIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/{modeltype}', [DBLibraryController::class, 'index'])->middleware("document_access:$LIBRARIES, $VIEW");
    Route::get('/NoAUTH/{modeltype}', [DBLibraryController::class, 'index']);
    Route::get('/NoAUTH/findOne/{id}', [DBLibraryController::class, 'show']);
    Route::get('/findOne/{id}', [DBLibraryController::class, 'show'])->middleware("document_access:$LIBRARIES, $VIEW");
    Route::post('/', [DBLibraryController::class, 'store'])->middleware("document_access:$LIBRARIES, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LIBRARIES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LIBRARIES, $CREATE");

    //Empty view route with PATCH with no docoument middle for viewing only in other form
    Route::patch('/{modeltype}', [DBLibraryController::class, 'index']);

    Route::put('/{id}', [DBLibraryController::class, 'update'])->middleware("document_access:$LIBRARIES, $UPDATE");
    Route::patch('/{id}', [DBLibraryController::class, 'update'])->middleware("document_access:$LIBRARIES, $UPDATE"); // PATCH method
    Route::delete('/{id}', [DBLibraryController::class, 'destroy'])->middleware("document_access:$LIBRARIES, $DELETE");
});

// Employee routes
Route::middleware('auth:sanctum')->prefix('EMPLOYEES')->group(function () use ($EMPLOYEES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [EmployeePersonalityController::class, 'index'])->middleware("document_access:$EMPLOYEES, $VIEW");
    Route::get('/NoUser', [EmployeePersonalityController::class, 'look'])->middleware("document_access:$EMPLOYEES, $VIEW");
    Route::get('/NoAUTH/findMany/', [EmployeePersonalityController::class, 'index']);
    Route::get('/NoAUTH/find/{id}', [EmployeePersonalityController::class, 'show']);
    Route::get('/NoAUTH/findMany/findNoUser/', [EmployeePersonalityController::class, 'look']);
    Route::get('/{id}', [EmployeePersonalityController::class, 'show'])->middleware("document_access:$EMPLOYEES, $VIEW");
    Route::post('/', [EmployeePersonalityController::class, 'store'])->middleware("document_access:$EMPLOYEES, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$EMPLOYEES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$EMPLOYEES, $CREATE");

    Route::put('/{id}', [EmployeePersonalityController::class, 'update'])->middleware("document_access:$EMPLOYEES, $UPDATE");
    Route::patch('/{id}', [EmployeePersonalityController::class, 'update'])->middleware("document_access:$EMPLOYEES, $UPDATE"); // PATCH method
    Route::delete('/{id}', [EmployeePersonalityController::class, 'destroy'])->middleware("document_access:$EMPLOYEES, $DELETE");
});

// Customers routes
Route::middleware('auth:sanctum')->prefix('CUSTOMERS')->group(function () use ($CUSTOMERS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [CustomerPersonalityController::class, 'index'])->middleware("document_access:$CUSTOMERS, $VIEW");
    Route::get('/NoAUTH', [CustomerPersonalityController::class, 'index']);
    Route::get('/NoAUTH/{id}', [CustomerPersonalityController::class, 'show']);
    Route::get('/{id}', [CustomerPersonalityController::class, 'show'])->middleware("document_access:$CUSTOMERS, $VIEW");
    Route::post('/', [CustomerPersonalityController::class, 'store'])->middleware("document_access:$CUSTOMERS, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMERS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMERS, $CREATE");

    Route::put('/{id}', [CustomerPersonalityController::class, 'update'])->middleware("document_access:$CUSTOMERS, $UPDATE");
    Route::patch('/{id}', [CustomerPersonalityController::class, 'update'])->middleware("document_access:$CUSTOMERS, $UPDATE"); // PATCH method
    Route::delete('/{id}', [CustomerPersonalityController::class, 'destroy'])->middleware("document_access:$CUSTOMERS, $DELETE");
});

// Personalities routes
Route::middleware('auth:sanctum')->prefix('PERSONALITIES')->group(function () use ($PERSONALITIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PersonalityController::class, 'index'])->middleware("document_access:$PERSONALITIES, $VIEW");
    Route::get('/NoAUTH', [PersonalityController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PersonalityController::class, 'show']);
    Route::get('/{id}', [PersonalityController::class, 'show'])->middleware("document_access:$PERSONALITIES, $VIEW");
    Route::post('/', [PersonalityController::class, 'store'])->middleware("document_access:$PERSONALITIES, $CREATE");
    Route::put('/{id}', [PersonalityController::class, 'update'])->middleware("document_access:$PERSONALITIES, $UPDATE");
    Route::delete('/{id}', [PersonalityController::class, 'destroy'])->middleware("document_access:$PERSONALITIES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PERSONALITIES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PERSONALITIES, $CREATE");
});

// Document Map Permission routes
Route::middleware('auth:sanctum')->prefix('DOCUMENT_MAP_PERMISSIONS')->group(function () use ($DOCUMENT_MAP_PERMISSIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [DocumentPermissionMapController::class, 'index'])->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $VIEW");
    Route::get('/NoAUTH', [DocumentPermissionMapController::class, 'index']);
    Route::get('/NoAUTH/{id}', [DocumentPermissionMapController::class, 'show']);
    Route::get('/{id}', [DocumentPermissionMapController::class, 'show'])->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $VIEW");
    Route::post('/', [DocumentPermissionMapController::class, 'store'])->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $CREATE");
    Route::put('/{id}', [DocumentPermissionMapController::class, 'update'])->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $UPDATE");
    Route::delete('/{id}', [DocumentPermissionMapController::class, 'destroy'])->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $CREATE");
});

// Document Map routes
Route::middleware('auth:sanctum')->prefix('DOCUMENT_MAPS')->group(function () use ($DOCUMENT_MAPS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [DocumentMapController::class, 'index'])->middleware("document_access:$DOCUMENT_MAPS, $VIEW");
    Route::get('/NoAUTH', [DocumentMapController::class, 'index']);
    Route::get('/NoAUTH/{id}', [DocumentMapController::class, 'show']);
    Route::get('/{id}', [DocumentMapController::class, 'show'])->middleware("document_access:$DOCUMENT_MAPS, $VIEW");
    Route::post('/', [DocumentMapController::class, 'store'])->middleware("document_access:$DOCUMENT_MAPS, $CREATE");
    Route::put('/{id}', [DocumentMapController::class, 'update'])->middleware("document_access:$DOCUMENT_MAPS, $UPDATE");
    Route::delete('/{id}', [DocumentMapController::class, 'destroy'])->middleware("document_access:$DOCUMENT_MAPS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAPS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAPS, $CREATE");
});

// Document Permission routes
Route::middleware('auth:sanctum')->prefix('DOCUMENT_PERMISSIONS')->group(function () use ($DOCUMENT_PERMISSIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [DocumentPermissionController::class, 'index'])->middleware("document_access:$DOCUMENT_PERMISSIONS, $VIEW");
    Route::get('/NoAUTH', [DocumentPermissionController::class, 'index']);
    Route::get('/NoAUTH/{id}', [DocumentPermissionController::class, 'show']);
    Route::get('/{id}', [DocumentPermissionController::class, 'show'])->middleware("document_access:$DOCUMENT_PERMISSIONS, $VIEW");
    Route::post('/', [DocumentPermissionController::class, 'store'])->middleware("document_access:$DOCUMENT_PERMISSIONS, $CREATE");
    Route::put('/{id}', [DocumentPermissionController::class, 'update'])->middleware("document_access:$DOCUMENT_PERMISSIONS, $UPDATE");
    Route::delete('/{id}', [DocumentPermissionController::class, 'destroy'])->middleware("document_access:$DOCUMENT_PERMISSIONS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_PERMISSIONS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_PERMISSIONS, $CREATE");
});

// Loan Count routes
Route::middleware('auth:sanctum')->prefix('LOAN_COUNTS')->group(function () use ($LOAN_COUNTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [LoanCountController::class, 'index'])->middleware("document_access:$LOAN_COUNTS, $VIEW");
    Route::get('/NoAUTH', [LoanCountController::class, 'index']);
    Route::get('/NoAUTH/{id}', [LoanCountController::class, 'show']);
    Route::get('/{id}', [LoanCountController::class, 'show'])->middleware("document_access:$LOAN_COUNTS, $VIEW");
    Route::post('/', [LoanCountController::class, 'store'])->middleware("document_access:$LOAN_COUNTS, $CREATE");
    Route::put('/{id}', [LoanCountController::class, 'update'])->middleware("document_access:$LOAN_COUNTS, $UPDATE");
    Route::delete('/{id}', [LoanCountController::class, 'destroy'])->middleware("document_access:$LOAN_COUNTS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_COUNTS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_COUNTS, $CREATE");
});

// Factor Rate routes
Route::middleware('auth:sanctum')->prefix('FACTOR_RATES')->group(function () use ($FACTORRATES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [FactorRateController::class, 'index'])->middleware("document_access:$FACTORRATES, $VIEW");
    Route::get('/NoAUTH', [FactorRateController::class, 'index']);
    Route::get('/NoAUTH/{id}', [FactorRateController::class, 'show']);
    Route::get('/{id}', [FactorRateController::class, 'show'])->middleware("document_access:$FACTORRATES, $VIEW");
    Route::post('/', [FactorRateController::class, 'store'])->middleware("document_access:$FACTORRATES, $CREATE");
    Route::put('/{id}', [FactorRateController::class, 'update'])->middleware("document_access:$FACTORRATES, $UPDATE");
    Route::delete('/{id}', [FactorRateController::class, 'destroy'])->middleware("document_access:$FACTORRATES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTORRATES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTORRATES, $CREATE");
});

// Payment Duration routes
Route::middleware('auth:sanctum')->prefix('PAYMENT_DURATIONS')->group(function () use ($PAYMENT_DURATIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PaymentDurationController::class, 'index'])->middleware("document_access:$PAYMENT_DURATIONS, $VIEW");
    Route::get('/NoAUTH', [PaymentDurationController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentDurationController::class, 'show']);
    Route::get('/{id}', [PaymentDurationController::class, 'show'])->middleware("document_access:$PAYMENT_DURATIONS, $VIEW");
    Route::post('/', [PaymentDurationController::class, 'store'])->middleware("document_access:$PAYMENT_DURATIONS, $CREATE");
    Route::put('/{id}', [PaymentDurationController::class, 'update'])->middleware("document_access:$PAYMENT_DURATIONS, $UPDATE");
    Route::delete('/{id}', [PaymentDurationController::class, 'destroy'])->middleware("document_access:$PAYMENT_DURATIONS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $CREATE");
});

Route::middleware('auth:sanctum')->prefix('PAYMENT_DURATIONS')->group(function () use ($PAYMENT_DURATIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PaymentDurationController::class, 'index'])->middleware("document_access:$PAYMENT_DURATIONS, $VIEW");
    Route::get('/NoAUTH', [PaymentDurationController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentDurationController::class, 'show']);
    Route::get('/{id}', [PaymentDurationController::class, 'show'])->middleware("document_access:$PAYMENT_DURATIONS, $VIEW");
    Route::post('/', [PaymentDurationController::class, 'store'])->middleware("document_access:$PAYMENT_DURATIONS, $CREATE");
    Route::put('/{id}', [PaymentDurationController::class, 'update'])->middleware("document_access:$PAYMENT_DURATIONS, $UPDATE");
    Route::delete('/{id}', [PaymentDurationController::class, 'destroy'])->middleware("document_access:$PAYMENT_DURATIONS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $CREATE");
});

// Payment routes
Route::middleware('auth:sanctum')->prefix('PAYMENTS')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::get('/NoAUTH', [PaymentController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentController::class, 'show']);
    Route::get('/{id}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::put('/{id}', [PaymentController::class, 'update']);
    Route::delete('/{id}', [PaymentController::class, 'destroy']);

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    });

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

// Payment Line
Route::middleware('auth:sanctum')->prefix('PAYMENT_LINES')->group(function () {
    Route::get('/', [PaymentLineController::class, 'index']);
    Route::get('/NoAUTH', [PaymentLineController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentLineController::class, 'show']);
    Route::get('/{id}', [PaymentLineController::class, 'show']);
    Route::post('/', [PaymentLineController::class, 'store']);
    Route::put('/{id}', [PaymentLineController::class, 'update']);
    Route::delete('/{id}', [PaymentLineController::class, 'destroy']);

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    });

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

// Payment Frequencies
Route::middleware('auth:sanctum')->prefix('PAYMENT_FREQUENCIES')->group(function () {
    Route::get('/', [PaymentFrequencyController::class, 'index']);
    Route::get('/NoAUTH', [PaymentFrequencyController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentFrequencyController::class, 'show']);
    Route::get('/{id}', [PaymentFrequencyController::class, 'show']);
    Route::post('/', [PaymentFrequencyController::class, 'store']);
    Route::put('/{id}', [PaymentFrequencyController::class, 'update']);
    Route::delete('/{id}', [PaymentFrequencyController::class, 'destroy']);

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    });

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    });
});

// Loan Schedules routes
Route::middleware('auth:sanctum')->prefix('PAYMENT_SCHEDULES')->group(function () {
    Route::get('/', [PaymentScheduleController::class, 'index']);
    Route::get('/NoAUTH', [PaymentScheduleController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentScheduleController::class, 'show']);
    Route::get('/{id}', [PaymentScheduleController::class, 'show']);
    Route::post('/', [PaymentScheduleController::class, 'store']);
    Route::put('/{id}', [PaymentScheduleController::class, 'update']);
    Route::delete('/{id}', [PaymentScheduleController::class, 'destroy']);

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    });

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    });
});



// Group routes
Route::middleware('auth:sanctum')->prefix('CUSTOMER_GROUPS')->group(function () use ($CUSTOMER_GROUPS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [CustomerGroupController::class, 'index'])->middleware("document_access:$CUSTOMER_GROUPS, $VIEW");
    Route::get('/NoAUTH', [CustomerGroupController::class, 'index']);
    Route::get('/NoAUTH/{id}', [CustomerGroupController::class, 'show']);
    Route::get('/{id}', [CustomerGroupController::class, 'show'])->middleware("document_access:$CUSTOMER_GROUPS, $VIEW");
    Route::post('/', [CustomerGroupController::class, 'store'])->middleware("document_access:$CUSTOMER_GROUPS, $CREATE");
    Route::put('/{id}', [CustomerGroupController::class, 'update'])->middleware("document_access:$CUSTOMER_GROUPS, $UPDATE");
    Route::delete('/{id}', [CustomerGroupController::class, 'destroy'])->middleware("document_access:$CUSTOMER_GROUPS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_GROUPS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_GROUPS, $CREATE");
});


// Payment Duration routes
Route::middleware('auth:sanctum')->prefix('FEES')->group(function () use ($FEES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [FeeController::class, 'index'])->middleware("document_access:$FEES, $VIEW");
    Route::get('/NoAUTH', [FeeController::class, 'index']);
    Route::get('/NoAUTH/{id}', [FeeController::class, 'show']);
    Route::get('/{id}', [FeeController::class, 'show'])->middleware("document_access:$FEES, $VIEW");
    Route::post('/', [FeeController::class, 'store'])->middleware("document_access:$FEES, $CREATE");
    Route::put('/{id}', [FeeController::class, 'update'])->middleware("document_access:$FEES, $UPDATE");
    Route::delete('/{id}', [FeeController::class, 'destroy'])->middleware("document_access:$FEES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FEES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FEES, $CREATE");

});

// Loan Application routes
Route::middleware('auth:sanctum')->prefix('LOAN_APPLICATIONS')->group(function () use ($LOAN_APPLICATIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [LoanApplicationController::class, 'index'])->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");
    Route::get('/NoAUTH', [LoanApplicationController::class, 'index']);
    Route::get('/NoAUTH/{id}', [LoanApplicationController::class, 'show']);
    Route::get('/{id}', [LoanApplicationController::class, 'show'])->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");
    Route::get('/loanno/{id}', [LoanApplicationController::class, 'look'])->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");;
    Route::get('/loanno/NoAUTH/{id}', [LoanApplicationController::class, 'look']);
    Route::get('/customer/{id}', [LoanApplicationController::class, 'see'])->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");
    Route::get('/customer/NoAUTH/{id}', [LoanApplicationController::class, 'see']);
    Route::post('/', [LoanApplicationController::class, 'store'])->middleware("document_access:$LOAN_APPLICATIONS, $CREATE");
    Route::put('/{id}', [LoanApplicationController::class, 'update'])->middleware("document_access:$LOAN_APPLICATIONS, $UPDATE");
    Route::delete('/{id}', [LoanApplicationController::class, 'destroy'])->middleware("document_access:$LOAN_APPLICATIONS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $UPDATE");

    //example code for approve
    Route::patch('/approve/{id}', [LoanApplicationController::class, 'approve']);

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $CREATE");

});

// Loan Application routes
Route::middleware('auth:sanctum')->prefix('LOAN_APPLICATION_COMAKERS')->group(function () use ($LOAN_APPLICATION_COMAKERS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [LoanApplicationCoMakerController::class, 'index'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $VIEW");
    Route::get('/NoAUTH', [LoanApplicationCoMakerController::class, 'index']);
    Route::get('/NoAUTH/{id}', [LoanApplicationCoMakerController::class, 'show']);
    Route::get('/{id}', [LoanApplicationCoMakerController::class, 'show'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $VIEW");
    Route::get('/loanid/NoAUTH/{id}', [LoanApplicationCoMakerController::class, 'look']);
    Route::get('/loanid/{id}', [LoanApplicationCoMakerController::class, 'look'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $VIEW");
    Route::post('/', [LoanApplicationCoMakerController::class, 'store'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $CREATE");
    Route::put('/{id}', [LoanApplicationCoMakerController::class, 'update'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $UPDATE");
    Route::delete('/{id}', [LoanApplicationCoMakerController::class, 'destroy'])->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $CREATE");

});


}

//get customer under this group (EG: Banana, Grapes)
Route::get('/test/{id}', [CustomerController::class, 'test']);

Route::middleware('auth')->get('/testCustomer', [UserController::class, 'test']);
