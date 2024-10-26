<?php

namespace App\Repository;

use App\Interface\Requirements\CustomerRequirementsRepositoryInterface;
use App\Models\CustomerRequirements;
use Illuminate\Http\Response;

class CustomerRequirementsRepository implements CustomerRequirementsRepositoryInterface 
{
    public function findMany()
    {
        return CustomerRequirements::paginate(10);
    }

    public function findOneById(int $id)
    {
        return CustomerRequirements::findOrFail($id);
    }

    public function create(object $payload)
    {
        $customer_requirements = new CustomerRequirements();
        $customer_requirements->customer_id = $payload->customer_id;
        $customer_requirements->requirement_id = $payload->requirement_id;
        $customer_requirements->expiry_date = $payload->expiry_date;
        $customer_requirements->save();

        return $customer_requirements->fresh();
    }

    public function update(object $payload, int $id)
    {
        $customer_requirements = CustomerRequirements::findOrFail( $id );
        $customer_requirements->customer_id = $payload->customer_id;
        $customer_requirements->requirement_id = $payload->requirement_id;
        $customer_requirements->expiry_date = $payload->expiry_date;
        $customer_requirements->save();

        return $customer_requirements->fresh();
    }

    public function delete(int $id)
    {
        $customer_requirements = CustomerRequirements::findOrFail($id);
        $customer_requirements->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
