<?php

namespace App\Repository;

use App\Interface\Repository\HolidayRepositoryInterface;
use App\Models\Holiday;
use Illuminate\Http\Response;

class HolidayRepository implements HolidayRepositoryInterface
{
    public function findMany()
    {
        return Holiday::get(); //no limit
    }

    public function findOneById($id)
    {
        return Holiday::find($id);
    }

    public function create(object $payload)
    {
        $holiday = new Holiday();
        $holiday->description= $payload->description;
        $holiday->date= $payload->date;
        $holiday->isActive= $payload->isActive;
        

        $holiday->save();
        return $holiday->fresh();
    }

    public function update(object $payload, int $id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->description= $payload->description;
        $holiday->date= $payload->date;
        $holiday->isActive= $payload->isActive;
        

        $holiday->save();
        return $holiday->fresh();
    }

    public function delete(int $id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
