# financiera_asesores

Sistema financiero para gestión de préstamos y ahorros con Laravel.

## Características

- Gestión de clientes y asesores
- Control de préstamos con cuotas
- Sistema de ahorros
- Cierre de caja diario
- Reportes financieros
- Autenticación de usuarios

## Instalación

1. Clonar el repositorio
```bash
git clone https://github.com/optimus0002/financiera_asesores.git
```

2. Instalar dependencias
```bash
composer install
npm install
```

3. Configurar variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar base de datos en `.env`

5. Ejecutar migraciones
```bash
php artisan migrate
```

6. Iniciar servidor
```bash
php artisan serve
npm run dev
```

## Estructura de la Base de Datos

El sistema incluye las siguientes tablas principales:

- **users** - Usuarios del sistema
- **user_asesores** - Asesores financieros
- **clients** - Clientes
- **loans** - Préstamos
- **savings** - Ahorros
- **installments** - Cuotas de préstamos
- **savings_installments** - Cuotas de ahorros
- **collections** - Cobros
- **daily_cash_closings** - Cierres de caja

## Tecnologías

- Laravel 11
- PHP 8.3+
- MySQL/MariaDB
- Bootstrap 5
- Blade Templates

## Licencia

MIT License
