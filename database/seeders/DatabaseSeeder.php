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
use App\Models\Document_Permission;
use App\Models\Loan_Count;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
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

        for($i = 0; $i < 4; $i++)
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
            // 'document_map',
            'customer_group',
            // 'personality_status_map',
        ];

        // Defining unique value arrays beforehand
        $civilStatusNames = ['Married', 'Widowed', 'Single'];
        $genderNames = ['Male', 'Female'];
        $creditStatusNames = ['Active', 'Inactive', 'Suspended', 'Blacklisted'];
        $userAccountStatusNames = ['Active', 'Inactive'];
        $documentPermissionNames = ['CREATE', 'UPDATE', 'DELETE', 'VIEW'];
        $nameTypes = ['Employee', 'Customer'];
        $personality_status_map = ['Pending', 'Approved','Reject','Active','Inactive'];

        $documentMap = [
            'USER_ACCOUNTS', # 1
            'BUTTON_AUTHORIZARIONS',
            'LIBRARIES',
            'CUSTOMERS',
            'CUSTOMER_GROUPS',
            'EMPLOYEES',
            'FACTOR_RATES', #5
            'PAYMENT_DURATIONS', #6
            'PAYMENT_FREQUENCIES', #7
            'PERSONALITIES',
            'DOCUMENT_MAPS',
            'DOCUMENT_MAP_PERMISSIONS',
            'DOCUMENT_PERMISSIONS',
            'LOAN_COUNTS',
            'FEES'
        ];

        foreach ($personality_status_map as $name) {
            Personality_Status_Map::createEntry($name); // No duplicates, direct array iteration
        }
        foreach ($documentMap as $name) {
            Document_Map::createEntry($name); // No duplicates, direct array iteration
        }
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
            $description = $faker->unique()->sentence(3);
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
                // case 'personality_status_map':
                //     Personality_Status_Map::createEntry($faker->unique()->sentence(6));
                //     break;
                // case 'document_map':
                //     Document_Map::createEntry($description);
                //     break;
                case 'customer_group':
                    Customer_Group::createEntry($description);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown model type: $modeltype");
            }
        }

        Employee::create([
            'sss_no' => $faker->unique()->randomDigit(),
            'phic_no' => $faker->unique()->randomDigit(),
            'tin_no' => $faker->unique()->randomDigit(),
            'datetime_hired' => $faker->unique()->date(),
            'datetime_resigned' => $faker->unique()->date(),
            'personality_id' => 1,
        ]);

        Employee::create([
            'sss_no' => $faker->unique()->randomDigit(),
            'phic_no' => $faker->unique()->randomDigit(),
            'tin_no' => $faker->unique()->randomDigit(),
            'datetime_hired' => $faker->unique()->date(),
            'datetime_resigned' => $faker->unique()->date(),
            'personality_id' => 2,
        ]);

        Customer::create([
            'group_id' => 1,
            'passbook_no' => $faker->unique()->randomDigit(),
            'loan_count' => $faker->unique()->randomDigit(),
            'enable_mortuary' => $faker->numberBetween(1,2),
            'mortuary_coverage_start' => null,
            'mortuary_coverage_end' => null,
            'personality_id' => 3,
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

        User_Account::create([
            'last_name' => 'Eric',
            'first_name' => 'Eric',
            'middle_name' => 'Eric',
            'email' => 'Eric@email.com',
            'password' => Hash::make('password'),
            'employee_id' => 2,
            'status_id' => 1,
        ]);

        //here is the permission
        // Fetch all document maps
        $documentMaps = Document_Map::all();

        // Fetch all document permissions
        $documentPermissions = Document_Permission_Map::all();

        // Loop through each document map and permission, and assign to user_id 1
        foreach ($documentMaps as $documentMap) {
            foreach ($documentPermissions as $documentPermission) {
                Document_Permission::create([
                    'user_id' => 1, // Assuming you are granting permissions to user with ID 1
                    'document_map_code' => $documentMap->id, // Use document map id as code
                    'document_permission' => $documentPermission->id, // Use document permission id
                    'datetime_granted' => now() // Current timestamp
                ]);
            }
        }

        Loan_Count::create([
            'loan_count' => 1,
            'min_amount' => 5000.00,
            'max_amount' => 15000.00,
        ]);

        Loan_Count::create([
            'loan_count' => 2,
            'min_amount' => 15000.00,
            'max_amount' => 30000.00,
        ]);

        Loan_Count::create([
            'loan_count' => 3,
            'min_amount' => 30000.00,
            'max_amount' => 60000.00,
        ]);

        //there are four (4) predefined duration in the database
        Payment_Frequency::create([
            'description' => 'Weekly',
            'days_interval' => 7,
            'notes' => 'Weekly Payments',
        ]);

        Payment_Frequency::create([
            'description' => 'Monthly',
            'days_interval' => 30, // Or 31 depending on the month
            'notes' => 'Monthly Payments',
        ]);

        Payment_Frequency::create([
            'description' => 'Quarterly',
            'days_interval' => 90, // Or 91, 92 depending on the quarter
            'notes' => 'Quarterly Payments',
        ]);

        Payment_Frequency::create([
            'description' => 'Annual',
            'days_interval' => 365, // Or 366 in leap years
            'notes' => 'Annual Payments',
        ]);


        //there are three (3) predefined frequency in the database
        Payment_Duration::create([
            'description' => '12 Weeks',
            'number_of_payments' => 12,
            'notes' => 'For 12 Weekly Payments',
        ]);

        Payment_Duration::create([
            'description' => '6 Months',
            'number_of_payments' => 26, // Assuming 4 weeks per month
            'notes' => 'For 6 Monthly Payments',
        ]);

        Payment_Duration::create([
            'description' => '1 Year',
            'number_of_payments' => 52, // Assuming 4 weeks per month
            'notes' => 'For 12 Monthly Payments',
        ]);
    }
}
