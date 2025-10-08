<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="w-full max-w-md mx-auto space-y-8 p-8">
        @csrf

        <!-- Campos del formulario -->
        <div class="space-y-6">
            <!-- Nombre -->
            <div class="space-y-2">
                <x-input-label for="name" :value="__('Nombre')" class="text-lg" />
                <x-text-input 
                    id="name" 
                    type="text" 
                    name="name" 
                    :value="old('name')" 
                    required 
                    autofocus 
                    autocomplete="name" 
                    class="w-full text-lg py-3 px-4"
                />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <!-- Últimos 4 del DNI -->
            <div class="space-y-2">
                <x-input-label for="dni_ultimo4" :value="__('Últimos 4 Dígitos del DNI')" class="text-lg" />
                <x-text-input 
                    id="dni_ultimo4" 
                    type="text" 
                    name="dni_ultimo4" 
                    maxlength="4"
                    pattern="[0-9]{4}"
                    inputmode="numeric"
                    :value="old('dni_ultimo4')" 
                    required 
                    class="w-full text-lg py-3 px-4"
                />
                <x-input-error :messages="$errors->get('dni_ultimo4')" />
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="space-y-6 pt-4">
            <x-primary-button class="w-full justify-center py-4 text-lg font-semibold">
                {{ __('Registrarse') }}
            </x-primary-button>

            <div class="text-center">
                <a 
                    href="{{ route('login') }}" 
                    class="text-sm underline text-[#66cce6] hover:text-[#00f0ff] transition-colors duration-300"
                >
                    {{ __('¿Ya tienes cuenta? Iniciar sesión') }}
                </a>
            </div>
        </div>
    </form>

    <style>
        /* Estilos coherentes con login */
        .space-y-8 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 2rem;
        }

        .space-y-6 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1.5rem;
        }

        input[type="text"] {
            font-size: 1rem !important;
            padding: 0.875rem 1rem !important;
            border-radius: 0.5rem !important;
        }

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
    </style>
</x-guest-layout>
