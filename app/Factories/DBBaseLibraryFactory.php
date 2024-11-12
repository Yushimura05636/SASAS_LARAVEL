<?php

namespace App\Factories;

use App\Http\Requests\DBLibraryStoreRequest;
use App\Http\Resources\DBLibraryResource;
use App\Models\Barangay; #Done
use App\Models\Branch; #Done
use App\Models\City; #Done
use App\Models\Civil_Status; #Done
use App\Models\Document_Status_code;
use App\Models\Gender_Map; #Done
use App\Models\Country; #Done
use App\Models\Province; #Done
use App\Models\Credit_Status; #Done
use App\Models\Personality_Status_Map; #Done
use App\Models\User_Account_Status; #Done
use App\Models\Document_Map; #Done
use App\Models\Document_Permission_Map; #Done
use App\Models\Name_Type; #Done
use App\Models\Customer_Group; #Done
use App\Models\Requirements;
use App\Models\User_Account;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/*
    KINI na section SA APP\\ dinhi pag pili ug unsa na model type ang gamit pang insert, delete, or create
    sa mga data example if gusto nimo mag insert ug description na PHILIPPINES sa country na table
    mag pili si factory na imong gi pasa is either kung country na model type imong gi pasa

    examplpe::
    protected $modelType;

    $modelType::createEntry('barangay', 'agdao');


*/

class DBBaseLibraryFactory
{
    public $modeltype;
    public $id;
    public $description;
    private $action;
    public $collector_id;

    public $bool;

    private $paginate;

    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }
    public function __construct(string $modeltype = null, object $payload = null, int $id = null, string $action = null)
    {

        //if the $modeltype variable is not null
        if (!is_null($modeltype) && $modeltype !== '') {
            $this->modeltype = $modeltype;
        }

        //check if $payload object is not null
        if(!is_null($payload) && $payload !== '')
        {
            $this->description = $payload->description;
            //else the $modeltype will use the object $payload->modeltype
            $this->modeltype = $payload->modeltype;

            $this->collector_id = $payload->collector_id;
        }
        $this->id = $id;
        $this->action = $action;

        switch($this->action)
        {
            case 'create':
                $this->createEntry($this->modeltype, $this->description, $this->collector_id);
                break;
            case 'findOne':
                $this->findOne($this->modeltype, $this->id);
                break;
            case 'find':
                $this->findMany($this->modeltype);
                break;
            case 'update':
                $this->updateEntry($this->modeltype, $this->id, $this->description, $this->collector_id);
                break;
            case 'delete':
                $this->bool = $this->deleteEntry($this->modeltype, $this->id);
                break;
            default:
                'Error';
        }
    }
    public static function createEntry($modeltype, $description, $collector_id)
{
    DB::beginTransaction(); // Start the transaction
    try {

        $fillable = [
            'description',
            'collector_id',
        ];

        // Switch logic for creating entries based on model type
        switch ($modeltype) {
            case 'barangay':
                Barangay::createEntry($description);
                break;
            case 'branch':
                Branch::createEntry($description);
                break;
            case 'city':
                City::createEntry($description);
                break;
            case 'civil_status':
                Civil_Status::createEntry($description);
                break;
            case 'gender_map':
                Gender_Map::createEntry($description);
                break;
            case 'country':
                Country::createEntry($description);
                break;
            case 'province':
                Province::createEntry($description);
                break;
            case 'credit_status':
                Credit_Status::createEntry($description);
                break;
            case 'personality_status_map':
                Personality_Status_Map::createEntry($description);
                break;
            case 'user_account_status':
                User_Account_Status::createEntry($description);
                break;
            case 'document_map':
                Document_Map::createEntry($description);
                break;
            case 'document_permission_map':
                Document_Permission_Map::createEntry($description);
                break;
            case 'name_type':
                Name_Type::createEntry($description);
                break;
            case 'customer_group':
                // throw new \Exception($modeltype == 'customer_group');
                Customer_Group::createEntry($description, $collector_id);
                break;
            case 'document_status_code':
                Document_Status_code::createEntry($description);
                break;
            case 'requirement':
                Requirements::createEntry($description);
                break;
            default:
                throw new \InvalidArgumentException("Unknown model type: $modeltype");
        }

        DB::commit(); // Commit the transaction if everything went well

        return response()->json([
            'status' => 'success',
            'message' => "$modeltype entry created successfully",
            'data' => [], // Optionally return data if needed
        ]);

    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction if something goes wrong

        return response()->json([
            'status' => 'error',
            'message' => "Failed to create $modeltype entry",
            'errors' => $e->getMessage(),
        ], 500); // Return an error response with status code 500
    }
}

    public static function findOne(string $modeltype, int $id)
    {
        // return response()->json([
        //             'status' => 'error', // Or 'success' depending on your logic
        //             'message' => $modeltype, // Assuming you want to return modeltype
        //             'data' => [], // You can include additional data if needed
        //             'errors' => [], // You can include any errors if applicable
        //         ], Response::HTTP_EXPECTATION_FAILED);
        switch ($modeltype) {
            case 'barangay':
                return Barangay::findOne($id);
            case 'branch':
                return Branch::findOne($id);
            case 'city':
                return City::findOne($id);
            case 'civil_status':
                return Civil_Status::findOne($id);
            case 'gender_map':
                return Gender_Map::findOne($id);
            case 'country':
                return Country::findOne($id);
            case 'province':
                return Province::findOne($id);
            case 'credit_status':
                return Credit_Status::findOne($id);
            case 'personality_status_map':
                return Personality_Status_Map::findOne($id);
            case 'user_account_status':
                return User_Account_Status::findOne($id);
            case 'document_map':
                return Document_Map::findOne($id);
            case 'document_permission_map':
                return Document_Permission_Map::findOne($id);
            case 'name_type':
                return Name_Type::findOne($id);
            case 'customer_group':
                $customer_group = Customer_Group::findOne($id)->first();

                //get the collector name
                $user_details = User_Account::where('id', $customer_group['collector_id']);

                $customer_group['last_name'] = $user_details->last_name;
                $customer_group['first_name'] = $user_details->first_name;
                $customer_group['middle_name'] = $user_details->middle_name;

                return $customer_group;
            case 'document_status_code':
                return Document_Status_code::findOne($id);
            case 'requirement':
                return Requirements::findOne($id);
            default:
                throw new \InvalidArgumentException("Unknown model type: $modeltype");
        }
    }

    public static function findMany($modeltype)
    {
        switch ($modeltype) {
            case 'barangay':
                return Barangay::findMany();
            case 'branch':
                return Branch::findMany();
            case 'city':
                return City::findMany();
            case 'civil_status':
                return Civil_Status::findMany();
            case 'gender_map':
                return Gender_Map::findMany();
            case 'country':
                return Country::findMany();
            case 'province':
                return Province::findMany();
            case 'credit_status':
                return Credit_Status::findMany();
            case 'personality_status_map':
                return Personality_Status_Map::findMany();
            case 'user_account_status':
                return User_Account_Status::findMany();
            case 'document_map':
                return Document_Map::findMany();
            case 'document_permission_map':
                return Document_Permission_Map::findMany();
            case 'name_type':
                return Name_Type::findMany();
            case 'customer_group':
                $customer_group = Customer_Group::findMany();

                foreach($customer_group as $group)
                {
                    if(!is_null($group))
                    {
                        //get the collector name
                        $user_details = User_Account::where('id', $group['collector_id'])->first();

                        if(!is_null($user_details))
                        {
                            $group['last_name'] = $user_details['last_name'];
                            $group['first_name'] = $user_details['first_name'];
                            $group['middle_name'] = $user_details['middle_name'];
                        }
                    }
                }

                return $customer_group;
            case 'document_status_code':
                return Document_Status_code::findMany();
            case 'requirement':
                return Requirements::findMany();
            default:
                throw new \InvalidArgumentException("Unknown model type: $modeltype");
        }
    }

    public static function deleteEntry(string $modelType, int $id)
    {
        switch ($modelType) {
            case 'barangay':
                return Barangay::deleteEntry($id);
            case 'branch':
                return Branch::deleteEntry($id);
            case 'city':
                return City::deleteEntry($id);
            case 'civil_status':
                return Civil_Status::deleteEntry($id);
            case 'gender_map':
                return Gender_Map::deleteEntry($id);
            case 'country':
                return Country::deleteEntry($id);
            case 'province':
                return Province::deleteEntry($id);
            case 'credit_status':
                return Credit_Status::deleteEntry($id);
            case 'personality_status_map':
                return Personality_Status_Map::deleteEntry($id);
            case 'user_account_status':
                return User_Account_Status::deleteEntry($id);
            case 'document_map':
                return Document_Map::deleteEntry($id);
            case 'document_permission_map':
                return Document_Permission_Map::deleteEntry($id);
            case 'name_type':
                return Name_Type::deleteEntry($id);
            case 'customer_group':
                return Customer_Group::deleteEntry($id);
            case 'document_status_code':
                return Document_Status_code::deleteEntry($id);
            case 'requirement':
                return Requirements::deleteEntry($id);
            default:
                throw new \InvalidArgumentException("Unknown model type: $modelType");
        }
    }

