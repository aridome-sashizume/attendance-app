<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
/**
 * FN002の使用技術としてFormRequestが指定されているため、
 * RegisterRequestのバリデーションルールとメッセージを利用する。
 */
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {

        /**
         * 要件(FN002)で FormRequest の使用が指定されているため、
         * RegisterRequest に定義したバリデーションルール・メッセージを利用する。
         */
        $request = new RegisterRequest;

        Validator::make(
            $input,
            $request->rules(),
            $request->messages()
        )->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
