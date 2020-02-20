<?php

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

        if (!is_null($user->google2fa_secret)) {
            $google2fa = app('pragmarx.google2fa');
            $google2fa_url = $google2fa->getQRCodeInline(
                'Hatchery '.$request->getHost(),
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
     */
    public function generate2faSecret()
    {
        /** @var User $user */
        $user = Auth::guard()->user();
        // Initialise the 2FA class
        $google2fa = app('pragmarx.google2fa');

        $user->google2fa_secret = $google2fa->generateSecretKey();
        $user->save();

        return redirect()->route('2fa')->with('success', 'Geheime sleutel is gegenereerd, voer OTP in om 2FA te activeren.');
    }

    /**
     * @param Enable2FaRequest $request
     *
     * @return RedirectResponse
     */
    public function enable2fa(Enable2FaRequest $request)
    {
        /** @var User $user */
        $user = Auth::guard()->user();
        $google2fa = app('pragmarx.google2fa');
        $secret = $request->input('verify-code');
        if (!is_null($user->google2fa_secret) && $google2fa->verifyKey($user->google2fa_secret, $secret)) {
            $user->google2fa_enabled = true;
            $user->save();

            return redirect()->route('2fa')->with('success', '2FA is geactiveerd.');
        } else {
            return redirect()->route('2fa')->with('error', 'OTP code verkeerd, probeer nogmaals.');
        }
    }

    /**
     * @param Disable2FaRequest $request
     *
     * @return RedirectResponse
     */
    public function disable2fa(Disable2FaRequest $request)
    {
        if (!(Hash::check($request->get('current-password'), Auth::guard()->user()->password))) {
            // The passwords matches
            return redirect()->back()
                ->with('error', 'Je wachtwoord klopt niet, probeer nogmaals.');
        }
        /** @var User $user */
        $user = Auth::guard()->user();
        $user->google2fa_enabled = false;
        $user->save();

        return redirect()->route('2fa')->with('success', '2FA is uitgeschakeld.');
    }

    /**
     * @return RedirectResponse
     */
    public function verify()
    {
        return redirect(URL()->previous());
    }
}
