<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\CustomerPersonalityController;
use App\Http\Controllers\CustomerRequirementController;
use App\Http\Controllers\DBLibraryController;
use App\Http\Controllers\DocumentMapController;
use App\Http\Controllers\DocumentPermissionController;
use App\Http\Controllers\DocumentPermissionMapController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePersonalityController;
use App\Http\Controllers\FactorRateController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GraphDataController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LoanApplicationCoMakerController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\LoanCountController;
use App\Http\Controllers\LoanReleaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentDurationController;
use App\Http\Controllers\PaymentFrequencyController;
use App\Http\Controllers\PaymentLineController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\PersonalityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserController2;
use App\Mail\TwoFactorCodeMail;
use App\Models\AuthPermission;
use App\Models\Document_Permission;
use App\Models\Document_Permission_Map;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use App\Models\User_Account;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
$FACTOR_RATES = AuthPermission::FACTOR_RATES();
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

$LOAN_RELEASES = AuthPermission::LOAN_RELEASES();
$PAYMENTS = AuthPermission::PAYMENTS();
$PAYMENT_SCHEDULES=  AuthPermission::PAYMENT_SCHEDULES();
$PAYMENT_LINES =  AuthPermission::PAYMENT_LINES();

$CUSTOMER_REQUIREMENTS = AuthPermission::CUSTOMER_REQUIREMENTS();
$REQUIREMENTS = AuthPermission::REQUIREMENTS();
$HOLIDAYS = AuthPermission::HOLIDAYS();


$VIEW =   AuthPermission::VIEW_PERM();
$CREATE = AuthPermission::CREATE_PERM();
$UPDATE = AuthPermission::UPDATE_PERM();
$DELETE = AuthPermission::DELETE_PERM();

$APPROVE = AuthPermission::APPROVE_PERM();
$REJECT = AuthPermission::REJECT_PERM();


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

//two factor authentication no auth
Route::post('/NoAUTH/SendVerification', [UserController::class, 'sendCode']);
Route::post('/NoAUTH/VerifyVerification/{code}', [UserController::class, 'verifyCode']);
Route::put('/NoAUTH/ChangePassword/{code}', [UserController::class, 'changePassword']);
Route::get('/NoAUTH/checkEmail', [UserController::class, 'checkEmail']);

