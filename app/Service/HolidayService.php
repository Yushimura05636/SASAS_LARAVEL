<?php 

namespace App\Service;

use App\Http\Resources\HolidayResource;
use App\Interface\Repository\HolidayRepositoryInterface;
use App\Interface\Service\HolidayServiceInterface;

class HolidayService implements HolidayServiceInterface
{
    private $holidayRepository;

    public function __construct(HolidayRepositoryInterface $holidayRepository)
    {
        $this->holidayRepository = $holidayRepository;
    }

    public function findHolidays()
    {
        $holiday = $this->holidayRepository->findMany();
        return HolidayResource::collection($holiday);
    }

    public function findHolidayById(int $id)
    {
        $holiday = $this->holidayRepository->findOneById($id);
        return new HolidayResource($holiday);

    }

    public function createHoliday(object $payload)
    {
        $holiday = $this->holidayRepository->create($payload);
        return new HolidayResource($holiday);
    }

    public function updateHoliday(object $payload, int $id)
    {
        $holiday = $this->holidayRepository->update($payload, $id);
        return new HolidayResource($holiday);
    }

    public function deleteHoliday(int $id)
    {
        return $this->holidayRepository->delete($id);

    }
}