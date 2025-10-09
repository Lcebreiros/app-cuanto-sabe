<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'dni_ultimo4' => ['required', 'digits:4'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

    // Buscar usuario por nombre Y dni_ultimo4 (ambos en texto plano)
    $user = User::where('name', $this->input('name'))
                ->where('dni_ultimo4', $this->input('dni_ultimo4'))
                ->first();

    // Si no existe, lanzar error
    if (! $user) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'name' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    // Login manual con el usuario encontrado
    Auth::login($user, $this->boolean('remember'));

    // Limpiar el rate limiter después del login exitoso
    RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'name' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('name')) . '|' . $this->ip());
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'dni_ultimo4.required' => 'Los últimos 4 dígitos del DNI son obligatorios.',
            'dni_ultimo4.digits' => 'Debes ingresar exactamente 4 dígitos.',
        ];
    }
}