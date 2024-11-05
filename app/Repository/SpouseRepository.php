<?php

namespace App\Repository;

use App\Interface\Repository\SpouseRepositoryInterface;
use App\Models\Spouse;
use Illuminate\Http\Response;

class SpouseRepository implements SpouseRepositoryInterface
{
    public function findMany()
    {
        return Spouse::paginate(10);
    }

    public function findOneById(int $id)
    {
        return Spouse::findOrFialId($id);
    }

    public function findOneByEmail(string $email)
    {
        return Spouse::findOrFail($email);
    }

    public function create(object $payload)
    {
        $spouse = new Spouse();
        $spouse->family_name = $payload->family_name;
        $spouse->middle_name = $payload->middle_name;
        $spouse->last_name = $payload->last_name;
        $spouse->gender_code = $payload->gender_code;
        $spouse->email_address = $payload->email_address;
        $spouse->cellphone_no = $payload->cellphone_no;
        $spouse->save();

        return $spouse->fresh();
    }

    public function update(object $payload, int $id)
    {
        $spouse = Spouse::findOrFail($id);
        $spouse->family_name = $payload->family_name;
        $spouse->middle_name = $payload->middle_name;
        $spouse->last_name = $payload->last_name;
        $spouse->gender_code = $payload->gender_code;
        $spouse->email_address = $payload->email_address;
        $spouse->cellphone_no = $payload->cellphone_no;
        $spouse->save();

        return $spouse->fresh();
    }

    public function delete(int $id)
    {
        $spouse = Spouse::findOrFail($id);
        $spouse->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
