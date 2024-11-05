<?php

namespace App\Http\Controllers;

use App\Http\Requests\DBLibraryDeleteRequest;
use App\Http\Requests\DBLibraryShowRequest;
use App\Http\Requests\DBLibraryStoreRequest;
use App\Http\Requests\DBLibraryUpdateRequest;
use App\Interface\Service\DBLibraryServiceInterface;
use App\Models\DBLibrary;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;
use DebugBar\DebugBar;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Debug;
use Illuminate\Http\Response;

class DBLibraryController extends Controller
{
    private $dblibraryservice;

    public function __construct(DBLibraryServiceInterface $dblibraryservice)
    {
        $this->dblibraryservice = $dblibraryservice;
    }

    public function index(string $modeltype)
    {
        return $this->dblibraryservice->findEntries($modeltype);
    }

    public function store(DBLibraryStoreRequest $request)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => ' here in store field', // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        return $this->dblibraryservice->createEntry($request);
    }

    public $reqs;

    public function show(int $id, DBLibraryShowRequest $request)
    {
        $modeltype = '' . trim($request->input('modeltype'));
        // $modeltype = $request->query('modeltype');
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => $modeltype, // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        return $this->dblibraryservice->findEntry($modeltype, $id);
    }

    public function update(DBLibraryUpdateRequest $request, int $id)
    {
        return $this->dblibraryservice->updateEntryById($request, $id);
    }

    public function destroy(DBLibraryDeleteRequest $payload, int $id)
    {
        return $this->dblibraryservice->deleteEntryById($payload, $id);
    }
}
