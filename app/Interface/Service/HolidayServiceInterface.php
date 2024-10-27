<?php

namespace App\Interface\Service;

interface HolidayServiceInterface
{
    public function findHolidays();

    public function findHolidayById(int $id);

    public function createHoliday(object $payload);

    public function updateHoliday(object $payload, int $id);

    public function deleteHoliday(int $id);
}
