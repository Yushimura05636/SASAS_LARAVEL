<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Interface\Service\CustomerServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    private $customerService;

    public function __construct(CustomerServiceInterface $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        return $this->customerService->findCustomers();
    }

    public function store(Request $request)
    {
        return $this->customerService->createCustomer($request);
    }

    public function show(int $id)
    {
        return $this->customerService->findCustomerById($id);
    }

    public function update(Request $request, int $id)
    {
        return $this->customerService->updateCustomer($request, $id);
    }

    public function destroy(int $id)
    {
        return $this->customerService->deleteCustomer($id);
    }

    public function test(int $id)
    {
        return $this->customerService->findCustomerByGroupId($id);
    }

    public function sendCode(Request $request)
    {
        //return response()->json(['message', $request->all()], Response::HTTP_INTERNAL_SERVER_ERROR);
        $request->validate(['email_address' => 'required']);
        $request->validate(['method' => 'required']);

        if($request->method == 'email_address')
        {
            $emailVerification = new EmailVerificationController($request);
            return $emailVerification->sendEmailClientVerification();
        }

        if($request->method == 'phone')
        {
            $request->validate([
                'phone_number' => 'required',
            ]);
            $request['phone_number'] = '+63' . $request->phone_number;
            $phoneVerification = new PhoneVerificationController($request);
            return $phoneVerification->sendPhoneVerification();
        }

        if($request->method == 'forgot')
        {
            $emailVerificaiton = new ForgotPasswordController($request);
            return $emailVerificaiton->sendResetLink();
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['email_address' => 'required']);
        // return response()->json([
        //     'message' => 'hello',
        // ], Response::HTTP_INTERNAL_SERVER_ERROR);
        // $request->validate(['code' => 'required', 'email' => 'required']);

        if($request->method == 'email_address')
        {
            $request->validate(['code' => 'required']);
            $verifyEmail = new EmailVerificationController($request);
            return $verifyEmail->verifyClientEmailCode();
        }

        if($request->method == 'phone')
        {
            $request->validate(['code' => 'required']);
            $request->validate(['phone_number' => 'required']);
            $verifyPhone = new PhoneVerificationController($request);
            return $verifyPhone->verifyPhoneCode();
        }

        if($request->method == 'forgot')
        {
            $request->validate(['token' => 'required']);
            $verifyEmailAndToken = new ForgotPasswordController($request);
            return $verifyEmailAndToken->verify();
        }
    }

}
