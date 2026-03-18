<x-guest-layout>
    <div class="mb-4 text-center">
        <h3 class="fw-bold h4 mb-1">Welcome Back</h3>
        <p class="text-muted small">Please enter your details to sign in</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
  
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Email Address</label>
            <input id="email" 
                   class="form-control bg-light border-0 py-2 px-3" 
                   style="border-radius: 12px; font-size: 0.95rem;"
                   type="email" 
                   name="email" 
                   :value="old('email')" 
                   required 
                   autofocus 
                   autocomplete="username" 
                   placeholder="admin@eyami.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 small text-danger" />
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label small fw-bold text-muted text-uppercase letter-spacing-1 mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-primary small text-decoration-none fw-semibold" href="{{ route('password.request') }}">
                        Forgot?
                    </a>
                @endif
            </div>
            <input id="password" 
                   class="form-control bg-light border-0 py-2 px-3" 
                   style="border-radius: 12px; font-size: 0.95rem;"
                   type="password"
                   name="password"
                   required 
                   placeholder="••••••••"
                   autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 small text-danger" />
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check small">
                <input id="remember_me" type="checkbox" class="form-check-input border-secondary" name="remember">
                <label for="remember_me" class="form-check-label text-muted">Keep me signed in</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3 mb-3" style="border-radius: 12px; font-size: 1rem;">
            Sign In to Dashboard
        </button>

        <div class="text-center">
            <p class="text-muted small mb-0">Don't have an account? <a href="#" class="text-primary fw-semibold text-decoration-none">Contact Admin</a></p>
        </div>
    </form>
</x-guest-layout>
