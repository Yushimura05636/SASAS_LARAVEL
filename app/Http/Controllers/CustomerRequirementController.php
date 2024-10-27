<?php

namespace App\Http\Controllers;

use App\Interface\Service\CustomerRequirementServiceInterface;
use App\Models\Customer_Requirements;
use App\Models\Requirements;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class CustomerRequirementController extends Controller
{
    private $customerRequirementService;

    public function __construct(CustomerRequirementServiceInterface $customerRequirementService)
    {
        $this->customerRequirementService = $customerRequirementService;
    }

    public function index()
    {
        return $this->customerRequirementService->findCustomerRequirements();
    }

    public function available()
    {
        //get the not expired customer_requirements
        $notExpiredRequirements = Customer_Requirements::where('expiry_date', '>', Carbon::now())->get();

        return new JsonResource($notExpiredRequirements);
    }

    public function show(int $id)
    {
        return $this->customerRequirementService->findCustomerRequirementById($id);
    }

    public function showAvailable(int $id)
    {

        //get the not expired customer_requirements
        $notExpiredRequirements = Customer_Requirements::where('customer_id', $id)->where('expiry_date', '>', Carbon::now())->get();

        $customer_requirements = [];
        $i = 0;

        foreach($notExpiredRequirements as $requirement)
        {
            $customer_requirement = Requirements::where('id', $requirement['requirement_id'])->where('isActive', 1)->first();
            $customer_requirements[$i] = $customer_requirement;
            $customer_requirements[$i]['expiry_date'] = $requirement['expiry_date'];
            $i++;
        }

        return new JsonResource($customer_requirements);
    }

    public function store(Request $request)
    {
        return $this->customerRequirementService->createCustomerRequirement($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->customerRequirementService->updateCustomerRequirement($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->customerRequirementService->deleteCustomerRequirement($id);
    }
}
