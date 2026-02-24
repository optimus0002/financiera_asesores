# Configuración de Supabase para Laravel

Este documento explica cómo configurar la aplicación Laravel para usar Supabase como base de datos y autenticación.

## 1. Configurar Variables de Entorno

Copia el archivo `.env.example` a `.env` y configura las siguientes variables:

```env
# Configuración de Supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-supabase-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-supabase-service-role-key

# Base de datos PostgreSQL de Supabase
DB_CONNECTION=pgsql
DB_HOST=your-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

## 2. Obtener Credenciales de Supabase

1. Ve a [supabase.com](https://supabase.com)
2. Crea un nuevo proyecto o selecciona uno existente
3. Ve a **Settings > API**
4. Copia:
   - **Project URL** → `SUPABASE_URL`
   - **anon public** → `SUPABASE_ANON_KEY`
   - **service_role** → `SUPABASE_SERVICE_ROLE_KEY`

5. Ve a **Settings > Database**
6. Copia la **Connection string** y extrae:
   - Host → `DB_HOST`
   - Password → `DB_PASSWORD`

## 3. Estructura de Tablas Requeridas

Asegúrate de tener estas tablas en tu base de datos Supabase:

```sql
-- Usuarios
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    dni VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'asesor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clientes
CREATE TABLE clients (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    advisor_id INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Estados de préstamos
CREATE TABLE loan_statuses (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(100) NOT NULL
);

-- Préstamos
CREATE TABLE loans (
    id SERIAL PRIMARY KEY,
    client_id INTEGER REFERENCES clients(id),
    amount DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2),
    term_months INTEGER,
    monthly_payment DECIMAL(12,2),
    status_id INTEGER REFERENCES loan_statuses(id),
    start_date DATE,
    end_date DATE,
    notes TEXT,
    codigo VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cuotas de préstamos
CREATE TABLE installments (
    id SERIAL PRIMARY KEY,
    loan_id INTEGER REFERENCES loans(id),
    due_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ahorros
CREATE TABLE savings (
    id SERIAL PRIMARY KEY,
    client_id INTEGER REFERENCES clients(id),
    amount DECIMAL(12,2) DEFAULT 0,
    daily_contribution DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    status VARCHAR(20) DEFAULT 'active',
    currency VARCHAR(10) DEFAULT 'PEN',
    codigo VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pagos
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    loan_id INTEGER REFERENCES loans(id),
    savings_id INTEGER REFERENCES savings(id),
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 4. Configurar Autenticación

La aplicación usa un sistema híbrido:
- **Supabase Auth** para validar credenciales
- **Sesión Laravel** para mantener el estado

## 5. Funcionalidades Implementadas

✅ **Autenticación con Supabase**
- Login usando DNI y contraseña
- Gestión de sesiones
- Redirección por rol

✅ **Operaciones de Base de Datos**
- Clientes asignados a asesores
- Gestión de préstamos y cuotas
- Registro de pagos
- Reportes en tiempo real

✅ **API Endpoints**
- Búsqueda de clientes
- Obtención de detalles
- Procesamiento de pagos
- Reportes diarios

## 6. Diferencias con Versión React

| Característica | React/Next.js | Laravel |
|---------------|----------------|---------|
| Base de Datos | Supabase Directo | Supabase via HTTP |
| Autenticación | Supabase Auth | Supabase Auth + Sesión Laravel |
| Estado | React State | Laravel Sessions |
| UI | Componentes React | Blade Templates |
| Routing | Next.js Router | Laravel Routes |

## 7. Pruebas

Para probar la configuración:

1. Inicia el servidor Laravel:
```bash
php artisan serve
```

2. Accede a `http://localhost:8000/login`

3. Usa credenciales de prueba (deben existir en Supabase)

## 8. Troubleshooting

### Error de conexión
- Verifica las credenciales de Supabase
- Confirma que la URL del proyecto sea correcta

### Error de autenticación
- Asegúrate que los usuarios existan en la tabla `users`
- Verifica que los passwords estén hasheados correctamente

### Datos no cargan
- Revisa las políticas RLS (Row Level Security) en Supabase
- Verifica que las tablas existan y tengan datos

## 9. Ventajas de esta Integración

- **Misma base de datos** que la versión React
- **Autenticación centralizada** con Supabase
- **Escalabilidad** de Laravel
- **Compatibilidad** con ecosistema Laravel
- **Mantenimiento simplificado** con una sola fuente de datos