// Users routes
Route::middleware('auth:sanctum')->prefix('USERS')->group(function () use ($USER_ACCOUNTS, $VIEW, $CREATE, $UPDATE, $DELETE) {

    Route::get('/', [UserController::class, 'index'])->middleware("document_access:$USER_ACCOUNTS, $VIEW");
    Route::get('/NoAUTH', [UserController::class, 'showOwnDetails']);
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


Route::post('/loginClient', [AuthController::class, 'clientLogin']);
//2FA
Route::post('/client/SendVerification', [CustomerController::class, 'sendCode']);
Route::post('/client/VerifyVerification/{code}', [CustomerController::class, 'verifyCode']);


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
Route::middleware('auth:sanctum')->prefix('CUSTOMERS')->group(function () use ($CUSTOMERS, $VIEW, $CREATE, $UPDATE, $DELETE, $APPROVE, $REJECT) {
    Route::get('/', [CustomerPersonalityController::class, 'index'])->middleware("document_access:$CUSTOMERS, $VIEW");
    Route::get('/NoAUTH', [CustomerPersonalityController::class, 'index']);
    Route::get('/CustomerAPPROVEAndActive', [CustomerPersonalityController::class, 'indexApproveActive'])->middleware("document_access:$CUSTOMERS, $VIEW");
    Route::get('/NoAUTH/CustomerAPPROVEAndActive', [CustomerPersonalityController::class, 'indexApproveActive']);

    //only get the customer with active and has payment dues
    Route::get('/NoAUTH/CustomerActiveWithPayments', [CustomerPersonalityController::class, 'indexActiveWithPayment']);

    //get the customer approve and active with loan application pending
    Route::get('/NoAUTH/CustomerAPPROVEAndActiveWithPending', [CustomerPersonalityController::class, 'indexApproveActivePending']);
    Route::get('/GroupAPPROVE/{id}', [CustomerPersonalityController::class, 'showGroupApprove'])->middleware("document_access:$CUSTOMERS, $VIEW");
    Route::get('/NoAUTH/{id}', [CustomerPersonalityController::class, 'show']);
    Route::get('/NoAUTH/Group/{id}', [CustomerPersonalityController::class, 'showGroupWithDataOnlyActive']);
    Route::get('/NoAUTH/Group/Data/{id}', [CustomerPersonalityController::class, 'showGroupWithData']);
    Route::get('/NoAUTH/Customer/{id}', [CustomerPersonalityController::class, 'showCustomerWithData']);
    Route::get('/NoAUTH/GroupAPPROVE/{id}', [CustomerPersonalityController::class, 'showGroupApprove']);
    Route::get('/NoAUTH/GroupAPPROVEACTIVE/{id}', [CustomerPersonalityController::class, 'showGroupApproveActive']);
    Route::get('/{id}', [CustomerPersonalityController::class, 'show'])->middleware("document_access:$CUSTOMERS, $VIEW");

    Route::post('/', [CustomerPersonalityController::class, 'store'])->middleware("document_access:$CUSTOMERS, $CREATE");
    Route::put('/UpdateApprove/{id}', [CustomerPersonalityController::class, 'updateApprove'])->middleware("document_access:$CUSTOMERS, $APPROVE");
    Route::put('/UpdateReject/{id}', [CustomerPersonalityController::class, 'updateReject'])->middleware("document_access:$CUSTOMERS, $REJECT");

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
Route::middleware('auth:sanctum')->prefix('FACTOR_RATES')->group(function () use ($FACTOR_RATES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [FactorRateController::class, 'index'])->middleware("document_access:$FACTOR_RATES, $VIEW");
    Route::get('/NoAUTH', [FactorRateController::class, 'index']);
    Route::get('/NoAUTH/{id}', [FactorRateController::class, 'show']);
    Route::get('/{id}', [FactorRateController::class, 'show'])->middleware("document_access:$FACTOR_RATES, $VIEW");
    Route::post('/', [FactorRateController::class, 'store'])->middleware("document_access:$FACTOR_RATES, $CREATE");
    Route::put('/{id}', [FactorRateController::class, 'update'])->middleware("document_access:$FACTOR_RATES, $UPDATE");
    Route::delete('/{id}', [FactorRateController::class, 'destroy'])->middleware("document_access:$FACTOR_RATES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTOR_RATES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTOR_RATES, $CREATE");
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
Route::middleware('auth:sanctum')->prefix('PAYMENTS')->group(function () use ($PAYMENTS, $VIEW, $CREATE, $UPDATE, $DELETE, $APPROVE, $REJECT) {
    Route::get('/', [PaymentController::class, 'index'])->middleware("document_access:$PAYMENTS, $VIEW");
    Route::get('/NoAUTH', [PaymentController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentController::class, 'show']);
    Route::get('/NoAUTH/CustomerId/{id}', [PaymentController::class, 'paymentCustomerId']);
    Route::get('/PaymentInLoanNO/{id}', [PaymentController::class, 'paymentLoanNo'])->middleware("document_access:$PAYMENTS, $VIEW");
    Route::get('/NoAUTH/PaymentInLoanNO/{id}', [PaymentController::class, 'paymentLoanNo']);
    Route::get('/{id}', [PaymentController::class, 'show'])->middleware("document_access:$PAYMENTS, $VIEW");
    Route::post('/', [PaymentController::class, 'store'])->middleware("document_access:$PAYMENTS, $CREATE");
    Route::put('/{id}', [PaymentController::class, 'paymentUpdate'])->middleware("document_access:$PAYMENTS, $UPDATE");
    Route::put('/PaymentAPPROVE/{id}', [PaymentController::class, 'paymentApprove'])->middleware("document_access:$PAYMENTS, $APPROVE");
    Route::put('/PaymentREJECT/{id}', [PaymentController::class, 'paymentReject'])->middleware("document_access:$PAYMENTS, $REJECT");
    Route::delete('/{id}', [PaymentController::class, 'destroy'])->middleware("document_access:$PAYMENTS, $DELETE");



    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENTS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENTS, $CREATE");
});

// Payment Line
Route::middleware('auth:sanctum')->prefix('PAYMENT_LINES')->group(function () use ($PAYMENT_LINES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PaymentLineController::class, 'index'])->middleware("document_access:$PAYMENT_LINES, $VIEW");
    Route::get('/NoAUTH', [PaymentLineController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentLineController::class, 'show']);
    Route::get('/{id}', [PaymentLineController::class, 'show'])->middleware("document_access:$PAYMENT_LINES, $VIEW");
    Route::post('/', [PaymentLineController::class, 'store'])->middleware("document_access:$PAYMENT_LINES, $CREATE");
    Route::put('/{id}', [PaymentLineController::class, 'update'])->middleware("document_access:$PAYMENT_LINES, $UPDATE");
    Route::delete('/{id}', [PaymentLineController::class, 'destroy'])->middleware("document_access:$PAYMENT_LINES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $CREATE");
});

// Payment Frequencies
Route::middleware('auth:sanctum')->prefix('PAYMENT_FREQUENCIES')->group(function () use ($PAYMENT_FREQUENCIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PaymentFrequencyController::class, 'index'])->middleware("document_access:$PAYMENT_FREQUENCIES, $VIEW");
    Route::get('/NoAUTH', [PaymentFrequencyController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentFrequencyController::class, 'show']);
    Route::get('/{id}', [PaymentFrequencyController::class, 'show'])->middleware("document_access:$PAYMENT_FREQUENCIES, $VIEW");
    Route::post('/', [PaymentFrequencyController::class, 'store'])->middleware("document_access:$PAYMENT_FREQUENCIES, $CREATE");
    Route::put('/{id}', [PaymentFrequencyController::class, 'update'])->middleware("document_access:$PAYMENT_FREQUENCIES, $UPDATE");
    Route::delete('/{id}', [PaymentFrequencyController::class, 'destroy'])->middleware("document_access:$PAYMENT_FREQUENCIES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_FREQUENCIES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_FREQUENCIES, $CREATE");
});

// Loan Schedules routes
Route::middleware('auth:sanctum')->prefix('PAYMENT_SCHEDULES')->group(function () use ($PAYMENT_SCHEDULES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [PaymentScheduleController::class, 'index'])->middleware("document_access:$PAYMENT_SCHEDULES, $VIEW");
    Route::get('/all', [PaymentScheduleController::class, 'indexAll'])->middleware("document_access:$PAYMENT_SCHEDULES, $VIEW");
    Route::get('/NoAUTH', [PaymentScheduleController::class, 'index']);
    Route::get('/NoAUTH/{id}', [PaymentScheduleController::class, 'show']);
    Route::get('/NoAUTH/Customer/{id}', [PaymentScheduleController::class, 'showCustomer']);
    Route::get('/{id}', [PaymentScheduleController::class, 'show'])->middleware("document_access:$PAYMENT_SCHEDULES, $VIEW");
    Route::post('/', [PaymentScheduleController::class, 'store'])->middleware("document_access:$PAYMENT_SCHEDULES, $CREATE");
    Route::put('/{id}', [PaymentScheduleController::class, 'update'])->middleware("document_access:$PAYMENT_SCHEDULES, $UPDATE");
    Route::delete('/{id}', [PaymentScheduleController::class, 'destroy'])->middleware("document_access:$PAYMENT_SCHEDULES, $DELETE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_SCHEDULES, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_SCHEDULES, $CREATE");
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
    Route::get('/NoAUTH/FeeActive/', [FeeController::class, 'indexActive']);
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
    Route::get('/loannoWithPending/NoAUTH/{id}', [LoanApplicationController::class, 'customerLoanApplicationNoPending']);
    Route::get('/customer/{id}', [LoanApplicationController::class, 'see'])->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");
    Route::get('/customer/NoAUTH/{id}', [LoanApplicationController::class, 'see']);
    Route::get('/customerWithPending/NoAUTH/{id}', [LoanApplicationController::class, 'seeWithPending']);
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


//loan Release
Route::middleware('auth:sanctum')->prefix('LOAN_RELEASES')->group(function () use ($LOAN_RELEASES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [LoanReleaseController::class, 'index'])->middleware("document_access:$LOAN_RELEASES, $VIEW");
    Route::get('/NoAUTH', [LoanReleaseController::class, 'index']);
    Route::get('/NoAUTH/{id}', [LoanReleaseController::class, 'show']);

    Route::get('/{id}', [LoanReleaseController::class, 'show'])->middleware("document_access:$LOAN_RELEASES, $VIEW");
    Route::post('/', [LoanReleaseController::class, 'store'])->middleware("document_access:$LOAN_RELEASES, $CREATE");
    Route::put('/{id}', [LoanReleaseController::class, 'update'])->middleware("document_access:$LOAN_RELEASES, $UPDATE");
    Route::delete('/{id}', [LoanReleaseController::class, 'destroy'])->middleware("document_access:$LOAN_RELEASES, $DELETE");
    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_RELEASES, $UPDATE");
    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_RELEASES, $CREATE");
});

// Customers routes
Route::middleware('auth:sanctum')->prefix('CUSTOMER_REQUIREMENTS')->group(function () use ($CUSTOMER_REQUIREMENTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [CustomerRequirementController::class, 'index'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $VIEW");
    Route::get('/NoAUTH', [CustomerRequirementController::class, 'index']);
    Route::get('/NotEXPIRED', [CustomerRequirementController::class, 'available'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $VIEW");
    Route::get('/NoAUTH/NotEXPIRED', [CustomerRequirementController::class, 'available']);
    Route::get('/NotEXPIRED/{id}', [CustomerRequirementController::class, 'showAvailable'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $VIEW");
    Route::get('/NoAUTH/NotEXPIRED/{id}', [CustomerRequirementController::class, 'showAvailable']);
    Route::get('/NoAUTH/{id}', [CustomerRequirementController::class, 'show']);
    Route::get('/{id}', [CustomerRequirementController::class, 'show'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $VIEW");
    Route::post('/', [CustomerRequirementController::class, 'store'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_REQUIREMENTS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_REQUIREMENTS, $CREATE");

    Route::put('/{id}', [CustomerRequirementController::class, 'update'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $UPDATE");
    Route::patch('/{id}', [CustomerRequirementController::class, 'update'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $UPDATE"); // PATCH method
    Route::delete('/{id}', [CustomerRequirementController::class, 'destroy'])->middleware("document_access:$CUSTOMER_REQUIREMENTS, $DELETE");
});

// Customers routes
Route::middleware('auth:sanctum')->prefix('REQUIREMENTS')->group(function () use ($REQUIREMENTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [RequirementController::class, 'index'])->middleware("document_access:$REQUIREMENTS, $VIEW");
    Route::get('/Active', [RequirementController::class, 'active'])->middleware("document_access:$REQUIREMENTS, $VIEW");
    Route::get('/NoAUTH/Active', [RequirementController::class, 'active']);
    Route::get('/NoAUTH', [RequirementController::class, 'index']);
    Route::get('/NoAUTH/{id}', [RequirementController::class, 'show']);
    Route::get('/{id}', [RequirementController::class, 'show'])->middleware("document_access:$REQUIREMENTS, $VIEW");
    Route::post('/', [RequirementController::class, 'store'])->middleware("document_access:$REQUIREMENTS, $CREATE");

    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$REQUIREMENTS, $UPDATE");

    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$REQUIREMENTS, $CREATE");

    Route::put('/{id}', [RequirementController::class, 'update'])->middleware("document_access:$REQUIREMENTS, $UPDATE");
    Route::patch('/{id}', [RequirementController::class, 'update'])->middleware("document_access:$REQUIREMENTS, $UPDATE"); // PATCH method
    Route::delete('/{id}', [RequirementController::class, 'destroy'])->middleware("document_access:$REQUIREMENTS, $DELETE");
});

//Holiday route
Route::middleware('auth:sanctum')->prefix('HOLIDAYS')->group(function () use ($HOLIDAYS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::get('/', [HolidayController::class, 'index'])->middleware("document_access:$HOLIDAYS, $VIEW");
    Route::get('/NoAUTH', [HolidayController::class, 'index']);
    Route::get('/NoUSERID', [HolidayController::class, '']);
    Route::get('/{id}', [HolidayController::class, 'show'])->middleware("document_access:$HOLIDAYS, $VIEW");
    Route::post('/', [HolidayController::class, 'store'])->middleware("document_access:$HOLIDAYS, $CREATE");
    // Empty update route with PATCH
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$HOLIDAYS, $UPDATE");
    // Empty create route with PATCH
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$HOLIDAYS, $CREATE");
    Route::put('/{id}', [HolidayController::class, 'update'])->middleware("document_access:$HOLIDAYS, $UPDATE");
    Route::patch('/{id}', [HolidayController::class, 'update'])->middleware("document_access:$HOLIDAYS, $UPDATE"); // PATCH method
    Route::delete('/{id}', [HolidayController::class, 'destroy'])->middleware("document_access:$HOLIDAYS, $DELETE");
});

//
//permissions

//criteria
//create
//update
//approve
//reject

//users
Route::middleware('auth:sanctum')->prefix('USER_ACCOUNTS_AUTH')->group(function () use ($USER_ACCOUNTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$USER_ACCOUNTS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$USER_ACCOUNTS, $CREATE");
});


//customers
Route::middleware('auth:sanctum')->prefix('CUSTOMERS_AUTH')->group(function () use ($CUSTOMERS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/view', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMERS, $VIEW");
    
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMERS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMERS, $CREATE");
});


//customers groups
Route::middleware('auth:sanctum')->prefix('CUSTOMER_AUTH')->group(function () use ($CUSTOMER_GROUPS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_GROUPS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_GROUPS, $CREATE");
});


//customers requirements
Route::middleware('auth:sanctum')->prefix('CUSTOMER_REQUIREMENTS_AUTH')->group(function () use ($CUSTOMER_REQUIREMENTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_REQUIREMENTS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$CUSTOMER_REQUIREMENTS, $CREATE");
});


//requirements
Route::middleware('auth:sanctum')->prefix('REQUIREMENTS_AUTH')->group(function () use ($REQUIREMENTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$REQUIREMENTS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$REQUIREMENTS, $CREATE");
});


//employees
Route::middleware('auth:sanctum')->prefix('EMPLOYEES_AUTH')->group(function () use ($EMPLOYEES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$EMPLOYEES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$EMPLOYEES, $CREATE");
});


//libraries
Route::middleware('auth:sanctum')->prefix('LIBRARIES_AUTH')->group(function () use ($LIBRARIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LIBRARIES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LIBRARIES, $CREATE");
});


//factor rates
Route::middleware('auth:sanctum')->prefix('FACTOR_RATES_AUTH')->group(function () use ($FACTOR_RATES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTOR_RATES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FACTOR_RATES, $CREATE");
});


//payment duration
Route::middleware('auth:sanctum')->prefix('PAYMENT_DURATIONS_AUTH')->group(function () use ($PAYMENT_DURATIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_DURATIONS, $CREATE");
});


//payment frequencies
Route::middleware('auth:sanctum')->prefix('PAYMENT_FREQUENCIES_AUTH')->group(function () use ($PAYMENT_FREQUENCIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_FREQUENCIES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_FREQUENCIES, $CREATE");
});


//personalities
Route::middleware('auth:sanctum')->prefix('PERSONALITIES_AUTH')->group(function () use ($PERSONALITIES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PERSONALITIES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PERSONALITIES, $CREATE");
});

//document maps
Route::middleware('auth:sanctum')->prefix('DOCUMENT_MAPS_AUTH')->group(function () use ($DOCUMENT_MAPS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAPS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAPS, $CREATE");
});

//document map permission
Route::middleware('auth:sanctum')->prefix('DOCUMENT_MAP_PERMISSIONS_AUTH')->group(function () use ($DOCUMENT_MAP_PERMISSIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_MAP_PERMISSIONS, $CREATE");
});


//document permission
Route::middleware('auth:sanctum')->prefix('DOCUMENT_MAP_PERMISSIONS_AUTH')->group(function () use ($DOCUMENT_PERMISSIONS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_PERMISSIONS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$DOCUMENT_PERMISSIONS, $CREATE");
});


//document permission
Route::middleware('auth:sanctum')->prefix('LOAN_COUNTS_AUTH')->group(function () use ($LOAN_COUNTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_COUNTS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_COUNTS, $CREATE");
});


//fees
Route::middleware('auth:sanctum')->prefix('FEES_AUTH')->group(function () use ($FEES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FEES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$FEES, $CREATE");
});


//loan applications
Route::middleware('auth:sanctum')->prefix('LOAN_APPLICATIONS_AUTH')->group(function () use ($LOAN_APPLICATIONS, $VIEW, $CREATE, $UPDATE, $DELETE, $APPROVE, $REJECT) {
    Route::patch('/view', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $VIEW");
    
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $CREATE");

    Route::patch('/approve', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $APPROVE");

    Route::patch('/reject', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATIONS, $REJECT");
});


//loan application co makers
Route::middleware('auth:sanctum')->prefix('LOAN_APPLICATION_COMAKERS_AUTH')->group(function () use ($LOAN_APPLICATION_COMAKERS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_APPLICATION_COMAKERS, $CREATE");
});


//loan releases
Route::middleware('auth:sanctum')->prefix('LOAN_RELEASES_AUTH')->group(function () use ($LOAN_RELEASES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_RELEASES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$LOAN_RELEASES, $CREATE");
});


//payments
Route::middleware('auth:sanctum')->prefix('PAYMENTS_AUTH')->group(function () use ($PAYMENTS, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/view', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENTS, $VIEW");

    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENTS, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENTS, $CREATE");
});


//payment schedules
Route::middleware('auth:sanctum')->prefix('PAYMENT_SCHEDULES_AUTH')->group(function () use ($PAYMENT_SCHEDULES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_SCHEDULES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_SCHEDULES, $CREATE");
});


//payment lines
Route::middleware('auth:sanctum')->prefix('PAYMENT_SCHEDULES_AUTH')->group(function () use ($PAYMENT_LINES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $CREATE");
});



//payment lines
Route::middleware('auth:sanctum')->prefix('PAYMENT_SCHEDULES_AUTH')->group(function () use ($PAYMENT_LINES, $VIEW, $CREATE, $UPDATE, $DELETE) {
    Route::patch('/update', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $UPDATE");
    
    Route::patch('/create', function () {
        return response()->json(['message' => 'Access granted']);
    })->middleware("document_access:$PAYMENT_LINES, $CREATE");
});

}

Route::middleware('auth:sanctum')->prefix('COLLECTORS')->group(function () {

    //show only the user with collector permission
    //collector permission consists of
    //customer_group
    //payments
    //payment_schedules
    //payment_line
    //customer
    Route::get('/NoAUTH', [UserController::class, 'getOnlyCollectorPermissions']);
});

// REGISTER FOR CUSTOMER IN LANDING PAGE
Route::prefix('REGISTER_LIBRARIES')->group(function () {
    Route::get('/NoAUTH/{modeltype}', [DBLibraryController::class, 'index']);
    Route::get('/NoAUTH', [LoanCountController::class, 'index']);
    Route::post('/', [CustomerPersonalityController::class, 'storeForRegistration']);
});
    Route::get('REGISTER/NoAUTH/FeeActive/', [FeeController::class, 'indexActive']);



//get customer under this group (EG: Banana, Grapes)
Route::get('/test/{id}', [CustomerController::class, 'test']);

Route::get('/loan-test/{id}', [PaymentScheduleController::class, 'test']);

//GET USER WHO IS LOGGED// TO RECORD WHO APPROVE AND RELEASE
Route::middleware('auth:sanctum')->get('/USER_LOGGED', [UserController::class, 'getUserLogged']);

Route::get('/HOLIDAY-TEST', [HolidayController::class, 'index']);

// // REGISTER FOR CUSTOMER IN LANDING PAGE
// Route::prefix('REGISTER_LIBRARIES_NOT')->group(function () {
//     Route::get('/NoAUTH/{modeltype}', [DBLibraryController::class, 'index']);
//     Route::get('/NoAUTH', [LoanCountController::class, 'index']);
//     Route::post('/', [CustomerPersonalityController::class, 'storeForRegistration']);
// });


//bar graph payments data
Route::get('GRAPHS/NoAUTH/Data/All', [GraphDataController::class, 'index']);


//test
Route::get('/NoAUTH/CustomerAPPROVE', [CustomerPersonalityController::class, 'indexApprove']);
Route::get('/customerWithPending/NoAUTH/{id}', [LoanApplicationController::class, 'seeWithPending']);


//REPORTS TEST
Route::get('REPORTS', [ReportController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('DASHBOARD')->group(function () {
    Route::get('/NoAUTH/USER/DETAILS', [UserController::class, 'showUserDetails']);
    Route::get('/NoAUTH/USER/LOAN/DETAILS', [UserController::class, 'showUserLoanDetails']);
    
});



