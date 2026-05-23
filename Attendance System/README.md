# Attendance System

Sistema de Control de Asistencias - MVC Framework PHP Puro

## Estructura del Proyecto

```
Attendance System/
├── application/
│   ├── config/
│   │   └── Config.php          # Configuración de la aplicación
│   ├── core/
│   │   ├── Autoload.php        # Autoload de clases
│   │   ├── Bootstrap.php       # Enrutador principal
│   │   ├── Controller.php      # Clase base de controladores
│   │   ├── Functions.php       # Helper functions
│   │   ├── Messages.php        # Mensajes estándar
│   │   ├── Model.php           # Clase base de modelos
│   │   ├── Request.php         # Manejo de peticiones
│   │   ├── Session.php         # Manejo de sesiones
│   │   └── View.php            # Clase base de vistas
│   ├── controllers/            # Controladores (prefijo C_)
│   │   ├── C_Login.php
│   │   └── C_Dashboard.php
│   ├── models/                 # Modelos (prefijo M_)
│   │   └── M_Login.php
│   └── views/                  # Vistas
│       ├── layouts/
│       │   └── layout/
│       │       ├── head.php
│       │       ├── header.php
│       │       └── footer.php
│       ├── login/
│       │   ├── index.php
│       │   └── js/
│       └── dashboard/
│           └── index.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── .htaccess                   # Configuración Apache
├── index.php                   # Punto de entrada
└── README.md
```

## Configuración

Editar `application/config/Config.php` para configurar:

- **Base de datos**: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT
- **URL base**: Se genera automáticamente
- **Controlador por defecto**: DEFAULT_CONTROLLER
- **Layout por defecto**: DEFAULT_LAYOUT

## Convenciones

- **Controladores**: Prefijo `C_`, PascalCase (ej: `C_Login.php`)
- **Modelos**: Prefijo `M_`, PascalCase (ej: `M_Login.php`)
- **Vistas**: snake_case, carpeta por controlador
- **Métodos**: snake_case (ej: `get_user()`)
- **Base de datos**: snake_case (ej: `user`, `user_campus`)

## Enrutamiento

Formato: `/controlador/metodo/argumento1/argumento2`

Ejemplos:
- `/` → Login/index
- `/Login` → Login/index
- `/Login/login` → Login/login()
- `/Dashboard` → Dashboard/index

## Sistema de Login

El sistema de login utiliza las mismas tablas que el dashboard Gliese:
- `user` - Usuarios
- `role` / `roleperson` - Roles
- `user_campus` - Relación usuario-campus
- `campus` - Campus/sedes

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Extensión PDO para MySQL

## Notas

- Este proyecto es compatible con la estructura del dashboard Gliese
- Utiliza PDO puro para conexión a base de datos
- No utiliza frameworks frontend (HTML, CSS, JS puro)
- Sistema de sesiones nativo de PHP
- Estructura MVC personalizada
