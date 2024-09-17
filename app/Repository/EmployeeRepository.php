<?php

namespace App\Repository;
use App\Interface\Repository\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Http\Response;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function findByMany()
    {
        return Employee::paginate(10);
    }

    public function findByOneId(int $id)
    {
        return Employee::findOrFail($id);
    }

    public function create(object $payload)
    {
        $employee = new Employee();
        $employee->sss_no = $payload->sss_no;
        $employee->phic_no = $payload->phic_no;
        $employee->tin_no = $payload->tin_no;
        $employee->datetime_hired = $payload->datetime_hired;
        $employee->datetime_resigned = $payload->datetime_resigned;
        $employee->personality_id = $payload->personality_id;
        $employee->save();

        return $employee->fresh();
    }

    public function update(object $payload, int $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->sss_no = $payload->sss_no;
        $employee->phic_no = $payload->phic_no;
        $employee->tin_no = $payload->tin_no;
        $employee->datetime_hired = $payload->datetime_hired;
        $employee->datetime_resigned = $payload->datetime_resigned;
        $employee->personality_id = $payload->personality_id;
        $employee->save();

        #importante mag fresh() pag human ug gamit sa save() para mu new instance napud ug dili
        #ma apil ang mga unsaved data sa last instance sa model
        return $employee->fresh();
    }

    public function delete(int $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'message' => 'success'
        ], Response::HTTP_OK);
    }
}
