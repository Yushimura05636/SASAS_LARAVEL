<?php

namespace App\Repository;

use App\Interface\Repository\RepositoryInterface;
use App\Factories\DBBaseLibraryFactory;
use App\Interface\Repository\DBLibraryRepositoryInterface;
use App\Models\DBLibrary;
use Illuminate\Http\Response;

class DBLibraryRepository implements DBLibraryRepositoryInterface
{
    public function findMany(string $modeltype)
    {
        $dblibrary = new DBBaseLibraryFactory();
        return $dblibrary::findMany($modeltype);
    }

    public function findOneById(string $modeltype, int $id)
    {
        // return response()->json([
        //             'status' => 'error', // Or 'success' depending on your logic
        //             'message' => $modeltype, // Assuming you want to return modeltype
        //             'data' => [], // You can include additional data if needed
        //             'errors' => [], // You can include any errors if applicable
        //         ], Response::HTTP_EXPECTATION_FAILED);
        $dblibrary = new DBBaseLibraryFactory($modeltype, null, $id, null);
        return $dblibrary::findOne($modeltype, $id);
    }

    public function create(object $payload)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => ' here in createdEntryrepository field', // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        return new DBBaseLibraryFactory(null, $payload, 0, "create");
    }

    public function update(int $id, object $payload)
    {
        return new DBBaseLibraryFactory(null, $payload, $id, 'update');
    }

    public function delete(object $payload, int $id)
    {
        $object = new DBBaseLibraryFactory(null, $payload, $id, 'delete');

        if($object->bool == true)
        {
            return response()->json([
            'status' => 'success', // Or 'success' depending on your logic
            'message' => ' successfully deleted!', // Assuming you want to return modeltype
            'data' => [], // You can include additional data if needed
            'errors' => [], // You can include any errors if applicable
            ], Response::HTTP_OK);
        }

        return response()->json([
            'status' => 'error', // Or 'success' depending on your logic
            'message' => 'deletion error!', // Assuming you want to return modeltype
            'data' => [], // You can include additional data if needed
            'errors' => [], // You can include any errors if applicable
        ], Response::HTTP_EXPECTATION_FAILED);
    }
}
