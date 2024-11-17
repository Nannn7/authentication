<?php

    namespace Modules\Authentication\Http\Requests;

    use Illuminate\Auth\Events\Lockout;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Str;
    use Illuminate\Validation\ValidationException;

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
        public function authenticate()
        : void
        {
            $this->ensureIsNotRateLimited();

            $credentials = $this->only('login', 'password');
            $loginField  = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'nik';

            $authData = [
                $loginField => $credentials['login'],
                'password'  => $credentials['password'],
            ];

            if (!Auth::attempt($authData, $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'login' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
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
