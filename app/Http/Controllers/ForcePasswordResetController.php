<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Authentication\Http\Requests\ForcePasswordResetRequest;

class ForcePasswordResetController extends Controller
{
    /**
     * Tampilkan form wajib ganti password.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return redirect('/');
        }

        return view('authentication::force-password-reset');
    }

    /**
     * Proses ganti password wajib.
     */
    public function update(ForcePasswordResetRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return redirect('/');
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password baru tidak boleh sama dengan password default/lama.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        return redirect('/')->with('success', 'Password berhasil diubah.');
    }
}