<?php

namespace App\Repository;

use App\Interface\Repository\CustomerGroupRepositoryInterface;
use App\Models\Customer;
use App\Models\Customer_Group;
use Illuminate\Http\Response;

class CustomerGroupRepository implements CustomerGroupRepositoryInterface
{
    public function findMany()
    {
        return Customer_Group::paginate(10);
    }

    public function findOneById($id)
    {
        return Customer_Group::findOrFail($id);
    }

    public function create(object $payload)
    {
        $customer_Group = new Customer_Group();
        $customer_Group->description = $payload->description;
        $customer_Group->collector_id = $payload->collector_id;
        $customer_Group->save();

        return $customer_Group->fresh();
    }

    public function update(object $payload, int $id)
    {
        $customer_Group = Customer_Group::findOrFail($id);
        $customer_Group->description = $payload->description;
        $customer_Group->save();
        
        return $customer_Group->fresh();
    }

    public function delete(int $id)
    {
        $customer_Group = Customer_Group::findOrFail($id);
        $customer_Group->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
