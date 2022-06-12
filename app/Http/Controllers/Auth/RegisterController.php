<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;

/**
 * Class RegisterController.
 *
 * This controller handles the registration of new users as well as their
 * validation and creation. By default, this controller uses a trait to
 * provide this functionality without requiring any additional code.
 */
class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/projects';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array<string, mixed> $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array<string, mixed> $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create(
            [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'editor'   => $data['editor'],
            ]
        );
    }
}
