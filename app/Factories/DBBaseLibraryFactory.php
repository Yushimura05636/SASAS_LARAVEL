<?php

namespace App\Factories;

use App\Http\Requests\DBLibraryStoreRequest;
use App\Http\Resources\DBLibraryResource;
use App\Models\Barangay; #Done
use App\Models\Branch; #Done
use App\Models\City; #Done
use App\Models\Civil_Status; #Done
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
use Illuminate\Http\Response;

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
        }

        $this->id = $id;
        $this->action = $action;

        switch($this->action)
        {
            case 'create':
                $this->createEntry($this->modeltype, $this->description);
                break;
            case 'findOne':
                $this->findOne($this->modeltype, $this->id);
                break;
            case 'find':
                $this->findMany($this->modeltype);
                break;
            case 'update':
                $this->updateEntry($this->modeltype, $this->id, $this->description);
                break;
            case 'delete':
                $this->bool = $this->deleteEntry($this->modeltype, $this->id);
                break;
            default:
                'Error';
        }
    }
    public static function createEntry($modeltype, $description)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => $modeltype, // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);

        $fillable = [
            'description' => $description,
        ];

        switch ($modeltype) {
            case 'barangay':
                return Barangay::createEntry($description);
            case 'branch':
                return Branch::createEntry($description);
            case 'city':
                City::createEntry($description);
                return new City();
            case 'civil_status':
                return Civil_Status::createEntry($description);
            case 'gender_map':
                return Gender_Map::createEntry($description);
            case 'country':
                return Country::createEntry($description);
            case 'province':
                return Province::createEntry($description);
            case 'credit_status':
                return Credit_Status::createEntry($description);
            case 'personality_status_map':
                return Personality_Status_Map::createEntry($description);
            case 'user_account_status':
                return User_Account_Status::createEntry($description);
            case 'document_map':
                return Document_Map::createEntry($description);
            case 'document_permission_map':
                return Document_Permission_Map::createEntry($description);
            case 'name_type':
                return Name_Type::createEntry($description);
            case 'customer_group':
                return Customer_Group::createEntry($description);
            default:
                throw new \InvalidArgumentException("Unknown model type: ", $modeltype);
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
                return Customer_Group::findOne($id);
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
                return Customer_Group::findMany();
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
            default:
                throw new \InvalidArgumentException("Unknown model type: $modelType");
        }
    }

    public static function updateEntry($modelType, $id, $description)
    {
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
                return Customer_Group::updateEntry($id, $description);
            default:
                throw new \InvalidArgumentException("Unknown model type: $modelType");
        }
    }
}
