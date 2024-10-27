<?php

namespace App\Repository;

use App\Interface\Repository\CustomerRequirementRepositoryInterface;
use App\Models\Customer_Requirements;
use Illuminate\Http\Response;

class CustomerRequirementRepository implements CustomerRequirementRepositoryInterface
{
    public function findMany()
    {
        return Customer_Requirements::paginate(10);
    }

    public function findOneById(int $id)
    {
        return Customer_Requirements::findOrFail($id);
    }

    public function create(object $payload)
    {
        $customer_requirements = new Customer_Requirements();
        $customer_requirements->customer_id = $payload->customer_id;
        $customer_requirements->requirement_id = $payload->requirement_id;
        $customer_requirements->expiry_date = $payload->expiry_date;
        $customer_requirements->save();

        return $customer_requirements->fresh();
    }

    public function update(object $payload, int $id)
    {
        $customer_requirements = Customer_Requirements::findOrFail( $id );
        $customer_requirements->customer_id = $payload->customer_id;
        $customer_requirements->requirement_id = $payload->requirement_id;
        $customer_requirements->expiry_date = $payload->expiry_date;
        $customer_requirements->save();

        return $customer_requirements->fresh();
    }

    public function delete(int $id)
    {
        $customer_requirements = Customer_Requirements::findOrFail($id);
        $customer_requirements->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
