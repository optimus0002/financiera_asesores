<?php

return [
    'url' => env('SUPABASE_URL'),
    'anon_key' => env('SUPABASE_ANON_KEY'),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY'),
    'database_url' => env('SUPABASE_URL') . '/rest/v1',
    'auth_url' => env('SUPABASE_URL') . '/auth/v1',
    'storage_url' => env('SUPABASE_URL') . '/storage/v1',
];
