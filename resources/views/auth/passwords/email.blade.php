@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 font-semibold text-lg">{{ __('Reset Password') }}</div>

                <div class="p-6">
                    @if (session('status'))
                        <div class="p-4 rounded-md bg-green-50 text-green-800 border border-green-200 mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <label for="email" class="md:w-1/3 md:text-right md:pr-4 mb-2 md:mb-0">{{ __('E-Mail Address') }}</label>

                                <div class="md:w-2/3">
                                    <input id="email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent{{ $errors->has('email') ? ' border-red-500' : '' }}" name="email" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="text-red-600 text-sm mt-1 block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <div class="flex justify-end md:justify-start md:ml-1/3">
                                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
