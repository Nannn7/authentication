@extends('layouts.auth')

@push('styles')
    <style>
        .branded-bg {
            background-image: url('assets/media/images/2600x1600/1.png');
        }

        .dark .branded-bg {
            background-image: url('assets/media/images/2600x1600/1-dark.png');
        }
    </style>
@endpush

@section('content')
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex order-2 justify-center items-center p-8 lg:p-10 lg:order-1">
            <div class="card max-w-[370px] w-full">
                <form action="{{ route('login') }}" class="flex flex-col gap-5 p-10 card-body" id="sign_in_form" method="POST">
                    @csrf
                    <div class="mb-2.5 text-center">
                        <h3 class="mb-2.5 text-lg font-semibold leading-none text-gray-900">
                            Sign in
                        </h3>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-gray-900 form-label">
                            Email or NIK
                        </label>
                        <input class="w-full input @error('login') border-danger @enderror"
                            placeholder="Enter your email or NIK" type="text" name="login" value="{{ old('login') }}">
                        @error('login')
                            <em class="text-sm alert text-danger">{{ $message }}</em>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1 justify-between items-center">
                            <label class="text-gray-900 form-label">
                                Password
                            </label>
                        </div>
                        <label class="input" data-toggle-password="true">
                            <input class="@error('password') border-danger @enderror" name="password"
                                placeholder="Enter Password" type="password" value="" />
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="hidden ki-outline ki-eye-slash toggle-password-active:block"></i>
                            </div>
                        </label>
                        @error('password')
                            <em class="text-sm alert text-danger">{{ $message }}</em>
                        @enderror
                    </div>
                    <label class="checkbox-group">
                        <input class="checkbox checkbox-sm" name="check" type="checkbox" value="1" />
                        <span class="checkbox-label">
                            Remember me
                        </span>
                    </label>
                    <button type="submit" class="flex justify-center btn btn-primary grow">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
        <div
            class="order-1 bg-top bg-no-repeat lg:rounded-xl lg:border lg:border-gray-200 lg:m-5 lg:order-2 xxl:bg-center xl:bg-cover branded-bg">
            <div class="flex flex-col gap-4 p-8 w-full lg:p-16">
                <div class="flex w-full">
                    <img class="h-[100px] lg:h-[200px] max-w-none" src="assets/media/app/logo-agi.png" />
                </div>
                <div class="flex flex-col gap-3">
                    <h3 class="text-4xl font-semibold text-gray-900">
                        {{ env('APP_NAME', 'Dashboard') }}
                    </h3>
                    <div class="text-lg font-medium text-gray-600">
                        A robust authentication
                        @if (env('METHOD_AUTH') == 'uim')
                            integrate with <span class="font-semibold text-gray-900">User ID
                                Management</span>
                        @endif
                        gateway ensuring
                        <br />
                        secure efficient user access to the
                        <span class="font-semibold text-gray-900">
                            {{ env('APP_NAME', 'Dashboard') }}
                        </span>
                        <br />
                        Dashboard interface.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
