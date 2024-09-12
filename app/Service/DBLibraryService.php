<?php

namespace App\Service;

use App\Http\Resources\DBLibraryResource;
use App\Interface\Repository\DBLibraryRepositoryInterface;
use App\Interface\Service\DBLibraryServiceInterface;
use Illuminate\Http\Response;

class DBLibraryService implements DBLibraryServiceInterface
{
    private $dbLibraryRepository;

    public function __construct(DBLibraryRepositoryInterface $dbLibraryRepository)
    {
        $this->dbLibraryRepository = $dbLibraryRepository;
    }

    public function findEntries(string $modeltype)
    {
        $library = $this->dbLibraryRepository->findMany($modeltype);
        return DBLibraryResource::collection($library);
    }

    public function findEntry(string $modeltype, int $id)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => $modeltype, // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        $library = $this->dbLibraryRepository->findOneById($modeltype, $id);
        return new DBLibraryResource($library);
    }

    public function createEntry(object $payload)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => ' here in createdEntryservice field', // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        $library = $this->dbLibraryRepository->create($payload);
        return new DBLibraryResource($library);
    }

    public function updateEntryById(object $payload, int $id)
    {
        $library = $this->dbLibraryRepository->update($id, $payload);

        return new dbLibraryResource($library);
    }

    public function deleteEntryById(object $payload, int $id)
    {
        return $library = $this->dbLibraryRepository->delete($payload, $id);
    }
}
