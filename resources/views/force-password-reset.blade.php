@extends('layouts.auth')

@push('styles')
    <style>
        .branded-bg {
            background-image: url('assets/media/images/2600x1600/bg-3.png');
        }

        .dark .branded-bg {
            background-image: url('assets/media/images/2600x1600/bg-3-dark.png');
        }
    </style>
@endpush

@section('content')
    <div class="grid lg:grid-cols-2 grow">
        <div class="flex order-2 justify-center items-center p-8 lg:p-10 lg:order-1">
            <div class="card max-w-[420px] w-full">
                <form action="{{ route('password.force_reset.update') }}" class="flex flex-col gap-5 p-10 card-body"
                    id="force_password_reset_form" method="POST">
                    @csrf
                    <div class="mb-2.5 text-center">
                        <h3 class="mb-2.5 text-lg font-semibold leading-none text-gray-900">
                            Ganti Password
                        </h3>
                        <div class="text-sm text-gray-600">
                            Ini pertama kali Anda login menggunakan password default. Untuk keamanan akun,
                            silakan buat password baru sebelum melanjutkan.
                        </div>
                    </div>

                    @if ($errors->any() && !$errors->has('password') && !$errors->has('password_confirmation'))
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="flex flex-col gap-1">
                        <label class="text-gray-900 form-label">
                            Password Baru
                        </label>
                        <label class="input" data-toggle-password="true">
                            <input class="@error('password') border-danger @enderror" name="password"
                                placeholder="Masukkan password baru" type="password" value="" />
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="hidden ki-outline ki-eye-slash toggle-password-active:block"></i>
                            </div>
                        </label>
                        @error('password')
                            <em class="text-sm alert text-danger">{{ $message }}</em>
                        @enderror
                        <div class="mt-1 text-xs text-gray-500">
                            Minimal 8 karakter, kombinasi huruf besar &amp; kecil, angka, dan simbol.
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-gray-900 form-label">
                            Konfirmasi Password Baru
                        </label>
                        <label class="input" data-toggle-password="true">
                            <input class="@error('password_confirmation') border-danger @enderror"
                                name="password_confirmation" placeholder="Ulangi password baru" type="password"
                                value="" />
                            <div class="btn btn-icon" data-toggle-password-trigger="true">
                                <i class="ki-outline ki-eye toggle-password-active:hidden"></i>
                                <i class="hidden ki-outline ki-eye-slash toggle-password-active:block"></i>
                            </div>
                        </label>
                        @error('password_confirmation')
                            <em class="text-sm alert text-danger">{{ $message }}</em>
                        @enderror
                    </div>

                    <button type="submit" class="flex justify-center btn btn-primary grow">
                        Simpan Password Baru
                    </button>

                    <a href="{{ route('logout') }}"
                        class="text-sm text-center text-gray-600 hover:text-primary">
                        Batal &amp; keluar
                    </a>
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
                        {{ config('app.name', 'Corsec App') }}
                    </h3>
                    <div class="text-lg font-medium text-gray-600">
                        Amankan akun Anda dengan mengganti password default sebelum melanjutkan
                        ke <span class="font-semibold text-gray-900">{{ config('app.name', 'Corsec App') }}</span>
                        Dashboard.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection