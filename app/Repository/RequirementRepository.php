<?php

namespace App\Repository;

use App\Interface\Repository\RequirementRepositoryInterface;
use App\Models\Requirements;
use Illuminate\Http\Response;

class RequirementRepository implements RequirementRepositoryInterface
{
    public function findMany()
    {
        return Requirements::paginate(10);
    }

    public function findOneById(int $id)
    {
        return Requirements::findOrFail($id);
    }

    public function create(object $payload)
    {
        $requirements = new Requirements();
        $requirements->description = $payload->description;
        $requirements->isActive = $payload->isActive;
        $requirements->save();

        return $requirements->fresh();
    }

    public function update(object $payload, int $id)
    {
        $requirements = Requirements::findOrFail( $id );
        $requirements->description = $payload->description;
        $requirements->isActive = $payload->isActive;
        $requirements->save();

        return $requirements->fresh();
    }

    public function delete(int $id)
    {
        $requirements = Requirements::findOrFail($id);
        $requirements->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
