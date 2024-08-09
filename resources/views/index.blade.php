@extends('layouts.auth')

@push('styles')
    <style>
        .branded-bg {
            background-image:url('assets/media/images/2600x1600/1.png');
        }
        .dark .branded-bg {
            background-image: url('assets/media/images/2600x1600/1-dark.png');
        }
    </style>
@endpush

@section('content')
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex justify-center items-center p-8 lg:p-10 order-2 lg:order-1">
            <div class="card max-w-[370px] w-full">
                <form action="{{ route('login') }}" class="card-body flex flex-col gap-5 p-10" id="sign_in_form" method="POST">
                    @csrf
                    <div class="text-center mb-2.5">
                        <h3 class="text-lg font-semibold text-gray-900 leading-none mb-2.5">
                            Sign in
                        </h3>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="form-label text-gray-900">
                            Email
                        </label>
                        <input class="w-full input @error('email') border-danger @enderror" placeholder="email@email.com" type="email" name="email" value="">
                        @error('email')
                        <em class="alert text-danger text-sm">{{ $message }}</em>
                        @enderror
                    </div>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center justify-between gap-1">
                            <label class="form-label text-gray-900">
                                Password
                            </label>
                        </div>
                        <label class="input" data-toggle-password="true">
                            <input class="@error('password') border-danger @enderror" name="password" placeholder="Enter Password" type="password" value=""/>
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="ki-outline ki-eye-slash hidden toggle-password-active:block"></i>
                            </div>
                            @error('password')
                            <em class="alert text-danger text-sm">{{ $message }}</em>
                            @enderror
                        </label>
                    </div>
                    <label class="checkbox-group">
                        <input class="checkbox checkbox-sm" name="check" type="checkbox" value="1"/>
                        <span class="checkbox-label">
                            Remember me
                        </span>
                    </label>
                    <button type="submit" class="btn btn-primary flex justify-center grow">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
        <div class="lg:rounded-xl lg:border lg:border-gray-200 lg:m-5 order-1 lg:order-2 bg-top xxl:bg-center xl:bg-cover bg-no-repeat branded-bg">
            <div class="flex flex-col p-8 lg:p-16 gap-4">
                <a href="{{ route('dashboard') }}">
                    <img class="h-[100px] max-w-none" src="assets/media/app/logo-agi.png"/>
                </a>
                <div class="flex flex-col gap-3">
                    <h3 class="text-2xl font-semibold text-gray-900">
                        Secure Access Portal
                    </h3>
                    <div class="text-base font-medium text-gray-600">
                        A robust authentication gateway ensuring
                        <br/>
                        secure
                        <span class="text-gray-900 font-semibold">
                            efficient user access
                        </span>
                        to the LPJ Online
                        <br/>
                        Dashboard interface.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
