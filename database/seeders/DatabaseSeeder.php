<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Models\User_Account;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

use App\Models\Barangay; #Done
use App\Models\Branch; #Done
use App\Models\City; #Done
use App\Models\Civil_Status; #Done
use App\Models\Gender_Map; #Done
use App\Models\Country; #Done
use App\Models\Province; #Done
use App\Models\Credit_Status; #Done
use App\Models\Customer;
use App\Models\Personality_Status_Map; #Done
use App\Models\User_Account_Status; #Done
use App\Models\Document_Map; #Done
use App\Models\Document_Permission_Map; #Done
use App\Models\Name_Type; #Done
use App\Models\Customer_Group; #Done
use App\Models\Loan_Count;
use App\Models\Personality; #Done
use App\Models\Spouse;

class DatabaseSeeder extends Seeder
{
    private $c;
    private $f;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create();

       $this->c = 500;
        $this->f = 1;

        for($i = 0; $i < 2; $i++)
        {
            Personality::create([
                'datetime_registered'=>now(),
                'family_name'=>$faker->name(),
                'middle_name'=>$faker->name(),
                'first_name'=>$faker->name(),
                //'description'=>$faker->sentence(6),
                'birthday'=>now(),
                'civil_status'=>$faker->sentence(6),
                'gender_code'=>$faker->numberBetween(1,2),
                'house_street'=>$faker->address(),
                'purok_zone'=>$faker->state(),
                'postal_code'=> $faker->postcode(),
                'telephone_no'=>$faker->phoneNumber(),
                'email_address'=>$faker->email(),
                'cellphone_no'=> $faker->phoneNumber(),
                'name_type_code'=>$faker->numberBetween(1,2),
                'personality_status_code'=>1,
                //'branch_id'=>$faker->numberBetween(1,10),
                'barangay_id'=>$faker->numberBetween(1,10),
                'city_id'=>$faker->numberBetween(1,10),
                'country_id'=>$faker->numberBetween(1,10),
                'province_id'=>$faker->numberBetween(1,10),
                //'spouse_id'=>$faker->numberBetween(1,10),
                'credit_status_id'=>$faker->numberBetween(1,10),
            ]);
        }

        $modelTypes = [
            'barangay',
            'branch',
            'city',
            'country',
            'province',
            'document_map',
            'customer_group'
        ];

        // Defining unique value arrays beforehand
        $civilStatusNames = ['Married', 'Widowed', 'Single'];
        $genderNames = ['Male', 'Female'];
        $creditStatusNames = ['Active', 'Inactive', 'Suspended', 'Blacklisted'];
        $userAccountStatusNames = ['Active', 'Inactive'];
        $documentPermissionNames = ['create', 'update', 'delete', 'view'];
        $nameTypes = ['Employee', 'Customer'];

        // Civil Status entries
        foreach ($civilStatusNames as $name) {
            Civil_Status::createEntry($name); // No duplicates, direct array iteration
        }

        // Gender Map entries
        foreach ($genderNames as $name) {
            Gender_Map::createEntry($name); // No duplicates, direct array iteration
        }

        // Credit Status entries
        foreach ($creditStatusNames as $name) {
            Credit_Status::createEntry($name); // No duplicates, direct array iteration
        }

        // User Account Status entries
        foreach ($userAccountStatusNames as $name) {
            User_Account_Status::createEntry($name); // No duplicates, direct array iteration
        }

        // Document Permission Map entries
        foreach ($documentPermissionNames as $name) {
            Document_Permission_Map::createEntry($name); // No duplicates, direct array iteration
        }

        // Name Type entries
        foreach ($nameTypes as $name) {
            Name_Type::createEntry($name); // No duplicates, direct array iteration
        }

        for($i = 0; $i < 200; $i++) {
            echo "1"; echo $this->f+1;
            $modeltype = $faker->randomElement($modelTypes);
            $description = $faker->sentence(10);
            echo $modeltype;

            switch ($modeltype) {
                case 'barangay':
                    Barangay::createEntry($faker->unique()->streetName());
                    break;
                case 'branch':
                    Branch::createEntry($description);
                    break;
                case 'city':
                    City::createEntry($faker->unique()->city());
                    break;
                case 'country':
                    Country::createEntry($faker->unique()->country());
                    break;
                case 'province':
                    Province::createEntry($faker->unique()->state());
                    break;
                case 'personality_status_map':
                    Personality_Status_Map::createEntry($description);
                    break;
                case 'document_map':
                    Document_Map::createEntry($description);
                    break;
                case 'customer_group':
                    Customer_Group::createEntry($description);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown model type: $modeltype");
            }
        }

        Employee::create([
            'sss_no' => $faker->randomDigit(),
            'phic_no' => $faker->randomDigit(),
            'tin_no' => $faker->randomDigit(),
            'datetime_hired' => $faker->date(),
            'datetime_resigned' => $faker->date(),
            'personality_id' => 1,
        ]);

        Loan_Count::create([
            'loan_count' => 1,
            'min_amount' => 5000.00,
            'max_amount' => 15000.00,
        ]);

        Customer::create([
            'group_id' => $faker->randomDigit(),
            'passbook_no' => $faker->randomDigit(),
            'loan_count_id' => 1,
            'enable_mortuary' => $faker->numberBetween(1,2),
            'mortuary_coverage_start' => null,
            'mortuary_coverage_end' => null,
            'personality_id' => 2,
        ]);

        User_Account::create([
            'last_name' => 'Sasas',
            'first_name' => 'Sasas',
            'middle_name' => 'Sasas',
            'email' => 'Sasas@email.com',
            'password' => Hash::make('password'),
            'employee_id' => 1,
            'status_id' => 1,
        ]);
    }
}
