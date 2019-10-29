<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

/**
 * Class ForgotPasswordController.
 *
 * This controller is responsible for handling password reset emails and
 * includes a trait which assists in sending these notifications from
 * your application to your users. Feel free to explore this trait.
 *
 * @package App\Http\Controllers\Auth
 */
class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
