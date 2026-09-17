<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Basicdata\Models\Branch;
use Modules\Usermanagement\Models\User;

class LoginRequest extends FormRequest
{
    private const IP_MAX_ATTEMPTS = 20;
    private const IP_DECAY_SECONDS = 60;
    /**
     * Returns an array of validation rules for the login form.
     *
     * @return array The validation rules.
     */
    public function rules(): array
    {
        return [
            'login'    => 'required',
            'password' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'login.required'    => 'User tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $credentials = $this->only('login', 'password');
        $loginField  = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';
        $authData = [
            $loginField => $credentials['login'],
            'password'  => $credentials['password'],
        ];

        if (env('METHOD_AUTH') == 'uim') {
            $this->userIdManagemeent($credentials);
        } else {
            if (!Auth::attempt($authData, $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());
                RateLimiter::hit($this->ipthrottleKey(), self::IP_DECAY_SECONDS);

                if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                    $this->throwLockoutValidationException();
                }

                if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::IP_MAX_ATTEMPTS)) {
                    $this->throwLockoutValidationException();
                }

                $this->throwInvalidCredentialsException();
            }

            $user = Auth::user();
            if (!$user || !$user->roles()->exists()) {
                Auth::guard('web')->logout();
                $this->session()->invalidate();
                $this->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'login' => 'Akun belum memiliki role. Silakan hubungi administrator.',
                ]);
            }

            RateLimiter::clear($this->throttleKey());
            RateLimiter::clear($this->ipThrottleKey());
        }
    }

    /**
     * Authenticate user through user manager
     *
     * @param array $credentials
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function userIdManagemeent($credentials)
    {
        $userArray       = [];
        $id              = $credentials['login'];
        $passwd          = $credentials['password'];
        $SERVER_ADDR     = request()->ip();
        $IPUserManager   = $_ENV['IP_USER_MANAGER'];
        $portUserManager = $_ENV['PORT_USER_MANAGER'];
        $appId           = $_ENV['APP_ID'];

        $userData = verify_user($id, $passwd, $SERVER_ADDR, $IPUserManager, $portUserManager, $appId);

        if (strlen($userData) > 1) {
            $userRawArray = explode("\t", $userData);

            foreach ($userRawArray as $rval) {
                [$key, $val] = explode('=', $rval);
                $userArray[0][$key] = $val;
            }
            // Use the login value to find the user
            $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';

            $kodeCabang = $userArray[0]['KD_CABANG']; // Example value containing the code
            $lastFourDigits = substr($kodeCabang, -4); // Gets the last 4 characters
            $branch = Branch::where('code', 'LIKE', '%' . $lastFourDigits)->first();

            session()->put($userArray[0]);
            if ($branch) {
                session()->put('branch_id', $branch->id);
            }

            $user = User::updateOrCreate(
                [$loginField => $credentials['login']],
                [
                    'name'     => $userArray[0]['NAMA_USER'],
                    'email'    => $loginField === 'email' ? $credentials['login'] : null,
                    'nik'      => $loginField === 'nik' ? $credentials['login'] : null,
                    'password' => bcrypt($credentials['password']),
                    'branch_id' => $branch ? $branch->id : null,
                ]
            );

            // Assign role based on user group code
            $role = match ($userArray[0]['KD_GROUP']) {
                '001' => 'administrator',
                default => 'customer_service'
            };

            $user->syncRoles($role);

            Auth::loginUsingId($user->id, true);
            $this->session()->regenerate();

            RateLimiter::clear($this->throttleKey());
        }

        // Authentication failed
        RateLimiter::hit($this->throttleKey());
        RateLimiter::hit($this->ipThrottleKey(), self::IP_DECAY_SECONDS);

        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $this->throwLockoutValidationException();
        }

        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::IP_MAX_ATTEMPTS)) {
            $this->throwLockoutValidationException();
        }

        $this->throwInvalidCredentialsException();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            event(new Lockout($this));
            $this->throwLockoutValidationException($this->throttleKey());
        }

        if (RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::IP_MAX_ATTEMPTS)) {
            event(new Lockout($this));

            $this->throwLockoutValidationException($this->ipThrottleKey());
        }
    }

    /**
     * Throw a visible validation error when login attempts are rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwLockoutValidationException(?string $key = null): void
    {
        $seconds = RateLimiter::availableIn($key ?? $this->throttleKey());
        $retryAfter = $seconds < 60
            ? $seconds . ' detik'
            : ceil($seconds / 60) . ' menit';

        throw ValidationException::withMessages([
            'login' => "Akun diblokir sementara karena terlalu banyak percobaan login. Silakan coba lagi dalam {$retryAfter}.",
        ]);
    }

    protected function throwInvalidCredentialsException(): void
    {
        throw ValidationException::withMessages([
            'login' => 'Email/NIK atau Password tidak sesuai.',
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login', '')) . '|' . $this->ip());
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function ipThrottleKey(): string
    {
        return 'login-ip:' . $this->ip();
    }
}
