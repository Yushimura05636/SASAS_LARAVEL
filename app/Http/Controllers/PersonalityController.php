<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalityStoreRequest;
use App\Http\Requests\PersonalityUpdateRequest;
use App\Interface\Service\PersonalityServiceInterface;
use App\Models\Personality;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PersonalityController extends Controller
{

    private $personalityService;
    public function __construct(PersonalityServiceInterface $personalityService)
    {
        $this->personalityService = $personalityService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => "Error! does not know why?", // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        return $this->personalityService->findPersonality();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->personalityService->createPersonality($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->personalityService->findPersonalityById($id);

        // $first_name = "Justen Cole I";
        // $family_name = 'Leola Williamson Jr.';
        // $middle_name = 'Ressie Schaefer';

        // $personality = Personality::where('first_name', $first_name)
        //     ->where('family_name', $family_name)
        //         ->where('middle_name', $middle_name)
        //             ->first();

        // $output = [
        //     "first_name" => $personality->first_name,
        //     "family_name" => $personality->family_name,
        //     'middle_name' => $personality->middle_name,
        // ];

        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => "Error! does not know why?", // Assuming you want to return modeltype
        //     'data' => $output, // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        return $this->personalityService->updatePersonality($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // return response()->json([
        //     'status' => 'error', // Or 'success' depending on your logic
        //     'message' => "Error! does not know why?", // Assuming you want to return modeltype
        //     'data' => [], // You can include additional data if needed
        //     'errors' => [], // You can include any errors if applicable
        // ], Response::HTTP_EXPECTATION_FAILED);
        return $this->personalityService->deletePersonality($id);
    }
}
