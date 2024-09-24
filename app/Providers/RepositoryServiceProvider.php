<?php

namespace App\Providers;

use App\Http\Controllers\LoanApplicationCoMaker;
use App\Interface\Repository\CustomerGroupRepositoryInterface;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Interface\Repository\DBLibraryRepositoryInterface;
use App\Interface\Repository\DocumentMapRepositoryInterface;
use App\Interface\Repository\DocumentPermissionMapRepositoryInterface;
use App\Interface\Repository\DocumentPermissionRepositoryInterface;
use App\Interface\Repository\EmployeeRepositoryInterface;
use App\Interface\Repository\FactorRateRepositoryInterface;
use App\Interface\Repository\FeeRepositoryInterface;
use App\Interface\Repository\LoanApplicationCoMakerRepositoryInterface;
use App\Interface\Repository\LoanApplicationFeeRepositoryInterface;
use App\Interface\Repository\LoanApplicationRepositoryInterface;
use App\Interface\Repository\LoanReleaseRepositoryInterface;
use App\Interface\Repository\PaymentDurationRepositoryInterface;
use App\Interface\Repository\PaymentFrequencyRepositoryInterface;
use App\Interface\Repository\PaymentLineRepositoryInterface;
use App\Interface\Repository\PaymentRepositoryInterface;
use App\Interface\Repository\PaymentScheduleRepositoryInterface;
use App\Interface\Repository\PersonalityRepositoryInterface;
use App\Interface\Repository\SpouseRepositoryInterface;
use App\Interface\Repository\UserRepositoryInterface;
use App\Interface\Service\AuthenticationServiceInterface;
use App\Interface\Service\CustomerGroupServiceInterface;
use App\Interface\Service\CustomerServiceInterface;
use App\Interface\Service\DBLibraryServiceInterface;
use App\Interface\Service\DocumentMapServiceInterface;
use App\Interface\Service\DocumentPermissionMapServiceInterface;
use App\Interface\Service\DocumentPermissionServiceInterface;
use App\Interface\Service\EmployeeServiceInterface;
use App\Interface\Service\FactorRateServiceInterface;
use App\Interface\Service\LoanApplicationCoMakerServiceInterface;
use App\Interface\Service\LoanApplicationFeeServiceInterface;
use App\Interface\Service\LoanApplicationServiceInterface;
use App\Interface\Service\LoanReleaseServiceInterface;
use App\Interface\Service\PaymentDurationServiceInterface;
use App\Interface\Service\PaymentFrequencyServiceInterface;
use App\Interface\Service\PaymentLineServiceInterface;
use App\Interface\Service\PaymentScheduleServiceInterface;
use App\Interface\Service\PaymentServiceInterface;
use App\Interface\Service\PersonalityServiceInterface;
use App\Interface\Service\SpouseServiceInterface;
use App\Interface\Service\UserServiceInterface;
use App\Repository\CustomerGroupRepository;
use App\Repository\CustomerRepository;
use App\Repository\DBLibraryRepository;
use App\Repository\DocumentMapRepository;
use App\Repository\DocumentPermissionMapRepository;
use App\Repository\DocumentPermissionRepository;
use App\Repository\EmployeeRepository;
use App\Repository\FactorRateRepository;
use App\Repository\FeeRepository;
use App\Repository\LoanApplicationCoMakerRepository;
use App\Repository\LoanApplicationFeeRepository;
use App\Repository\LoanApplicationRepository;
use App\Repository\LoanReleaseRepositority;
use App\Repository\PaymentDurationRepository;
use App\Repository\PaymentFrequencyRepository;
use App\Repository\PaymentLineRepository;
use App\Repository\PaymentRepository;
use App\Repository\PersonalityRepository;
use App\Repository\SpouseRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use App\Service\CustomerGroupService;
use App\Service\CustomerService;
use App\Service\DBLibraryService;
use App\Service\DocumentMapService;
use App\Service\DocumentPermissionMapService;
use App\Service\DocumentPermissionService;
use App\Service\EmployeeService;
use App\Service\FactorRateService;
use App\Service\FeeService;
use App\Service\LoanApplicationCoMakerService;
use App\Service\LoanApplicationFeeService;
use App\Service\LoanApplicationService;
use App\Service\LoanReleaseService;
use App\Service\PaymentDurationService;
use App\Service\PaymentFrequencyService;
use App\Service\PaymentLineService;
use App\Service\PaymentScheduleService;
use App\Service\PaymentService;
use App\Service\PersonalityService;
use App\Service\SpouseService;
use App\Service\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //Repository
        $this->app->bind(SpouseRepositoryInterface::class, SpouseRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentLineRepositoryInterface::class, PaymentLineRepository::class);

        $this->app->bind(DocumentMapRepositoryInterface::class, DocumentMapRepository::class);
        $this->app->bind(DocumentPermissionMapRepositoryInterface::class, DocumentPermissionMapRepository::class);
        $this->app->bind(DocumentPermissionRepositoryInterface::class, DocumentPermissionRepository::class);

        $this->app->bind(FactorRateRepositoryInterface::class, FactorRateRepository::class);
        $this->app->bind(PaymentDurationRepositoryInterface::class, PaymentDurationRepository::class);
        $this->app->bind(PaymentScheduleRepositoryInterface::class, PaymentFrequencyRepository::class);
        $this->app->bind(LoanReleaseRepositoryInterface::class, LoanReleaseRepositority::class);
        $this->app->bind(LoanApplicationRepositoryInterface::class, LoanApplicationRepository::class);
        $this->app->bind(PaymentFrequencyRepositoryInterface::class, PaymentFrequencyRepository::class);
        $this->app->bind(LoanApplicationCoMakerRepositoryInterface::class, LoanApplicationCoMakerRepository::class);
        $this->app->bind(LoanApplicationFeeRepositoryInterface::class, LoanApplicationFeeRepository::class);
        $this->app->bind(FeeRepositoryInterface::class, FeeRepository::class);
        $this->app->bind(PersonalityRepositoryInterface::class, PersonalityRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DBLibraryRepositoryInterface::class, DBLibraryRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);

        $this->app->bind(CustomerGroupRepositoryInterface::class, CustomerGroupRepository::class);


        //Service
        $this->app->bind(SpouseServiceInterface::class, SpouseService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(PaymentLineServiceInterface::class, PaymentLineService::class);
        $this->app->bind(DocumentMapServiceInterface::class, DocumentMapService::class);
        $this->app->bind(DocumentPermissionMapServiceInterface::class, DocumentPermissionMapService::class);
        $this->app->bind(DocumentPermissionServiceInterface::class, DocumentPermissionService::class);
        $this->app->bind(FactorRateServiceInterface::class, FactorRateService::class);
        $this->app->bind(PaymentDurationServiceInterface::class, PaymentDurationService::class);
        $this->app->bind(PaymentScheduleServiceInterface::class, PaymentScheduleService::class);
        $this->app->bind(LoanReleaseServiceInterface::class, LoanReleaseService::class);
        $this->app->bind(LoanApplicationServiceInterface::class, LoanApplicationService::class);
        $this->app->bind(PaymentFrequencyServiceInterface::class, PaymentFrequencyService::class);
        $this->app->bind(LoanApplicationCoMakerServiceInterface::class, LoanApplicationCoMakerService::class);
        $this->app->bind(LoanApplicationFeeServiceInterface::class, LoanApplicationFeeService::class);
        $this->app->bind(FeeRepositoryInterface::class, FeeService::class);
        $this->app->bind(PersonalityServiceInterface::class, PersonalityService::class);
        $this->app->bind(EmployeeServiceInterface::class, EmployeeService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(DBLibraryServiceInterface::class, DBLibraryService::class);
        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(CustomerGroupServiceInterface::class, CustomerGroupService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