public static function updateEntry($modelType, $id, $description, $collector_id)
{
    return DB::transaction(function () use ($modelType, $id, $description, $collector_id) {
        switch ($modelType) {
            case 'barangay':
                return Barangay::updateEntry($id, $description);
            case 'branch':
                return Branch::updateEntry($id, $description);
            case 'city':
                return City::updateEntry($id, $description);
            case 'civil_status':
                return Civil_Status::updateEntry($id, $description);
            case 'gender_map':
                return Gender_Map::updateEntry($id, $description);
            case 'country':
                return Country::updateEntry($id, $description);
            case 'province':
                return Province::updateEntry($id, $description);
            case 'credit_status':
                return Credit_Status::updateEntry($id, $description);
            case 'personality_status_map':
                return Personality_Status_Map::updateEntry($id, $description);
            case 'user_account_status':
                return User_Account_Status::updateEntry($id, $description);
            case 'document_map':
                return Document_Map::updateEntry($id, $description);
            case 'document_permission_map':
                return Document_Permission_Map::updateEntry($id, $description);
            case 'name_type':
                return Name_Type::updateEntry($id, $description);
            case 'customer_group':
                //throw new \Exception('stop');
                return Customer_Group::updateEntry($id, $description, $collector_id);
            case 'document_status_code':
                return Document_Status_code::updateEntry($id, $description);
            case 'requirement':
                return Requirements::updateEntry($id, $description);
            default:
                throw new \InvalidArgumentException("Unknown model type: $modelType");
        }
    });
}

}
