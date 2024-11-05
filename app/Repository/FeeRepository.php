<?php

namespace App\Repository;
use App\Interface\Repository\FeeRepositoryInterface;
use App\Models\Fees;
use Illuminate\Http\Response;

class FeeRepository implements FeeRepositoryInterface
{
    public function findMany()
    {
        return Fees::paginate(10);
    }

    public function findOneById(int $id)
    {
        return Fees::findOrFail($id);
    }

    public function create(object $payload)
    {
        $fee = new Fees();
        $fee->description = $payload->description;
        $fee->amount = $payload->amount;
        $fee->isactive = $payload->isactive;
        $fee->notes = $payload->notes;
        $fee->save();

        return $fee->fresh();
    }

    public function update(object $payload, int $id)
    {
        $fee = Fees::findOrFail( $id );
        $fee->description = $payload->description;
        $fee->amount = $payload->amount;
        $fee->isactive = $payload->isactive;
        $fee->notes = $payload->notes;
        $fee->save();

        return $fee->fresh();
    }

    public function delete(int $id)
    {
        $fee = Fees::findOrFail($id);
        $fee->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
