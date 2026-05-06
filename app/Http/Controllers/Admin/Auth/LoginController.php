<?php

namespace App\Http\Controllers\Admin\Auth;

use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends BackpackLoginController
{
    public function __construct()
    {
        $this->loginPath ??= backpack_url('login');
        $this->redirectTo ??= backpack_url('dashboard');
        $this->redirectAfterLogout ??= backpack_url('login');
    }

    public function showLoginForm()
    {
        if (backpack_auth()->check()) {
            return redirect($this->redirectPath());
        }

        return parent::showLoginForm();
    }

    /**
     * Backpack admins should always land inside the admin panel after login.
     */
    protected function authenticated(Request $request, $user): Response|RedirectResponse
    {
        $request->session()->forget('url.intended');

        if ($request->wantsJson()) {
            return response('', 204);
        }

        return redirect($this->redirectPath());
    }
}
