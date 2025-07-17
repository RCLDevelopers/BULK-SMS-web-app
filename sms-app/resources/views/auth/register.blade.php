<x-guest-layout>
    <div class="bg-white shadow-md rounded-lg p-8 max-w-md w-full mx-auto">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
            <p class="text-gray-600 mt-2">Join our platform to get started</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Full Name')" class="text-gray-700 font-medium" />
                <div class="mt-1">
                    <x-text-input 
                        id="name" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        type="text" 
                        name="name" 
                        :value="old('name')" 
                        required 
                        autofocus 
                        autocomplete="name" 
                        placeholder="John Doe"
                    />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-red-600" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" class="text-gray-700 font-medium" />
                <div class="mt-1">
                    <x-text-input 
                        id="email" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autocomplete="email"
                        placeholder="you@example.com"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
                <div class="mt-1">
                    <x-text-input 
                        id="password" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        type="password"
                        name="password"
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters, with at least one uppercase, one lowercase, one number, and one special character.</p>
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-medium" />
                <div class="mt-1">
                    <x-text-input 
                        id="password_confirmation" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        type="password"
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-red-600" />
            </div>

            <!-- Terms and Conditions -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input 
                        id="terms" 
                        name="terms" 
                        type="checkbox" 
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        required
                    >
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="font-medium text-gray-700">I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a></label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <x-primary-button class="w-full justify-center py-3 text-base font-medium">
                    {{ __('Create Account') }}
                </x-primary-button>
            </div>

            <div class="text-center text-sm text-gray-600 mt-4">
                <p>Already have an account? 
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        {{ __('Sign in') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
