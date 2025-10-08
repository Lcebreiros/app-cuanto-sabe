<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-md mx-auto space-y-8 p-8">
        @csrf
        
        <!-- Campos del formulario -->
        <div class="space-y-6">
            <!-- Email -->
            <div class="space-y-2">
                <x-input-label for="name" :value="__('Nombre')" class="text-lg" />
                <x-text-input 
                    id="name" 
                    type="name" 
                    name="name" 
                    :value="old('name')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    class="w-full text-lg py-3 px-4" 
                />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <x-input-label for="dni_ultimo4" :value="__('Últimos 4 dígitos del DNI')" class="text-lg" />
                <x-text-input 
                    id="dni_ultimo4" 
                    type="text" 
                    name="dni_ultimo4" 
                    required 
                    maxlength="4"
                    inputmode="numeric"
                    autocomplete="current-password" 
                    class="w-full text-lg py-3 px-4" 
                />
                <x-input-error :messages="$errors->get('dni_ultimo4')" />
            </div>

            <!-- Remember me -->
            <div class="flex items-center space-x-3">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-4 h-4"
                >
                <label for="remember_me" class="text-gray-300">
                    {{ __('Recordar sesión') }}
                </label>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="space-y-6">
            <!-- Botón de login principal -->
            <x-primary-button class="w-full justify-center py-4 text-lg font-semibold">
                {{ __('Iniciar sesión') }}
            </x-primary-button>

            <!-- Forgot password -->
            @if (Route::has('password.request'))
                <div class="text-center">
                    <a 
                        class="text-sm underline text-[#66cce6] hover:text-[#00f0ff] transition-colors duration-300" 
                        href="{{ route('password.request') }}"
                    >
                        {{ __('Olvidaste tu contraseña?') }}
                    </a>
                </div>
            @endif

            <!-- Separador -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-900 text-gray-400">o</span>
                </div>
            </div>

            <!-- Crear cuenta -->
            <div class="text-center space-y-3">
                <p class="text-gray-300 text-sm">
                    ¿Aún no tienes cuenta?
                </p>
                <a 
                    href="{{ route('register') }}" 
                    class="block w-full text-center py-3 px-4 border-2 border-[#00f0ff] text-[#00f0ff] rounded-lg hover:bg-[#00f0ff] hover:text-black transition-all duration-300 font-semibold"
                >
                    {{ __('Registrarse') }}
                </a>
            </div>
        </div>
    </form>

    <style>
        /* Estilos adicionales para el formulario */
        .space-y-8 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 2rem;
        }
        
        .space-y-6 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1.5rem;
        }

        .space-y-3 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.75rem;
        }

        .space-x-3 > :not([hidden]) ~ :not([hidden]) {
            margin-left: 0.75rem;
        }

        /* Mejorar el contenedor del guest-layout */
        x-guest-layout .content-container,
        .guest-layout-container {
            max-width: 500px !important;
            padding: 3rem 2.5rem !important;
        }

        /* Estilos para inputs más grandes */
        input[type="email"], 
        input[type="password"] {
            font-size: 1rem !important;
            padding: 0.875rem 1rem !important;
            border-radius: 0.5rem !important;
        }

        /* Botón principal más prominente */
        .primary-button {
            background: linear-gradient(135deg, #00f0ff, #0099cc) !important;
            font-size: 1.1rem !important;
            padding: 1rem 2rem !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 15px rgba(0, 240, 255, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .primary-button:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(0, 240, 255, 0.4) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .space-y-8 {
                padding: 1.5rem !important;
            }
            
            input[type="email"], 
            input[type="password"] {
                font-size: 0.9rem !important;
                padding: 0.75rem !important;
            }
        }
    </style>
</x-guest-layout>