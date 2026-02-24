<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class SupabaseUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        // Buscar en sesión primero
        if (session()->has('supabase_user')) {
            $supabaseUser = session('supabase_user');
            
            if ($supabaseUser['id'] == $identifier) {
                return $this->createUserFromSupabase($supabaseUser);
            }
        }
        
        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        // Para este caso, no implementamos remember me
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        // No implementamos remember me
    }

    /**
     * Create a User instance from Supabase data
     */
    protected function createUserFromSupabase($supabaseUser)
    {
        $user = new \App\Models\User();
        $user->id = $supabaseUser['id'];
        $user->dni = $supabaseUser['dni'];
        $user->full_name = $supabaseUser['full_name'];
        $user->email = $supabaseUser['email'];
        $user->role = $supabaseUser['role'];
        $user->password = ''; // Evitar problemas con hash
        
        return $user;
    }
}
