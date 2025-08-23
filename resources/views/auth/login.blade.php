<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
  
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->

    <div class="col-md-4 col-sm-6 col-xs-8 mx-auto" style="padding: 10px; border-radius: 8px; background-color: #f9f9f9; border: 1px solid #ccc;">
         
        <div class="col-md-6 col-sm-8 col-xs-8 mx-auto" >

        
            <label for="email" class="text-start"><small><b>Email</b></small></label><br>
            <input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"  style="width:100%"/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        

        <!-- Password -->
        
            <label for="password" class="text-start"><small><b>Password</b></small></label><br>

            <input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" style="width:100%"/>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        

        
        <button class="m-3">
                {{ __('Log in') }}
         </button>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            
        </div>
        </div>
        </div>
    </form>
</x-guest-layout>
