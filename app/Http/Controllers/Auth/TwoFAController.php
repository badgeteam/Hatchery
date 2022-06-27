<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Disable2FaRequest;
use App\Http\Requests\Enable2FaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

/**
 * Class TwoFAController.
 */
class TwoFAController extends Controller
{
    /**
     * @return View
     */
    public function show2faForm(Request $request)
    {
        /** @var User $user */
        $user = Auth::guard()->user();

        $google2fa_url = '';

        if ($user->google2fa_secret !== null) {
            $google2fa = app('pragmarx.google2fa');
            $google2fa_url = $google2fa->getQRCodeInline(
                'Hatchery ' . $request->getHost(),
                $user->email,
                $user->google2fa_secret
            );
        }
        $data = [
            'user'          => $user,
            'google2fa_url' => $google2fa_url,
        ];

        return view('auth.2fa')->with('data', $data);
    }

    /**
     * @return RedirectResponse
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws IncompatibleWithGoogleAuthenticatorException
     */
    public function generate2faSecret()
    {
        /** @var User $user */
        $user = Auth::guard()->user();

        if ( $user->google2fa_secret !== null) {
            return redirect()->route('2fa')->with('error', 'User already has OTP secret.');
        }

        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');

        $user->google2fa_secret = $google2fa->generateSecretKey();
        $user->save();

        return redirect()->route('2fa')->with('success', 'Secret key has been created, enter OTP to activate 2FA.');
    }

    /**
     * @param Enable2FaRequest $request
     *
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     *
     * @return RedirectResponse
     */
    public function enable2fa(Enable2FaRequest $request)
    {
        /** @var User $user */
        $user = Auth::guard()->user();
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $code = $request->input('verify-code', '');
        if ($user->google2fa_secret !== null && $google2fa->verifyKey($user->google2fa_secret, $code)) {
            $user->google2fa_enabled = true;
            $user->save();

            return redirect()->route('2fa')->with('success', '2FA has been activated.');
        }

        return redirect()->route('2fa')->with('error', 'OTP code wrong, please try again.');
    }

    /**
     * @param Disable2FaRequest $request
     *
     * @return RedirectResponse
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function disable2fa(Disable2FaRequest $request)
    {
        /** @var User $user */
        $user = Auth::guard()->user();
        if (!(Hash::check($request->get('current-password'), $user->password))) {
            return redirect()->back()
                ->with('error', 'Your password is invalid, try again.');
        }
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $code = $request->input('verify-code', '');
        if ($user->google2fa_secret !== null && !$google2fa->verifyKey($user->google2fa_secret, $code)) {
            return redirect()->back()
                ->with('error', 'Your 2FA code is invalid, try again.');
        }
        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        return redirect()->route('2fa')->with('success', '2FA has been disabled.');
    }

    /**
     * @return RedirectResponse
     */
    public function verify()
    {
        return redirect(URL()->previous());
    }
}
