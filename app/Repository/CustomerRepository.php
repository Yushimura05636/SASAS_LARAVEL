<?php

namespace App\Repository;
use App\Interface\Repository\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function findMany()
    {
        return Customer::orderBy('personality_id')->get();
    }

    public function findOneById($id)
    {
        return Customer::find($id);
    }

    public function findOneByEmail(string $email)
    {
        return Customer::findOrFail($email);
    }

    public function create(object $payload)
    {
        $customer = new Customer();
        $customer->group_id = $payload->group_id;
        $customer->passbook_no = $payload->passbook_no;
        $customer->loan_count = $payload->loan_count;
        $customer->enable_mortuary = $payload->enable_mortuary;
        $customer->mortuary_coverage_start = $payload->mortuary_coverage_start;
        $customer->mortuary_coverage_end = $payload->mortuary_coverage_end;
        $customer->personality_id = $payload->personality_id;
        $customer->password = Hash::make($payload->password);

        $customer->save();

        return $customer->fresh();
    }

    public function update(object $payload, int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->group_id = $payload->group_id;
        $customer->passbook_no = $payload->passbook_no;
        $customer->loan_count = $payload->loan_count;
        $customer->enable_mortuary = $payload->enable_mortuary;
        $customer->mortuary_coverage_start = $payload->mortuary_coverage_start;
        $customer->mortuary_coverage_end = $payload->mortuary_coverage_end;
        $customer->personality_id = $payload->personality_id;
        $customer->password = Hash::make($payload->password);

        $customer->save();

        return $customer->fresh();
    }

    public function delete(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }

    public function findByGroupId($id){
        return Customer::where('group_id', $id)
        ->with('personality')  // Include related personality data
        ->orderBy('personality_id')
        ->get();
    }

    public function findByPersonalityId($personalityId)
    {
        return Customer::where('personality_id', $personalityId)->first();
    }
}
