<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SupabaseService
{
    private $baseUrl;
    private $apiKey;
    private $serviceRoleKey;

    public function __construct()
    {
        $this->baseUrl = env('SUPABASE_URL');
        $this->apiKey = env('SUPABASE_ANON_KEY');
        $this->serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    /**
     * Autenticar usuario con Supabase Auth (similar a React)
     */
    public function signIn($email, $password)
    {
        try {
            // Usar el mismo endpoint que React
            $response = Http::post($this->baseUrl . '/auth/v1/token?grant_type=password', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'error' => $response->json()['error_description'] ?? 'Error de autenticación'
                ];
            }

            $data = $response->json();
            
            // Obtener información del usuario (como hace React)
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $data['access_token']
            ])->get($this->baseUrl . '/auth/v1/user');

            if ($userResponse->failed()) {
                return [
                    'success' => false,
                    'error' => 'Error al obtener información del usuario'
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'user' => $userResponse->json()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error en autenticación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar token JWT
     */
    public function verifyToken($token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get($this->baseUrl . '/auth/v1/user');

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Realizar consulta a Supabase Database
     */
    public function from($table)
    {
        return new SupabaseQueryBuilder($this->baseUrl, $this->serviceRoleKey, $table);
    }

    /**
     * Insertar datos en tabla
     */
    public function insert($table, $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->serviceRoleKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=minimal'
        ])->post($this->baseUrl . '/rest/v1/' . $table, $data);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Actualizar datos en tabla
     */
    public function update($table, $data, $filters = [])
    {
        $url = $this->baseUrl . '/rest/v1/' . $table;
        
        if (!empty($filters)) {
            $url .= '?' . http_build_query($filters);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->serviceRoleKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=minimal'
        ])->patch($url, $data);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Eliminar datos de tabla
     */
    public function delete($table, $filters = [])
    {
        $url = $this->baseUrl . '/rest/v1/' . $table;
        
        if (!empty($filters)) {
            $url .= '?' . http_build_query($filters);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->serviceRoleKey,
            'Prefer' => 'return=minimal'
        ])->delete($url);

        return $response->successful();
    }
}

class SupabaseQueryBuilder
{
    private $baseUrl;
    private $apiKey;
    private $table;
    private $select = '*';
    private $filters = [];
    private $orderBy = [];
    private $limit = null;

    public function __construct($baseUrl, $apiKey, $table)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->table = $table;
    }

    public function select($columns)
    {
        $this->select = $columns;
        return $this;
    }

    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = 'eq';
        }

        $this->filters[] = "{$column}={$operator}.{$value}";
        return $this;
    }

    public function like($column, $value)
    {
        $this->filters[] = "{$column}=like.*{$value}*";
        return $this;
    }

    public function whereIn($column, $values)
    {
        $valuesStr = '(' . implode(',', $values) . ')';
        $this->filters[] = "{$column}=in.{$valuesStr}";
        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBy[] = "{$column}.{$direction}";
        return $this;
    }

    public function limit($count)
    {
        $this->limit = $count;
        return $this;
    }

    public function get()
    {
        $url = $this->buildUrl();
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'apikey' => $this->apiKey
        ])->get($url);

        return $response->successful() ? $response->json() : null;
    }

    public function first()
    {
        $this->limit = 1;
        $results = $this->get();
        return $results && !empty($results) ? $results[0] : null;
    }

    private function buildUrl()
    {
        $url = $this->baseUrl . '/rest/v1/' . $this->table;
        $params = [];

        if ($this->select !== '*') {
            $params['select'] = $this->select;
        }

        if (!empty($this->filters)) {
            $params[] = implode('&', $this->filters);
        }

        if (!empty($this->orderBy)) {
            $params['order'] = implode(',', $this->orderBy);
        }

        if ($this->limit) {
            $params['limit'] = $this->limit;
        }

        if (!empty($params)) {
            $url .= '?' . (is_array($params[0]) ? http_build_query($params) : implode('&', $params));
        }

        return $url;
    }
}
