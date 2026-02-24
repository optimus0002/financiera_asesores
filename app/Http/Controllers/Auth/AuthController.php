<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'dni' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            Log::info('Login attempt for DNI: ' . $credentials['dni']);

            // Buscar usuario por DNI
            $user = User::where('dni', $credentials['dni'])->first();

            if (!$user) {
                Log::warning('User not found');
                return back()->withErrors([
                    'dni' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                ])->withInput($request->except('password'));
            }

            Log::info('User found. ID: ' . $user->id . ' | Role: ' . $user->role);

            // Verificar contraseña
            if (!password_verify($credentials['password'], $user->password_hash)) {
                Log::warning('Password mismatch for user ID: ' . $user->id);
                return back()->withErrors([
                    'dni' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                ])->withInput($request->except('password'));
            }

            // Autenticar usuario
            Auth::login($user);

            // Regenerar sesión (seguridad)
            $request->session()->regenerate();

            Log::info('User authenticated successfully. Auth::check(): ' . (Auth::check() ? 'YES' : 'NO'));

            // Guardar datos adicionales en sesión (opcional)
            Session::put('user_data', [
                'id' => $user->id,
                'dni' => $user->dni,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // Redirigir según rol
            if ($user->role === 'admin') {
                Log::info('Redirecting to admin.reports');
                return redirect()->route('admin.reports');
            }

            Log::info('Redirecting to asesor.dashboard');
            return redirect()->route('asesor.dashboard');

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return back()->withErrors([
                'dni' => 'Error al procesar la solicitud. Intente nuevamente.',
            ])->withInput($request->except('password'));
        }
    }

    public function logout(Request $request)
    {
        // Limpiar sesión personalizada
        Session::forget('user_data');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
