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
        $documentPermissionNames = ['CREATE', 'UPDATE', 'DELETE', 'VIEW', 'REJECT', 'APPROVED'];
        $nameTypes = ['Employee', 'Customer'];
        $document_status_code = ['Pending', 'Approved', 'Reject', 'Active', 'Inactive'];
        $personality_status_map = ['Pending', 'Approved','Reject','Active','Inactive'];
        
        $city = [
            // NCR
            'Quezon City',
            'Manila',
            'Caloocan',
            'Pasig',
            'Makati',
            'Taguig',
            'Pasay',
            'Las Piñas',
            'Muntinlupa',
            'Malabon',
            'Navotas',
            'Parañaque',
            'Valenzuela',
            'Marikina',
            // Luzon
            'Baguio City',
            'San Fernando City (La Union)',
            'Angeles City',
            'Olongapo City',
            'Batangas City',
            'Lucena City',
            'Calamba City',
            'Antipolo City',
            'Puerto Princesa City',
            // Visayas
            'Cebu City',
            'Mandaue City',
            'Lapu-Lapu City',
            'Iloilo City',
            'Bacolod City',
            'Dumaguete City',
            'Tagbilaran City',
            'Tacloban City',
            // Mindanao
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
        
        $barangay = [
            // NCR
            'Barangay Bagumbayan (Quezon City)',
            'Barangay Poblacion (Makati)',
            'Barangay Ususan (Taguig)',
            'Barangay San Isidro (Parañaque)',
            'Barangay Malibay (Pasay)',
            'Barangay Longos (Malabon)',
            'Barangay Dagat-dagatan (Navotas)',
            'Barangay Talipapa (Caloocan)',
            'Barangay San Bartolome (Novaliches, Quezon City)',
            // Luzon
            'Barangay Mines View (Baguio City)',
            'Barangay San Fernando (San Fernando City, La Union)',
            'Barangay Balibago (Angeles City)',
            'Barangay Barretto (Olongapo City)',
            'Barangay Pallocan (Batangas City)',
            'Barangay Ibabang Dupay (Lucena City)',
            'Barangay Halang (Calamba City)',
            'Barangay San Roque (Antipolo City)',
            'Barangay San Pedro (Puerto Princesa City)',
            // Visayas
            'Barangay Lahug (Cebu City)',
            'Barangay Pajo (Lapu-Lapu City)',
            'Barangay Tipolo (Mandaue City)',
            'Barangay Jaro (Iloilo City)',
            'Barangay Mandalagan (Bacolod City)',
            'Barangay Piapi (Dumaguete City)',
            'Barangay Cogon (Tagbilaran City)',
            'Barangay San Jose (Tacloban City)',
            // Mindanao
            'Barangay Buhangin (Davao City)',
            'Barangay Carmen (Cagayan de Oro)',
            'Barangay Talon-Talon (Zamboanga City)',
            'Barangay San Isidro (General Santos City)',
            'Barangay Baan Riverside (Butuan City)',
            'Barangay Poblacion (Iligan City)',
            'Barangay Bagua (Cotabato City)',
            'Barangay Magugpo East (Tagum City)',
            'Barangay Balangasan (Pagadian City)',
            'Barangay Miputak (Dipolog City)',
            'Barangay Washington (Surigao City)',
            'Barangay Gen. Paulino Santos (Koronadal City)',
            'Barangay Kalasungay (Malaybalay City)',
            'Barangay Poblacion (Valencia City)',
            'Barangay Maningcol (Ozamiz City)'
        ];

        $provinces = [
            // NCR (No provinces, but for reference, it's the National Capital Region)
            'Metro Manila',
        
            // Luzon
            'Abra',
            'Albay',
            'Aurora',
            'Bataan',
            'Batangas',
            'Benguet',
            'Bulacan',
            'Cagayan',
            'Camarines Norte',
            'Camarines Sur',
            'Catanduanes',
            'Cavite',
            'Ilocos Norte',
            'Ilocos Sur',
            'Isabela',
            'Kalinga',
            'La Union',
            'Laguna',
            'Marinduque',
            'Masbate',
            'Nueva Ecija',
            'Nueva Vizcaya',
            'Occidental Mindoro',
            'Oriental Mindoro',
            'Palawan',
            'Pampanga',
            'Pangasinan',
            'Quezon',
            'Quirino',
            'Rizal',
            'Romblon',
            'Sorsogon',
            'Tarlac',
            'Zambales',
        
            // Visayas
            'Aklan',
            'Antique',
            'Bohol',
            'Capiz',
            'Cebu',
            'Eastern Samar',
            'Guimaras',
            'Iloilo',
            'Leyte',
            'Negros Occidental',
            'Negros Oriental',
            'Northern Samar',
            'Samar (Western Samar)',
            'Siquijor',
            'Southern Leyte',
        
            // Mindanao
            'Agusan del Norte',
            'Agusan del Sur',
            'Basilan',
            'Bukidnon',
            'Camiguin',
            'Compostela Valley (Davao de Oro)',
            'Davao del Norte',
            'Davao del Sur',
            'Davao Occidental',
            'Davao Oriental',
            'Dinagat Islands',
            'Lanao del Norte',
            'Lanao del Sur',
            'Maguindanao del Norte',
            'Maguindanao del Sur',
            'Misamis Occidental',
            'Misamis Oriental',
            'North Cotabato (Cotabato)',
            'Sarangani',
            'South Cotabato',
            'Sultan Kudarat',
            'Surigao del Norte',
            'Surigao del Sur',
            'Tawi-Tawi',
            'Zamboanga del Norte',
            'Zamboanga del Sur',
            'Zamboanga Sibugay'
        ];
        
        $country = [
            'Philippines'
        ];

        //document
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
            'DASHBOARD_EMPLOYEES',
        ];

        foreach ($city as $name) {
            City::createEntry($name); // No duplicates, direct array iteration
        }

        foreach ($provinces as $name) {
            Province::createEntry($name); // No duplicates, direct array iteration
        }

        foreach ($barangay as $name) {
            Barangay::createEntry($name); // No duplicates, direct array iteration
        }

        foreach ($country as $name) {
            Country::createEntry($name); // No duplicates, direct array iteration
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

        Requirements::create([
            'description' => '2x2 picture ID',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Proof of Income',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'House Bills',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Passport',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'PSA birth certificate',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Driver`s license',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'TIN ID',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'ePhilSys',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'PhilHealth ID',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Postal ID',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'NBI Clearance',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Police clearance',
            'isActive' => 1,
        ]);
        Requirements::create([
            'description' => 'Senior Citizen ID',
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

        Personality::create([
            'datetime_registered'=>now(),
            'family_name'=>'Sastre',
            'middle_name'=>'Queroa',
            'first_name'=>'Adrian',
            //'description'=>$faker->sentence(6),
            'birthday'=>'2000-01-01',
            'civil_status'=>3,
            'gender_code'=>1,
            'house_street'=>'House No. 15, Purok 2, Julian Rivera Street, Barangay Agdao Proper, Davao City, 8000, Philippines',
            'purok_zone'=>'House No. 25, Purok 3, Zone 2, Rizal Street, Barangay Agdao Proper, Davao City, 8000, Philippines',
            'postal_code'=> '8000',
            'telephone_no'=>'',
            'email_address'=>'zipppycole@gmail.com',
            'cellphone_no'=> '09606863294',
            'name_type_code'=>1,
            'personality_status_code'=>2,
            //'branch_id'=>$faker->numberBetween(1,10),
            'barangay_id'=>1,
            'city_id'=>1,
            'country_id'=>1,
            'province_id'=>1,
            //'spouse_id'=>$faker->numberBetween(1,10),
            'credit_status_id'=>1,
        ]);

        Employee::create([
            'sss_no' => 3412345678,
            'phic_no' => 123456789012,
            'tin_no' => 123456789000,
            'datetime_hired' => now(),
            'personality_id' => 1,
        ]);

        User_Account::create([
            'status_id' => 1,
            'last_name' => 'Sastre',
            'first_name' => 'Adrian',
            'middle_name' => 'Queroa',
            'phone_number' => '09606863294',
            'password' => Hash::make('Lendcash123'),
            'email' => 'rosendosastrejr125@gmail.com',
            'employee_id' => 1,
            'customer_id' => null,
            'notes' => 'admin',
        ]);

        //here is the permission
        // Fetch all document maps
        $documentMaps = Document_Map::all();

        // Fetch all document permissions
        $documentPermissions = Document_Permission_Map::all();

        for($i = 0; $i < 1; $i++)
        {
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
        }

        Loan_Count::create([
            'loan_count' => 1,
            'min_amount' => 4000.00,
            'max_amount' => 10000.00,
        ]);

        Loan_Count::create([
            'loan_count' => 2,
            'min_amount' => 10000.00,
            'max_amount' => 20000.00,
        ]);

        Loan_Count::create([
            'loan_count' => 3,
            'min_amount' => 20000.00,
            'max_amount' => 30000.00,
        ]);

        //there are four (4) predefined duration in the database
        Payment_Frequency::create([
            'description' => 'Weekly',
            'days_interval' => 7,
            'notes' => 'Weekly Payments',
        ]);

        //there are three (3) predefined frequency in the database
        Payment_Duration::create([
            'description' => '15 Weeks',
            'number_of_payments' => 15,
            'notes' => 'For 15 Weekly Payments',
        ]);

        //there are three (3) predefined frequency in the database
        Payment_Duration::create([
            'description' => '24 Weeks',
            'number_of_payments' => 24,
            'notes' => 'For 24 Weekly Payments',
        ]);

        Factor_Rate::create([
            'payment_frequency_id' => 1,
            'payment_duration_id' => 1,
            'description' => 'weekly payment in 15 weeks',
            'value' => 30,
        ]);

        Factor_Rate::create([
            'payment_frequency_id' => 1,
            'payment_duration_id' => 1,
            'description' => 'weekly payment in 24 weeks',
            'value' => 30,
        ]);

        Fees::create([
            'description' => 'Transaction Loan Fee',
            'amount' => 100,
            'isactive' => 1,
            'notes' => 'Transaction Loan fee',
        ]);

        Fees::create([
            'description' => 'Membership Fees',
            'amount' => 100,
            'isactive' => 1,
            'notes' => 'Membership fee',
        ]);

        Fees::create([
            'description' => 'Passbook fees',
            'amount' => 200,
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
    }
}
