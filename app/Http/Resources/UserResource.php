<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>                 $this->id,
            'last_name' =>          $this->last_name,
            'first_name' =>         $this->first_name,
            'middle_name' =>        $this->middle_name,
            'email' =>              $this->email, // Access the email through the relationship
            'phone_number' =>       $this->phone_number,
            'employee_id' =>        $this->employee_id,// Access the employee ID
            'created_at' =>         $this->created_at,
            'updated_at' =>         $this->updated_at,
            'password' =>           $this->password,
            'customer_id' =>         $this->customer_id,
        ];
    }
}
