<?php
namespace App\Repository;

use App\Models\Personality;
//use App\Factories\DBBaseLibraryFactory;
use App\Interface\Repository\PersonalityRepositoryInterface;
use Illuminate\Http\Response;

class PersonalityRepository implements PersonalityRepositoryInterface
{
    public function findMany()
    {
        return Personality::orderBy('id')->get();
    }

    public function findOneById(int $id)
    {
        return Personality::FindorFail($id);
    }

    public function findByEmail(string $email)
    {
        return Personality::where('email_address', $email)->first();
    }


    public function create(object $payload)
    {
        //$personality = new DBBaseLibraryFactory($modelType, $payload);
        $Personality = new Personality();

        $Personality->datetime_registered = $payload->datetime_registered;
        $Personality->family_name = $payload->family_name;
        $Personality->middle_name = $payload->middle_name;
        $Personality->first_name = $payload->first_name;
        //$Personality->description = $payload->description;
        $Personality->birthday = $payload->birthday;
        $Personality->civil_status = $payload->civil_status;
        $Personality->gender_code = $payload->gender_code;
        $Personality->house_street = $payload->house_street;
        $Personality->purok_zone = $payload->purok_zone;
        $Personality->postal_code = $payload->postal_code;
        $Personality->telephone_no = $payload->telephone_no;
        $Personality->email_address = $payload->email_address;
        $Personality->cellphone_no = $payload->cellphone_no;

        #dli nako sure dre sas kay foreign key
        $Personality->name_type_code = $payload->name_type_code;
        $Personality->personality_status_code = $payload->personality_status_code;
        //$Personality->branch_id = $payload->branch_id;
        $Personality->barangay_id = $payload->barangay_id;
        $Personality->city_id = $payload->city_id;
        $Personality->country_id = $payload->country_id;
        $Personality->province_id = $payload->province_id;
        //$Personality->spouse_id = $payload->spouse_id;
        $Personality->credit_status_id = $payload->credit_status_id;

        $Personality->save();
        return $Personality->refresh();
    }

    public function update(  object $payload, int $id)
    {
        $Personality = Personality::FindorFail($id);
        $Personality->datetime_registered = $payload->datetime_registered;
        $Personality->family_name = $payload->family_name;
        $Personality->middle_name = $payload->middle_name;
        $Personality->first_name = $payload->first_name;
        //$Personality->description = $payload->description;
        $Personality->birthday = $payload->birthday;
        $Personality->civil_status = $payload->civil_status;
        $Personality->gender_code = $payload->gender_code;
        $Personality->house_street = $payload->house_street;
        $Personality->purok_zone = $payload->purok_zone;
        $Personality->postal_code = $payload->postal_code;
        $Personality->telephone_no = $payload->telephone_no;
        $Personality->email_address = $payload->email_address;
        $Personality->cellphone_no = $payload->cellphone_no;

        #dli nako sure dre sas kay foreign key
        $Personality->name_type_code = $payload->name_type_code;
        $Personality->personality_status_code = $payload->personality_status_code;
        //$Personality->branch_id = $payload->branch_id;
        $Personality->barangay_id = $payload->barangay_id;
        $Personality->city_id = $payload->city_id;
        $Personality->country_id = $payload->country_id;
        $Personality->province_id = $payload->province_id;
        //$Personality->spouse_id = $payload->spouse_id;
        $Personality->credit_status_id = $payload->credit_status_id;

        $Personality->save();
        return $Personality->refresh();
    }

    public function delete(int $id)
    {
        $Personality = Personality::FindorFail($id);
        $Personality->delete();

        return response()->json([
            'message' => 'Success'
        ], Response::HTTP_OK);
    }
}
