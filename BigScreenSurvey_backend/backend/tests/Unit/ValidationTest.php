<?php

namespace Tests\Unit\Validation;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\AdminLoginRequest;

class AdminLoginValidationTest extends TestCase
{
    public function test_login_validation_passes_with_valid_data()
    {
        $request = new AdminLoginRequest();
        $validator = Validator::make([
            'username' => 'admin_test',
            'password' => 'password123'
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_login_validation_fails_with_missing_username()
    {
        $request = new AdminLoginRequest();
        $validator = Validator::make([
            'password' => 'password123'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('username'));
    }

    public function test_login_validation_fails_with_short_password()
    {
        $request = new AdminLoginRequest();
        $validator = Validator::make([
            'username' => 'admin_test',
            'password' => '123'
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('password'));
    }
}