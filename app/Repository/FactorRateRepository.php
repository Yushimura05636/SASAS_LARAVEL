<?php

namespace App\Repository;

use App\Interface\Repository\FactorRateRepositoryInterface;
use App\Models\Factor_Rate;
use Illuminate\Http\Response;

class FactorRateRepository implements FactorRateRepositoryInterface
{
    public function findMany()
    {
        return Factor_Rate::with(['paymentFrequency','paymentDuration'])->get();
        // return Factor_Rate::paginate(10);
    }

    public function findOneById($id)
    {
        return Factor_Rate::findOrFail($id);
    }

    public function create(object $payload)
    {
        $factorRate = new Factor_Rate();
        $factorRate->payment_frequency_id = $payload->payment_frequency_id;
        $factorRate->payment_duration_id = $payload->payment_duration_id;
        $factorRate->description = $payload->description;
        $factorRate->value = $payload->value;
        $factorRate->notes = $payload->notes;
        $factorRate->save();
        return $factorRate->refresh();
    }

    public function update(object $payload, int $id)
    {
        $factorRate = Factor_Rate::findOrFail($id);
        $factorRate->payment_frequency_id = $payload->payment_frequency_id;
        $factorRate->payment_duration_id = $payload->payment_duration_id;
        $factorRate->description = $payload->description;
        $factorRate->value = $payload->value;
        $factorRate->notes = $payload->notes;
        $factorRate->save();
        return $factorRate->refresh();
    }

    public function delete(int $id)
    {
        $factorRate = Factor_Rate::findOrFail($id);
        $factorRate->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }

    public function getDescription()
    {
        return Factor_Rate::with(['paymentFrequency','paymentDuration'])->get();
    }
}
