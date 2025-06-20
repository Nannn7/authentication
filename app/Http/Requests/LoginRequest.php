<?php

    namespace Modules\Authentication\Http\Requests;

    use Illuminate\Auth\Events\Lockout;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Str;
    use Illuminate\Validation\ValidationException;
    use Modules\Basicdata\Models\Branch;
    use Modules\Usermanagement\Models\User;

    class LoginRequest extends FormRequest
    {
        /**
         * Returns an array of validation rules for the login form.
         *
         * @return array The validation rules.
         */
        public function rules()
        : array
        {
            return [
                'login'    => 'required',
                'password' => 'required',
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

            if ($_ENV['METHOD_AUTH'] == 'uim') {
                $this->userIdManagemeent($credentials);
            } else {
                if (!Auth::attempt($authData, $this->boolean('remember'))) {
                    RateLimiter::hit($this->throttleKey());
                    throw ValidationException::withMessages([
                        'login' => trans('auth.failed'),
                    ]);
                }

                RateLimiter::clear($this->throttleKey());
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

                session()->put($userArray[0]);

                // Use the login value to find the user
                $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';
                $user = User::where($loginField, $credentials['login'])->first();

                $someValue = $userArray[0]['KD_CABANG']; // Example value containing the code
                $lastFourDigits = substr($someValue, -4); // Gets the last 4 characters
                $branch = Branch::where('code', 'LIKE', '%' . $lastFourDigits)->first();

                session()->put('branch_id',$branch->id);

                if (!$user) {
                    //get branch id by 4 digit terakhir 0029

                    $user = User::create([
                        'name'     => $userArray[0]['NAMA_USER'],
                        'email'    => $loginField === 'email' ? $credentials['login'] : null,
                        'nik'      => $loginField === 'nik' ? $credentials['login'] : null,
                        'password' => bcrypt($credentials['password']),
                        'branch_id' => $branch ? $branch->id : null,
                    ]);

                    switch ($userArray[0]['KD_GROUP']) {
                        case '001':
                            $user->assignRole('administrator');
                            break;
                        case '025':
                            $user->assignRole('customer_service');
                            break;
                    }
                }

                Auth::loginUsingId($user->id, true);
                $this->session()->regenerate();

                RateLimiter::clear($this->throttleKey());
            }

            // Authentication failed
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        /**
         * Ensure the login request is not rate limited.
         *
         * @return void
         *
         * @throws \Illuminate\Validation\ValidationException
         */
        public function ensureIsNotRateLimited()
        : void
        {
            if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                return;
            }

            event(new Lockout($this));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        /**
         * Get the rate limiting throttle key for the request.
         *
         * @return string
         */
        public function throttleKey()
        : string
        {
            return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
        }
    }
