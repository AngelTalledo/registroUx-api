-- =====================================================================
-- SCRIPT DE MIGRACIÓN Y UNIFICACIÓN DE AÑO ACADÉMICO Y PERIODOS (2026)
-- FILTRADO POR DOCENTE: 'MARGARITA TAVARA GAMIO'
-- =====================================================================
-- Este script migra y unifica todos los datos registrados por error bajo 
-- el Año Académico ID: 2 y el Periodo ID: 1, moviéndolos hacia los IDs 
-- correctos en producción (Año ID: 3 y Periodo ID: 3).
--
-- Restringido únicamente para la docente: MARGARITA TAVARA GAMIO
--
-- INSTRUCCIONES:
-- 1. Haz un respaldo de tu base de datos de producción antes de ejecutar.
-- 2. Ejecuta este script en la pestaña "SQL" de phpMyAdmin.
-- =====================================================================

START TRANSACTION;

-- Obtener dinámicamente el ID de la docente 'MARGARITA TAVARA GAMIO'
SET @teacher_id = (SELECT `id` FROM `teachers` WHERE `full_name` = 'MARGARITA TAVARA GAMIO' LIMIT 1);

-- 1. Migrar Aulas al Año Académico ID 3 (Filtro por Docente)
UPDATE `classrooms` 
SET `academic_year_id` = 3 
WHERE `academic_year_id` = 2 AND `teacher_id` = @teacher_id;

-- 2. Migrar Competencias al Año Académico ID 3 (Filtro por Docente)
UPDATE `competencies` 
SET `academic_year_id` = 3 
WHERE `academic_year_id` = 2 AND `teacher_id` = @teacher_id;

-- 3. Migrar Cursos al Año Académico ID 3 (Filtro por Docente)
UPDATE `courses` 
SET `academic_year_id` = 3 
WHERE `academic_year_id` = 2 AND `teacher_id` = @teacher_id;

-- 4. Migrar Configuración de Horarios al Año Académico ID 3 (Filtro por Docente)
UPDATE `schedule_settings` 
SET `academic_year_id` = 3 
WHERE `academic_year_id` = 2 AND `teacher_id` = @teacher_id;

-- 5. Migrar Evaluaciones Diagnósticas al Año 3 y Periodo 3 (Filtro por Docente)
UPDATE `diagnostic_evaluations` 
SET `academic_year_id` = 3 
WHERE `academic_year_id` = 2 AND `teacher_id` = @teacher_id;

UPDATE `diagnostic_evaluations` 
SET `period_id` = 3 
WHERE `period_id` = 1 AND `teacher_id` = @teacher_id;

-- 6. Migrar Planificaciones de Sesión (session_competencies) al Periodo ID 3 (Filtro por Docente)
UPDATE `session_competencies` 
SET `period_id` = 3 
WHERE `period_id` = 1 AND `teacher_id` = @teacher_id;

-- 7. Migrar Sesiones de Clase (sessions) al Periodo ID 3 (Filtro por Docente)
UPDATE `sessions` 
SET `period_id` = 3 
WHERE `period_id` = 1 AND `teacher_id` = @teacher_id;

-- 8. Migrar Historiales de Asistencia al Año 3 y Periodo 3 (Filtro por Docente a través de Estudiantes)
UPDATE `historical_attendance` ha
JOIN `students` s ON ha.`student_id` = s.`id`
SET ha.`academic_year_id` = 3 
WHERE ha.`academic_year_id` = 2 AND s.`teacher_id` = @teacher_id;

UPDATE `historical_attendance` ha
JOIN `students` s ON ha.`student_id` = s.`id`
SET ha.`period_id` = 3 
WHERE ha.`period_id` = 1 AND s.`teacher_id` = @teacher_id;

-- 9. Migrar Historiales de Evaluaciones al Año 3 y Periodo 3 (Filtro por Docente a través de Estudiantes)
UPDATE `historical_evaluations` he
JOIN `students` s ON he.`student_id` = s.`id`
SET he.`academic_year_id` = 3 
WHERE he.`academic_year_id` = 2 AND s.`teacher_id` = @teacher_id;

UPDATE `historical_evaluations` he
JOIN `students` s ON he.`student_id` = s.`id`
SET he.`period_id` = 3 
WHERE he.`period_id` = 1 AND s.`teacher_id` = @teacher_id;

COMMIT;

-- =====================================================================
-- ¡MIGRACIÓN COMPLETADA CON ÉXITO PARA LA DOCENTE MARGARITA TAVARA GAMIO!
-- Todos los datos de 2026 ya están unificados bajo Año 3 y Periodo 3.
-- =====================================================================
