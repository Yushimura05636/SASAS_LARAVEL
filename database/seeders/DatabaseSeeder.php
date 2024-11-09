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
use App\Models\Document_Status_Code;
use App\Models\Factor_Rate;
use App\Models\Fees;
use App\Models\Holiday;
use App\Models\Loan_Count;
use App\Models\Payment_Duration;
use App\Models\Payment_Frequency;
use App\Models\Personality; #Done
use App\Models\Requirements;
use App\Models\Spouse;
use PHPUnit\Metadata\Version\Requirement;

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

        for($i = 0; $i < 3; $i++)
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
            // 'city',
            'country',
            'province',
            // 'document_map',
            // 'customer_group',
            // 'personality_status_map',
        ];

        // Defining unique value arrays beforehand


        $civilStatusNames = ['Married', 'Widowed', 'Single'];
        $genderNames = ['Male', 'Female'];
        $creditStatusNames = ['Active', 'Inactive', 'Suspended', 'Blacklisted'];
        $userAccountStatusNames = ['Active', 'Inactive'];
        $documentPermissionNames = ['CREATE', 'UPDATE', 'DELETE', 'VIEW'];
        $nameTypes = ['Employee', 'Customer'];
        $document_status_code = ['Pending', 'Approved', 'Reject', 'Active', 'Inactive'];
        $personality_status_map = ['Pending', 'Approved','Reject','Active','Inactive'];
        $city = [
            'Davao City',
            'Cagayan de Oro',
            'Zamboanga City',
            'General Santos City',
            'Butuan City',
            'Iligan City',
            'Cotabato City',
            'Tagum City',
            'Pagadian City',
            'Dipolog City',
            'Surigao City',
            'Koronadal City',
            'Malaybalay City',
            'Valencia City',
            'Ozamiz City'
        ];

        $customer_group = [
            'Apple',
            'Banana',
            'Orange',
            'Mango',
            'Pineapple',
            'Grapes',
            'Strawberry',
            'Blueberry',
            'Watermelon',
            'Kiwi'
        ];

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
            'FEES',
            'LOAN_APPLICATIONS',
            'LOAN_APPLICATIONS_COMAKERS',
            'LOAN_RELEASES',
            'PAYMENTS',
            'PAYMENT_SCHEDULES',
            'PAYMENT_LINES',
            'CUSTOMER_REQUIREMENTS',
            'REQUIREMENTS',
        ];

        foreach ($city as $name) {
            City::createEntry($name); // No duplicates, direct array iteration
        }

        foreach ($customer_group as $name) {
            Customer_Group::createEntry($name); // No duplicates, direct array iteration
        }

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

        // Document Status Code entries
        foreach ($document_status_code as $name) {
            Document_Status_Code::createEntry($name); // No duplicates, direct array iteration
        }

        for($i = 0; $i < 100; $i++) {
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
                // case 'city':
                //     City::createEntry($faker->unique()->city());
                //     break;
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
                // case 'customer_group':
                //     Customer_Group::createEntry($description);
                //     break;
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

        Employee::create([
            'sss_no' => $faker->unique()->randomDigit(),
            'phic_no' => $faker->unique()->randomDigit(),
            'tin_no' => $faker->unique()->randomDigit(),
            'datetime_hired' => $faker->unique()->date(),
            'datetime_resigned' => $faker->unique()->date(),
            'personality_id' => 3,
        ]);

        Requirements::create([
            'description' => 'Barangay Certificate',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Birth Certificate',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Valid ID',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Proof of Address',
            'isActive' => 1,
        ]);

        //'LN' . '-' . $faker->randomDigit() . trim(strtoupper($faker->lexify('??????')), ' ')

        // for($i = 0; $i < 10; $i++)
        // {
        //     Customer::create([
        //         'group_id' => 1,
        //         'passbook_no' => $faker->randomDigitNotZero() . $faker->randomNumber(),
        //         'loan_count' => 1,
        //         'enable_mortuary' => $faker->numberBetween(1,2),
        //         'mortuary_coverage_start' => null,
        //         'mortuary_coverage_end' => null,
        //         'personality_id' => $faker->numberBetween(3, 14),
        //     ]);
        // }

        User_Account::create([
            'last_name' => 'Eric',
            'first_name' => 'Eric',
            'middle_name' => 'Eric',
            'email' => 'ericramonesexb@gmail.com',
            'phone_number' => '09536404961',
            'password' => Hash::make('password'),
            'employee_id' => 1,
            'status_id' => 1,
        ]);

        User_Account::create([
            'last_name' => 'Layur',
            'first_name' => 'Yapsuri',
            'middle_name' => 'Kui',
            'email' => 'Layuor0@gmail.com',
            'phone_number' => '09606863294',
            'password' => Hash::make('password'),
            'employee_id' => 2,
            'status_id' => 1,
        ]);

        User_Account::create([
            'last_name' => 'Mars',
            'first_name' => 'Mars',
            'middle_name' => 'Mars',
            'email' => 'MarsAmarillento@email.com',
            'phone_number' => '09078625434',
            'password' => Hash::make('marsamarillento123'),
            'employee_id' => 3,
            'status_id' => 1,
        ]);

        //here is the permission
        // Fetch all document maps
        $documentMaps = Document_Map::all();

        // Fetch all document permissions
        $documentPermissions = Document_Permission_Map::all();

        for($i = 0; $i < 3; $i++)
        {
            // Loop through each document map and permission, and assign to user_id 1
            foreach ($documentMaps as $documentMap) {
                foreach ($documentPermissions as $documentPermission) {
                    Document_Permission::create([
                        'user_id' => $i+1, // Assuming you are granting permissions to user with ID 1
                        'document_map_code' => $documentMap->id, // Use document map id as code
                        'document_permission' => $documentPermission->id, // Use document permission id
                        'datetime_granted' => now() // Current timestamp
                    ]);
                }
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

        Factor_Rate::create([
            'payment_frequency_id' => 1,
            'payment_duration_id' => 1,
            'description' => 'weekly payment in 12 weeks',
            'value' => 12,
        ]);

        Fees::create([
            'description' => 'Transaction Fees',
            'amount' => 12.00000,
            'isactive' => 1,
            'notes' => 'Transaction fee',
        ]);

        Fees::create([
            'description' => 'Passbook fees',
            'amount' => 230.00000,
            'isactive' => 1,
            'notes' => 'Passbook fee',
        ]);

        //holy week
        Holiday::create([
            'description' => 'Maundy Thursday',
            'date' => '2024-11-06',
            'isActive' => 1,
        ]);

        Holiday::create([
            'description' => 'Good Friday',
            'date' => '2024-11-14',
            'isActive' => 1,
        ]);

        Holiday::create([
            'description' => 'Black Saturday',
            'date' => '2024-11-22',
            'isActive' => 1,
        ]);

        //customer
        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Carter',
            'middle_name' => 'John',
            'first_name' => 'Lily',
            'birthday' => '1985-06-15',
            'civil_status' => 1, // Single
            'gender_code' => 2, // Female
            'house_street' => '45 Pine Street',
            'purok_zone' => 'Zone 3',
            'postal_code' => '2500',
            'telephone_no' => '(045) 123-4567',
            'email_address' => 'ericramones1253@gmail.com',
            'cellphone_no' => '+63 917 765 4321',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 1, // Adjust based on your data
            'city_id' => 4, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 2, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null, // If applicable, otherwise set to NULL
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Ramirez',
            'middle_name' => 'Ana',
            'first_name' => 'Carlos',
            'birthday' => '1990-03-22',
            'civil_status' => 2, // Married
            'gender_code' => 1, // Male
            'house_street' => '102 Elm Road',
            'purok_zone' => 'Zone 1',
            'postal_code' => '2501',
            'telephone_no' => '(045) 987-6543',
            'email_address' => 'carlos.ramirez@example.com',
            'cellphone_no' => '+63 917 123 4567',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 2, // Adjust based on your data
            'city_id' => 6, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 3, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null, // If applicable, otherwise set to NULL
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Santos',
            'middle_name' => 'Maria',
            'first_name' => 'Juan',
            'birthday' => '1995-12-30',
            'civil_status' => 3, // Divorced
            'gender_code' => 1, // Male
            'house_street' => '12 Mango Avenue',
            'purok_zone' => 'Zone 2',
            'postal_code' => '2502',
            'telephone_no' => '(045) 456-7890',
            'email_address' => 'juan.santos@example.com',
            'cellphone_no' => '+63 912 345 6789',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 3, // Adjust based on your data
            'city_id' => 8, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 4, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null, // If applicable, otherwise set to NULL
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Lim',
            'middle_name' => 'Wong',
            'first_name' => 'Mei',
            'birthday' => '1992-09-14',
            'civil_status' => 1, // Single
            'gender_code' => 2, // Female
            'house_street' => '99 Orchid Street',
            'purok_zone' => 'Zone 4',
            'postal_code' => '2503',
            'telephone_no' => '(045) 321-6547',
            'email_address' => 'mei.lim@example.com',
            'cellphone_no' => '+63 917 876 5432',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 1, // Adjust based on your data
            'city_id' => 1, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 1, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null, // If applicable, otherwise set to NULL
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Santos J',
            'middle_name' => 'Maria',
            'first_name' => 'Juan',
            'birthday' => '1985-02-25',
            'civil_status' => 2, // Married
            'gender_code' => 1, // Male
            'house_street' => '45 Rose Avenue',
            'purok_zone' => 'Zone 5',
            'postal_code' => '2504',
            'telephone_no' => '(045) 222-3333',
            'email_address' => 'juan.santos@example.com',
            'cellphone_no' => '+63 918 765 4321',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 1, // Adjust based on your data
            'city_id' => 1, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 1, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null,
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'De Guzman',
            'middle_name' => 'Alfonso',
            'first_name' => 'Ana',
            'birthday' => '1990-06-30',
            'civil_status' => 1, // Single
            'gender_code' => 2, // Female
            'house_street' => '88 Daisy Lane',
            'purok_zone' => 'Zone 3',
            'postal_code' => '2505',
            'telephone_no' => '(045) 123-4567',
            'email_address' => 'ana.deguzman@example.com',
            'cellphone_no' => '+63 915 543 2100',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 1, // Adjust based on your data
            'city_id' => 1, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 1, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => null,
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Reyes',
            'middle_name' => 'Lorenzo',
            'first_name' => 'Pedro',
            'birthday' => '1982-12-12',
            'civil_status' => 1, // Married
            'gender_code' => 1, // Male
            'house_street' => '32 Lily Boulevard',
            'purok_zone' => 'Zone 1',
            'postal_code' => '2506',
            'telephone_no' => '(045) 654-3210',
            'email_address' => 'pedro.reyes@example.com',
            'cellphone_no' => '+63 911 234 5678',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 1, // Adjust based on your data
            'city_id' => 1, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 1, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => 'Frequent traveler.',
        ]);

        Personality::create([
            'datetime_registered' => now(),
            'family_name' => 'Tan',
            'middle_name' => 'Li',
            'first_name' => 'Xiu',
            'birthday' => '1995-07-15',
            'civil_status' => 1, // Single
            'gender_code' => 2, // Female
            'house_street' => '7 Bamboo Street',
            'purok_zone' => 'Zone 6',
            'postal_code' => '2507',
            'telephone_no' => '(045) 111-2222',
            'email_address' => 'xiu.tan@example.com',
            'cellphone_no' => '+63 917 123 4567',
            'name_type_code' => 2,
            'personality_status_code' => 2,
            'barangay_id' => 5, // Adjust based on your data
            'city_id' => 1, // Adjust based on your data
            'country_id' => 1, // Adjust based on your data
            'province_id' => 1, // Adjust based on your data
            'credit_status_id' => 1, // Adjust based on your data
            'notes' => 'New to the area.',
        ]);


        Customer::create([
            'group_id' => 1, // Adjust as necessary
            'passbook_no' => 11123456, // Example passbook number
            'loan_count' => 1, // Initial loan count
            'enable_mortuary' => 1, // Enable mortuary coverage (1 for yes, 0 for no)
            'mortuary_coverage_start' => now(), // Start date for coverage
            'mortuary_coverage_end' => now()->addYear(), // End date for coverage, one year from now
            'personality_id' => 3, // ID of Lily Carter
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 1, // Adjust as necessary
            'passbook_no' => 11123457, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 4, // ID of Carlos Ramirez
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 1, // Adjust as necessary
            'passbook_no' => 11123458, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 0, // Disable mortuary coverage
            'mortuary_coverage_start' => null, // No coverage
            'mortuary_coverage_end' => null,
            'personality_id' => 5, // ID of Juan Santos
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 1, // Adjust as necessary
            'passbook_no' => 1123459, // Example passbook number
            'loan_count' => 1, // Initial loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 6, // ID of Mei Lim
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 2, // Group ID for all entries
            'passbook_no' => 11123464, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 7, // ID of another personality
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 2, // Group ID for all entries
            'passbook_no' => 11123465, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 8, // ID of another personality
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 2, // Group ID for all entries
            'passbook_no' => 11123466, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 9, // ID of another personality
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Customer::create([
            'group_id' => 2, // Group ID for all entries
            'passbook_no' => 11123467, // Example passbook number
            'loan_count' => 1, // Example loan count
            'enable_mortuary' => 1, // Enable mortuary coverage
            'mortuary_coverage_start' => now(),
            'mortuary_coverage_end' => now()->addYear(),
            'personality_id' => 10, // ID of another personality
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
