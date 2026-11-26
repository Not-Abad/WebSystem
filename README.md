# Sistema Web Administrativo Escolar — EBA "Lic. Antonio María de Rivera"

Plataforma web integral de **Gestión Académica y Portal Institucional** desarrollada para la **Escuela de Bachilleres "Lic. Antonio María de Rivera" (Turno Vespertino)**. El sistema centraliza el control escolar, la confección de grupos y horarios sin solapamientos, la gestión de criterios de evaluación ponderados, la emisión de boletas semestrales y la administración de contenidos públicos del plantel.

---

## Tabla de Contenidos

1. [Módulos y Roles del Sistema](#módulos-y-roles-del-sistema)
2. [Stack Tecnológico y Requisitos Previos](#stack-tecnológico-y-requisitos-previos)
3. [Instalación y Configuración Local](#instalación-y-configuración-local)
4. [Alta Inicial de Cuentas](#alta-inicial-de-cuentas)
5. [Documentación Técnica (Wiki)](#documentación-técnica-wiki)
6. [Convenciones de Desarrollo y Git](#convenciones-de-desarrollo-y-git)
7. [Equipo de Desarrollo](#equipo-de-desarrollo)

---

## Módulos y Roles del Sistema

El acceso a la plataforma está controlado mediante autenticación cifrada y autorización estricta basada en **5 roles operativos**:

| Rol | Módulos Habilitados | Funcionalidades Principales |
| :--- | :--- | :--- |
| **SuperUsuario** | Control de Personal, Bitácora de Auditoría | Activación/suspensión de cuentas de `Administrador` y `Docente`, y acceso exclusivo para consultar, filtrar y exportar la bitácora inalterable de auditoría. |
| **Administrador** | Alumnos, Tutores, Grupos, Clases, Horarios, Catálogos, CMS | Gestión de planes de estudio, materias, grupos, cargas horarias con validación de no solapamiento, expedientes de alumnos/tutores y publicación de noticias, documentos, reportes financieros y galería en el portal web. |
| **Docente** | Criterios de Evaluación, Calificaciones, Horarios | Definición de rubros de evaluación por parcial (suma exacta al `100 %`), captura de calificaciones (`0.00` a `10.00`) y consulta de horario asignado. |
| **Alumno** | Expediente Académico, Criterios, Calificaciones, Boletas | Consulta de carga horaria, criterios de evaluación por materia, calificaciones desglosadas por parcial y descarga de boletas oficiales. |
| **Tutor** | Seguimiento Académico, Boletas | Consulta del desempeño escolar, estatus y boletas de evaluación de los estudiantes bajo su tutela. |

---

## Stack Tecnológico y Requisitos Previos

* **Backend:** PHP 8.2+ y Framework **Laravel** (Eloquent ORM, Migrations, Seeders, FormRequests, Middlewares).
* **Base de Datos:** **MySQL 8.0** (`ENGINE=InnoDB`, `CHARSET=utf8mb4`, `COLLATE=utf8mb4_unicode_ci`).
* **Frontend:** Blade Templates, HTML5, CSS3, JavaScript y Vite.
* **Control de Versiones:** Git + GitHub.

Antes de instalar el proyecto en tu entorno local (XAMPP, Laragon o servidor independiente), asegúrate de contar con:
* PHP `>= 8.2` con extensiones `pdo_mysql`, `mbstring`, `openssl` y `fileinfo`.
* Composer `>= 2.x`.
* MySQL Server `8.0+`.
* Node.js `>= 18.x` y NPM.

---

## Instalación y Configuración Local

Sigue estos pasos después de clonar el repositorio para levantar el proyecto desde cero:

### 1. Clonar el repositorio e instalar dependencias
```bash
git clone https://github.com/Not-Abad/WebSystem.git
cd WebSystem

composer install
npm install
```

### 2. Configurar variables de entorno (`.env`)
Copia la plantilla `.env.example` y genera la llave de cifrado de Laravel:
```bash
cp .env.example .env
php artisan key:generate
```

Verifica que el bloque de base de datos en tu archivo `.env` apunte a **MySQL** y a la base `control_escolar`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_escolar
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 3. Crear la base de datos en MySQL
Abre **MySQL Workbench**, **phpMyAdmin** o la terminal de MySQL y crea la base de datos vacía:
```sql
CREATE DATABASE IF NOT EXISTS control_escolar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 4. Ejecutar las migraciones y cargar los catálogos base
Ejecuta el comando de migración con *seeders* para construir las tablas del modelo e insertar únicamente la estructura institucional: roles, puestos, estados de alumno, semestres, capacitaciones, áreas propedéuticas, plan de estudios, materias, grupos y salones.

**No se crean usuarios, personas, avisos, documentos ni calificaciones de demostración.** Todo eso se da de alta desde los módulos administrativos o se importa de la fuente institucional real.
```bash
php artisan migrate:fresh --seed --force
```

### 5. Compilar recursos y arrancar el servidor local
En dos terminales separadas (o usando `composer run dev` si está configurado), inicia Vite y el servidor de Laravel:
```bash
# Terminal 1: Compilación de assets frontend
npm run dev

# Terminal 2: Servidor PHP de desarrollo
php artisan serve
```
Accede desde tu navegador a `http://127.0.0.1:8000`.

---

## Alta Inicial de Cuentas

La instalación queda sin cuentas de acceso. Para crear la primera cuenta de **SuperUsuario**, ejecuta:

```bash
php artisan usuarios:crear-superadmin TU_USUARIO
```

El comando solicita la contraseña de forma interactiva y oculta, valida una longitud mínima de 12 caracteres y exige mayúscula, minúscula y número.

Con esa cuenta entra al sistema y da de alta al resto del personal desde los módulos correspondientes.

### Datos inventados para demostración (opcional)

Si necesitas **mostrar el sistema** con alumnos, docentes, tutores, clases, calificaciones, avisos y documentos de ejemplo, carga el seeder de demostración **sobre la base de datos real**:

```bash
php artisan migrate:fresh --force
php artisan db:seed --class='Database\Seeders\Demo\DemoDataSeeder' --force
```

Cuentas creadas (contraseña `Prueba123!` para todas):

| Rol | Usuario |
| :--- | :--- |
| SuperUsuario | `superadmin` |
| Administrador | `admin.escolar` |
| Docente | `docente.historia` |
| Alumno | `alumno.prueba` |
| Tutor | `tutor.prueba` |

Estos datos **no se cargan** con `php artisan migrate:fresh --seed`. Para volver a una instalación limpia y sin demos, ejecuta de nuevo `php artisan migrate:fresh --seed --force`.

### Pruebas sobre MySQL

La configuración por defecto (`phpunit.xml`) usa SQLite en memoria. Para ejecutar la misma suite sobre MySQL, en el mismo servidor que la base real, usa la base separada `control_escolar_pruebas` (así no se borran los datos de demostración):

```bash
# Crear una sola vez la base de pruebas:
# CREATE DATABASE control_escolar_pruebas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

php vendor/bin/phpunit -c phpunit.mysql.xml
```

`RefreshDatabase` migra y siembra automáticamente esa base con el seeder de demostración; no hace falta cargarla a mano.

---

## Documentación Técnica (Wiki)

La documentación completa de ingeniería de software y diseño de base de datos se encuentra publicada y versionada en la **[Wiki del Repositorio](https://github.com/Not-Abad/WebSystem/wiki)**:

* **[Home](https://github.com/Not-Abad/WebSystem/wiki)** — Visión general, matriz de roles y arquitectura MVC.
* **[BD-01. Análisis de Requerimientos](https://github.com/Not-Abad/WebSystem/wiki/BD-01-Analisis-de-Requerimientos)** — Problemática, alcance y especificación narrativa por dominio.
* **[BD-02. Reglas de Negocio](https://github.com/Not-Abad/WebSystem/wiki/BD-02-Reglas-de-Negocio)** — Restricciones curriculares, validación de no solapamiento horario (`RN-15`), ponderación al `100 %` (`RN-17`) y auditoría.
* **[BD-03. Modelo Conceptual](https://github.com/Not-Abad/WebSystem/wiki/BD-03-Modelo-Conceptual)** — Diagrama Entidad-Relación completo.
* **[BD-04. Modelo Lógico (Crow's Foot)](https://github.com/Not-Abad/WebSystem/wiki/BD-04-Modelo-Logico-Crows-Foot)** — Esquema relacional en Tercera Forma Normal (3FN) y políticas de integridad referencial.
* **[BD-05. Diccionario de Datos](https://github.com/Not-Abad/WebSystem/wiki/BD-05-Diccionario-de-Datos)** — Especificación física en MySQL 8.0 de las 25 tablas del sistema.

---

## Convenciones de Desarrollo y Git

* **Ramas:** `main` contiene el código estable integrado mediante *Pull Requests*. Cada nueva funcionalidad se desarrolla en una rama independiente (`feature/modelos-auth`, `feature/horarios`, `feature/evaluacion`, `feature/cms`, etc.).
* **Commits:** Se sigue el estándar **Conventional Commits** (`feat:`, `fix:`, `docs:`, `refactor:`, `chore:`).
* **Integridad de Datos:** Nunca se realizan eliminaciones físicas (`DELETE`) sobre usuarios, personal o alumnos; se utiliza baja lógica mediante `is_active = 0` o cambio en `id_estado_alumno`, registrando cada operación crítica en `bitacora_auditoria`.

---

## Equipo de Desarrollo

| Integrante | Usuario GitHub | Rol en el Proyecto |
| :--- | :--- | :--- |
| **Abad Romero Jesús** | [@Not-Abad](https://github.com/Not-Abad) | Arquitectura de BD, Backend Laravel y Documentación |
| **Trujillo Miranda Ian Said** | [@SopaDePixel-dev](https://github.com/SopaDePixel-dev) | Desarrollo Full-Stack y Modelado |
| **Ramírez Bautista Axel Gerardo** | [@A-G-R-B](https://github.com/A-G-R-B) | Desarrollo Full-Stack y Validaciones |