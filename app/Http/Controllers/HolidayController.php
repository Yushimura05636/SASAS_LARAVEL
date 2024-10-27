<?php

namespace App\Http\Controllers;

use App\Http\Requests\HolidayStoreRequest;
use App\Http\Requests\HolidayUpdateRequest;
use App\Interface\Service\HolidayServiceInterface;

class HolidayController extends Controller
{
    private $holidayService;

    public function __construct(HolidayServiceInterface $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index()
    {
        return $this->holidayService->findHolidays();
    }

    public function store(HolidayStoreRequest $request)
    {
        return $this->holidayService->createHoliday($request);
    }

    public function show(int $id)
    {
        return $this->holidayService->findHolidayById($id);
    }

    public function update(HolidayUpdateRequest $request, int $id)
    {
        return $this->holidayService->updateHoliday($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->holidayService->deleteHoliday($id);
    }
}
