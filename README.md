# RegistroUx API - Sistema de Gestión Académica

Esta es una API REST profesional construida con **Slim Framework 4**, diseñada para gestionar registros académicos, asistencias, evaluaciones y estructuras institucionales.

## 🚀 Tecnologías Utilizadas

- **Lenguaje:** PHP 8.1+
- **Framework:** [Slim Framework 4](https://www.slimframework.com/)
- **ORM:** [Illuminate Database (Eloquent)](https://laravel.com/docs/eloquent)
- **Migraciones:** [Phinx](https://phinx.org/)
- **Autenticación:** JWT (JSON Web Tokens)
- **Inyección de Dependencias:** PHP-DI
- **Validación:** Respect Validation
- **Reportes:** Dompdf (PDF) y PhpSpreadsheet (Excel)
- **Logging:** Monolog

## 📋 Características Principales

- **Gestión de Usuarios y Autenticación:** Inicio de sesión seguro, gestión de sesiones y recuperación de contraseña (vía OTP).
- **Estructura Académica:** Configuración de años académicos, periodos (trimestres/bimestres), grados, cursos y aulas.
- **Control de Asistencia:** Registro diario de asistencias y generación de reportes detallados.
- **Sistema de Evaluaciones:** Gestión de competencias, sesiones de clase, criterios de evaluación y evidencias.
- **Cierre Histórico:** Funcionalidad para "congelar" datos al finalizar periodos académicos.
- **Personalización Institucional:** Gestión de logos y plantillas de encabezado para reportes personalizados.
- **Dashboard:** Resumen de horarios y actividades actuales para los docentes.

## 🛠️ Instalación y Configuración

### Requisitos Previos

- PHP 8.1 o superior.
- Composer.
- MySQL/MariaDB.
- Servidor Web (Apache con `mod_rewrite` habilitado o Nginx).

### Pasos de Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/AngelTalledo/registroUx-api.git
   cd registroUx-api
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   ```

3. **Configurar el entorno:**
   Crea un archivo `.env` en la raíz del proyecto (puedes basarte en las credenciales de tu base de datos) con las siguientes variables:
   ```env
   DB_HOST=localhost
   DB_NAME=nombre_bd
   DB_USER=usuario
   DB_PASS=contraseña
   JWT_SECRET=tu_secreto_super_seguro
   ```

4. **Ejecutar migraciones:**
   ```bash
   vendor/bin/phinx migrate
   ```

5. **Servir la aplicación:**
   Puedes usar el servidor embebido de PHP para desarrollo:
   ```bash
   php -S localhost:8080 -t public
   ```

## 📂 Estructura de Endpoints (Resumen)

Todos los endpoints están agrupados bajo el prefijo `/api` y la mayoría requiere un token JWT válido.

### Autenticación y Seguridad
- `POST /api/auth/login` - Iniciar sesión.
- `POST /api/password-reset/request` - Solicitar recuperación de contraseña.
- `POST /api/password-reset/verify` - Verificar código OTP.

### Docentes y Estudiantes
- `GET /api/teachers` - Listar docentes.
- `GET /api/students` - Listar estudiantes.
- `GET /api/students/my-courses` - Cursos asignados al estudiante actual.

### Academia
- `GET/POST /api/academic-years` - Gestión de años escolares.
- `GET/POST /api/periods` - Trimestres o periodos de evaluación.
- `GET/POST /api/competencies` - Competencias curriculares.

### Asistencias y Evaluaciones
- `POST /api/attendances` - Registrar asistencia.
- `POST /api/evaluations/upsert` - Registrar o actualizar calificaciones.
- `POST /api/reports/attendance` - Generar reporte de asistencia (PDF/Excel).
- `POST /api/reports/evaluation` - Generar reporte de notas (PDF/Excel).

### Configuración Institucional
- `GET/PUT /api/institutions/{id}` - Datos de la institución.
- `POST /api/institutions/{id}/logos` - Subir logos institucionales.

## 📄 Licencia

Este proyecto es propiedad de **Angel Talledo**. Todos los derechos reservados.