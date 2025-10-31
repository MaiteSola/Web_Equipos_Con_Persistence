# README - Equipos_Persistence

**Equipos_Persistence** es una aplicación web para la gestión de equipos (deportivos, hardware, etc.) con persistencia de datos en **MySQL**. Está diseñada para ejecutarse en un entorno local utilizando **XAMPP**.

---

## Requisitos del sistema

Para desarrollar y ejecutar el proyecto, necesitas:

| Herramienta | Versión recomendada | Enlace |
|-------------|---------------------|--------|
| **XAMPP**   | 8.2.x o superior    | [apachefriends.org](https://www.apachefriends.org) |
| **MySQL**   | Incluido en XAMPP   | — |
| **PHP**     | 8.1+ (incluido en XAMPP) | — |
| **Bootstrap** | 5.3.x (vía CDN) | [getbootstrap.com](https://getbootstrap.com) |
| **Editor de código** | VS Code, PHPStorm, etc. | Recomendado: [code.visualstudio.com](https://code.visualstudio.com/) |

> **Importante**: Asegúrate de que **Apache** y **MySQL** estén activos en el panel de control de XAMPP.

---

## Instalación de la base de datos

Sigue estos pasos para configurar la base de datos:

1. Inicia XAMPP y verifica que **Apache** y **MySQL** estén en ejecución.
2. Abre tu navegador y accede a: <http://localhost/phpmyadmin>
3. En el panel izquierdo, haz clic en **"Nueva"**.
4. Nombra la base de datos: `equipos_persistence`
5. Selecciona el cotejamiento: `utf8mb4_general_ci`
6. Haz clic en **Crear**.

### Importar estructura y datos iniciales

1. Selecciona la base de datos `equipos_persistence` en phpMyAdmin.
2. Ve a la pestaña **"Importar"**.
3. Elige el archivo SQL ubicado en: persistence/scripts/equipos_competicion_ms.sql
4. Haz clic en **Continuar** para ejecutar la importación.

> El script incluye:
> - Tabla `equipos` (`id`, `nombre`, `ciudad`, `año_fundacion`, etc.)
> - Datos de ejemplo (opcional)

---

## Configuración de Bootstrap

El proyecto utiliza **Bootstrap 5** mediante **CDN** (sin instalación local requerida).

### Enlaces CDN (ya incluidos en las vistas):

```html
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

<!-- Bootstrap JS (incluye Poppper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


### Estructura del Proyecto
Equipos_Persistence/
├── index.php                     # Página principal
├── .hintrc                       # Configuración de linter
├── app/
│   ├── equipos.php               # Listado y gestión de equipos
│   ├── partidos_equipo.php       # Partidos por equipo
│   └── partidos.php              # Gestión general de partidos
├── assets/
│   ├── css/
│   │   └── style.css             # Estilos personalizados
│   └── js/                       # Scripts JavaScript
├── persistence/
│   ├── conf/
│   │   ├── credentials.json      # Credenciales de la BD
│   │   └── PersistentManager.php # Gestor de conexión
│   ├── DAO/
│   │   ├── EquiposDAO.php        # Consultas sobre equipos
│   │   ├── PartidosDAO.php       # Consultas sobre partidos
│   │   └── GenericDAO.php        # Clase base de acceso a datos
│   └── scripts/
│       └── equipos_competicion_ms.sql # Script SQL inicial
├── templates/
│   ├── header.php                # Cabecera común
│   ├── footer.php                # Pie de página
│   └── menu.php                  # Menú de navegación
└── utils/
    └── SessionHelper.php         # Gestión de sesiones

```

## Configuración de la conexión a la base de datos

1. Abre el archivo: persistence/conf/credentials.json
2. Modifica los siguientes campos con tus datos:
   {
  "host": "localhost",
  "user": "root",
  "password": "",
  "database": "equipos_persistence"
}

## Cómo ejecutar el proyecto

1. Copia la carpeta completa Equipos_Persistence dentro de la carpeta htdocs de XAMPP: C:\xampp\htdocs\Equipos_Persistence
2. Inicia Apache y MySQL desde el panel de control de XAMPP.
3. Abre tu navegador y accede a: http://localhost/Equipos_Persistence
