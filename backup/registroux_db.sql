-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-04-2026 a las 21:37:02
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `registroux_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `academic_years`
--

INSERT INTO `academic_years` (`id`, `teacher_id`, `year`, `name`, `status`, `is_current`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2025, '2025', 1, 0, '2026-03-15 05:26:53', '2026-04-03 11:22:12', NULL),
(2, 1, 2026, '2026', 1, 1, '2026-03-15 05:27:11', '2026-04-03 11:22:12', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `session_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `status` enum('PRESENTE','FALTA','TARDANZA') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `grade_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `classrooms`
--

INSERT INTO `classrooms` (`id`, `teacher_id`, `academic_year_id`, `course_id`, `grade_id`, `section`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 1, 1, 'A', 1, '2026-03-17 10:42:52', '2026-03-17 10:47:01', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `competencies`
--

CREATE TABLE `competencies` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `competencies`
--

INSERT INTO `competencies` (`id`, `teacher_id`, `academic_year_id`, `course_id`, `name`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 1, 'C1', 'Construye su identidad como persona amada por Dios digna, libre y trascendente.', 1, '2026-04-02 11:27:16', '2026-04-02 11:27:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `academic_year_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `courses`
--

INSERT INTO `courses` (`id`, `teacher_id`, `academic_year_id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 'RELIGION A', 1, '2026-03-17 07:55:36', '2026-03-17 07:56:02', NULL),
(2, 1, 2, 'CTA n', 1, '2026-03-17 07:55:53', '2026-03-17 08:19:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_groups`
--

CREATE TABLE `course_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diagnostic_evaluations`
--

CREATE TABLE `diagnostic_evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED NOT NULL,
  `academic_year_id` int(11) UNSIGNED NOT NULL,
  `period_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `competency_id` int(11) UNSIGNED NOT NULL,
  `course_id` int(11) UNSIGNED NOT NULL,
  `aula_id` int(11) UNSIGNED NOT NULL,
  `grade` varchar(2) DEFAULT NULL COMMENT 'Possible values: AD, A, B, C',
  `evaluation_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `diagnostic_evaluations`
--

INSERT INTO `diagnostic_evaluations` (`id`, `teacher_id`, `academic_year_id`, `period_id`, `student_id`, `competency_id`, `course_id`, `aula_id`, `grade`, `evaluation_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, 1, 3, 1, 1, 1, 'A', '2026-04-03', '2026-04-02 22:48:30', '2026-04-03 07:30:37', NULL),
(2, 1, 2, 1, 1, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 06:25:32', '2026-04-03 06:25:32', NULL),
(4, 1, 2, 1, 5, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(5, 1, 2, 1, 6, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(6, 1, 2, 1, 9, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(7, 1, 2, 1, 11, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(8, 1, 2, 1, 12, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(9, 1, 2, 1, 14, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(10, 1, 2, 1, 15, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(11, 1, 2, 1, 17, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(12, 1, 2, 1, 18, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(13, 1, 2, 1, 19, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(14, 1, 2, 1, 21, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL),
(15, 1, 2, 1, 22, 1, 1, 1, 'A', '2026-04-03', '2026-04-03 07:30:37', '2026-04-03 07:30:37', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `session_competency_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluation_audit`
--

CREATE TABLE `evaluation_audit` (
  `id` int(11) UNSIGNED NOT NULL,
  `evaluation_id` int(11) UNSIGNED NOT NULL,
  `old_grade` varchar(5) DEFAULT NULL,
  `new_grade` varchar(5) NOT NULL,
  `changed_by` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidences`
--

CREATE TABLE `evidences` (
  `id` int(11) UNSIGNED NOT NULL,
  `evaluation_id` int(11) UNSIGNED DEFAULT NULL,
  `file_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grades`
--

CREATE TABLE `grades` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grades`
--

INSERT INTO `grades` (`id`, `teacher_id`, `name`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'PRIMERO', 1, '2026-03-17 08:40:19', '2026-03-17 08:40:19', NULL),
(2, 1, 'SEGUNDO', 1, '2026-03-17 08:40:35', '2026-03-17 08:40:35', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `header_templates`
--

CREATE TABLE `header_templates` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `header_templates`
--

INSERT INTO `header_templates` (`id`, `teacher_id`, `name`, `description`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 1, 'Doble Logo Frontal', 'Dos logotipos a la izquierda, ideal para convenios o UGEL.', 'screenshot', '2026-03-24 16:26:36', '2026-03-24 16:26:36', NULL),
(5, 1, 'Logos a los Extremos', 'Un logotipo a cada lado (Izquierda y Derecha).', 'extremes', '2026-03-24 16:26:36', '2026-03-24 16:26:36', NULL),
(6, 1, 'Moderno Central', 'Logos centrados sobre el nombre de la institución.', 'centered', '2026-03-24 16:26:36', '2026-03-24 16:26:36', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historical_attendance`
--

CREATE TABLE `historical_attendance` (
  `id` int(11) UNSIGNED NOT NULL,
  `academic_year_id` int(11) UNSIGNED DEFAULT NULL,
  `period_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `total_sessions` int(11) DEFAULT 0,
  `total_presents` int(11) DEFAULT 0,
  `total_absents` int(11) DEFAULT 0,
  `total_tardies` int(11) DEFAULT 0,
  `total_justified` int(11) DEFAULT 0,
  `closing_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historical_evaluations`
--

CREATE TABLE `historical_evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `academic_year_id` int(11) UNSIGNED DEFAULT NULL,
  `period_id` int(11) UNSIGNED DEFAULT NULL,
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `classroom_id` int(11) UNSIGNED DEFAULT NULL,
  `competency_id` int(11) UNSIGNED DEFAULT NULL,
  `competency_name` varchar(255) DEFAULT NULL,
  `final_grade` varchar(10) DEFAULT NULL,
  `is_exonerated` tinyint(1) DEFAULT 0,
  `teacher_comment` text DEFAULT NULL,
  `closing_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historical_session_evaluations`
--

CREATE TABLE `historical_session_evaluations` (
  `id` int(11) UNSIGNED NOT NULL,
  `historical_evaluation_id` int(11) UNSIGNED DEFAULT NULL,
  `session_competency_id` int(11) UNSIGNED DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `session_label` varchar(50) DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `session_theme` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institutions`
--

CREATE TABLE `institutions` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `header_template_id` int(11) UNSIGNED DEFAULT NULL,
  `report_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `institutions`
--

INSERT INTO `institutions` (`id`, `teacher_id`, `name`, `header_template_id`, `report_enabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'UNIDAD DE GESTIÓN EDUCATIVA LOCAL_m', 4, 1, '2026-03-25 02:35:23', '2026-03-25 02:40:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institution_logos`
--

CREATE TABLE `institution_logos` (
  `id` int(11) UNSIGNED NOT NULL,
  `institution_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `url` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `institution_logos`
--

INSERT INTO `institution_logos` (`id`, `institution_id`, `name`, `url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Imagen1.png', '/uploads/logos/0a3d502c8366117a.png', '2026-03-27 11:27:54', '2026-03-27 11:27:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_sessions`
--

CREATE TABLE `password_reset_sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `session_token` varchar(100) DEFAULT NULL,
  `otp_code` varchar(255) DEFAULT NULL,
  `status` enum('pending','scanned','verified','used','expired') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_sessions`
--

INSERT INTO `password_reset_sessions` (`id`, `user_id`, `session_token`, `otp_code`, `status`, `attempts`, `ip_address`, `user_agent`, `expires_at`, `created_at`) VALUES
(1, 1, 'cd5a0da7eebbf7cd4b29210e003d8702dc0272dcfa80f631a0f987a0fa9a7e06', '128815', 'expired', 0, '192.168.1.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 17:46:39', '2026-03-30 10:31:39'),
(2, 1, '65174830acffb038fdc9ebd3ef9160190e41f2ee91378945b3e8bbc06e90e772', '912980', 'expired', 0, '192.168.1.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 20:13:52', '2026-03-30 12:58:52'),
(3, 1, 'ee1d9c95c36a7bacf31887f53995f0084d0cd19b8dee0b6363f956aca86e8f33', '294955', 'expired', 0, '192.168.1.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 20:14:45', '2026-03-30 12:59:45'),
(4, 1, '03034509df7756ef66f99afc23e0c8ef25e9cff44b26daca893603fda58a4e98', '155625', 'expired', 0, '192.168.1.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 20:15:50', '2026-03-30 13:00:50'),
(5, 1, 'a9b5cf2028ac7a808fe502d6eeb1e4c0dd626c32685ae6795eeb79154dee7fdb', '294771', 'expired', 0, '192.168.1.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-03-30 21:32:35', '2026-03-30 14:17:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periods`
--

CREATE TABLE `periods` (
  `id` int(11) UNSIGNED NOT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `is_current` bit(1) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` bit(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `periods`
--

INSERT INTO `periods` (`id`, `academic_year_id`, `name`, `is_current`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'BIMESTRE I', b'1', '2026-03-16', '2026-03-30', b'1', '2026-03-15 03:24:21', '2026-03-15 11:01:25', NULL),
(2, 2, 'BIMESTRE II', b'0', '2026-03-24', '2026-04-04', b'1', '2026-03-15 10:29:49', '2026-03-15 11:01:25', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20240312000000, 'CreateExampleItemsTable', '2026-03-13 07:27:43', '2026-03-13 07:27:43', 0),
(20260313012715, 'CreateItemsTable', '2026-03-13 07:27:43', '2026-03-13 07:27:43', 0),
(20260313172714, 'CreateUsersAndTeachersTable', '2026-03-14 02:39:27', '2026-03-14 02:39:27', 0),
(20260313172759, 'CreateAcademicStructureTable', '2026-03-15 06:41:24', '2026-03-15 06:41:24', 0),
(20260313172760, 'CreateCompetenciesTable', '2026-03-16 10:24:26', '2026-03-16 10:24:26', 0),
(20260313172761, 'CreateAcademicLoadTable', '2026-03-16 10:24:26', '2026-03-16 10:24:26', 0),
(20260313172762, 'CreateDailyRegistryTable', '2026-03-16 10:24:26', '2026-03-16 10:24:27', 0),
(20260314062106, 'AddGenderToTeachersTable', '2026-03-16 04:13:07', '2026-03-16 04:13:07', 0),
(20260314154147, 'AddIsCurrentToAcademicYears', '2026-03-16 04:13:07', '2026-03-16 04:13:07', 0),
(20260315231000, 'CreateCourseGradeClassroomTables', '2026-03-17 10:25:30', '2026-03-17 10:25:30', 0),
(20260317190600, 'UpdateSessionsTableStructure', '2026-03-18 06:05:34', '2026-03-18 06:05:35', 0),
(20260318173400, 'CreateSessionsCompetenciaTable', '2026-03-19 05:28:28', '2026-03-19 05:28:29', 0),
(20260318180300, 'RefactorEvaluationsUseSessionCompetency', '2026-03-21 03:49:32', '2026-03-21 03:49:32', 0),
(20260320153500, 'FixEvaluationsForeignKey', '2026-03-21 04:15:44', '2026-03-21 04:15:44', 0),
(20260321063000, 'AddCourseIdToCompetenciesTable', '2026-03-22 05:55:59', '2026-03-22 05:56:00', 0),
(20260321190300, 'AddStatusToCompetenciesTable', '2026-03-22 06:29:07', '2026-03-22 06:29:07', 0),
(20260322235000, 'CreateScheduleSettingsTable', '2026-03-23 10:50:49', '2026-03-23 10:50:50', 0),
(20260323002300, 'CreateScheduleEntriesTable', '2026-03-23 11:26:41', '2026-03-23 11:26:42', 0),
(20260324105400, 'CreateHeaderTemplatesTable', '2026-03-24 22:24:57', '2026-03-24 22:24:57', 0),
(20260324113600, 'CreateInstitutionsTable', '2026-03-24 23:08:30', '2026-03-24 23:08:30', 0),
(20260324121200, 'AddTeacherIdToInstitutions', '2026-03-24 23:11:43', '2026-03-24 23:11:44', 0),
(20260324121900, 'CreateInstitutionLogosTable', '2026-03-24 23:22:39', '2026-03-24 23:22:39', 0),
(20260324153000, 'AddNameToInstitutionLogos', '2026-03-25 02:28:08', '2026-03-25 02:28:08', 0),
(20260324154200, 'RenameTemplateIdInInstitutions', '2026-03-25 02:39:37', '2026-03-25 02:39:37', 0),
(20260325013524, 'AddIsExoneratedToStudentsTable', '2026-03-25 07:37:00', '2026-03-25 07:37:00', 0),
(20260326012618, 'AddDescriptionToCompetenciesTable', '2026-03-26 07:26:38', '2026-03-26 07:26:39', 0),
(20260327072000, 'CreateStudentStatusHistoryTable', '2026-03-28 07:51:48', '2026-03-28 07:51:49', 0),
(20260327072500, 'CreateEvaluationAuditTable', '2026-03-28 07:51:49', '2026-03-28 07:51:49', 0),
(20260327143600, 'CreateHistoricalTables', '2026-03-28 07:51:49', '2026-03-28 07:51:49', 0),
(20260330000000, 'CreatePasswordResetSessionsTable', '2026-03-30 17:21:18', '2026-03-30 17:21:18', 0),
(20260401120000, 'AddPhoneAndOrderToStudents', '2026-04-02 07:00:14', '2026-04-02 07:00:15', 0),
(20260401220000, 'UnifyNamesAndLastNames', '2026-04-02 10:19:29', '2026-04-02 10:19:30', 0),
(20260402083204, 'CreateDiagnosticEvaluationsTable', '2026-04-02 15:32:27', '2026-04-02 15:32:28', 0),
(20260402213941, 'AddAcademicYearIdToDiagnosticEvaluations', '2026-04-02 23:55:21', '2026-04-02 23:55:21', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_entries`
--

CREATE TABLE `schedule_entries` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `academic_period_id` int(11) UNSIGNED DEFAULT NULL,
  `day_of_week` smallint(6) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `classroom_id` int(11) UNSIGNED DEFAULT NULL,
  `is_break` tinyint(1) DEFAULT 0,
  `color` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `schedule_entries`
--

INSERT INTO `schedule_entries` (`id`, `teacher_id`, `academic_period_id`, `day_of_week`, `start_time`, `end_time`, `course_id`, `classroom_id`, `is_break`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '10:45:00', '11:30:00', NULL, NULL, 1, 'bg-rose-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(2, 1, 1, 2, '10:45:00', '11:30:00', NULL, NULL, 1, 'bg-rose-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(3, 1, 1, 3, '10:45:00', '11:30:00', NULL, NULL, 1, 'bg-rose-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(4, 1, 1, 4, '10:45:00', '11:30:00', NULL, NULL, 1, 'bg-rose-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(5, 1, 1, 5, '10:45:00', '11:30:00', NULL, NULL, 1, 'bg-rose-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(6, 1, 1, 1, '09:15:00', '10:00:00', 1, 1, 0, 'bg-amber-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(7, 1, 1, 1, '10:00:00', '10:45:00', 1, 1, 0, 'bg-amber-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37'),
(8, 1, 1, 4, '09:15:00', '10:00:00', 2, 1, 0, 'bg-amber-500', '2026-03-23 11:57:37', '2026-03-23 11:57:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedule_settings`
--

CREATE TABLE `schedule_settings` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `academic_year_id` int(11) UNSIGNED DEFAULT NULL,
  `start_time` time DEFAULT '08:00:00',
  `end_time` time DEFAULT '18:00:00',
  `slot_duration` int(11) DEFAULT 60 COMMENT 'duration in minutes',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `schedule_settings`
--

INSERT INTO `schedule_settings` (`id`, `teacher_id`, `academic_year_id`, `start_time`, `end_time`, `slot_duration`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '07:00:00', '18:00:00', 45, '2026-03-23 11:08:23', '2026-03-23 11:08:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) UNSIGNED NOT NULL,
  `teacher_id` int(11) UNSIGNED DEFAULT NULL,
  `period_id` int(11) UNSIGNED DEFAULT NULL,
  `course_id` int(11) UNSIGNED DEFAULT NULL,
  `grade_id` int(11) UNSIGNED DEFAULT NULL,
  `classroom_id` int(11) UNSIGNED DEFAULT NULL,
  `date` date DEFAULT NULL,
  `theme` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `session_competencies`
--

CREATE TABLE `session_competencies` (
  `id` int(11) UNSIGNED NOT NULL,
  `competency_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `period_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `grade_id` int(10) UNSIGNED NOT NULL,
  `classroom_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `theme` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `classroom_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `grade_id` int(10) UNSIGNED NOT NULL,
  `dni` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `order_number` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_exonerated` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `students`
--

INSERT INTO `students` (`id`, `teacher_id`, `classroom_id`, `course_id`, `grade_id`, `dni`, `full_name`, `phone_number`, `order_number`, `gender`, `status`, `is_exonerated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, '', 'ALVAREZ JUAREZ SILVANA YARELA', '', 1, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(2, 1, 1, 1, 1, '', 'CALLE CORDOVA ALEXA FABIANNE', '', 2, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(3, 1, 1, 1, 1, '', 'CAMACHO MANRIQUE VANIA DE LOS ANGELES', '', 3, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(4, 1, 1, 1, 1, '', 'CASTILLO QUISPE ANGELINA ALEXANDRA', '', 4, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(5, 1, 1, 1, 1, '', 'CHUICA MENDEZ DANNA ARELIZ', '', 5, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(6, 1, 1, 1, 1, '', 'CORDOVA LOPEZ MIA SILVANA', '', 6, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(7, 1, 1, 1, 1, '', 'DIAZ PALACIOS MACIEL GENESIS', '', 7, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(8, 1, 1, 1, 1, '', 'ELERA BARRERA NOELANY GUADALUPE', '', 8, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(9, 1, 1, 1, 1, '', 'ESTEVES CALLE JESUS DE LOS MILAGROS', '', 9, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(10, 1, 1, 1, 1, '', 'GARCIA JULCAHUANGA ALEJANDRA CAMILA', '', 10, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(11, 1, 1, 1, 1, '', 'GOMEZ POZO MARY CRISTELL', '', 11, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(12, 1, 1, 1, 1, '', 'JUAREZ ANCAJIMA THAMARA ARACELY', '', 12, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(13, 1, 1, 1, 1, '', 'LEON CARRASCO DANIA DEL PILAR', '', 13, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(14, 1, 1, 1, 1, '', 'MAZA ADRIANZEN DAMARIS FERNANDA', '', 14, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(15, 1, 1, 1, 1, '', 'MOGOLLON CALLE LEYDI FABIOLA', '', 15, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(16, 1, 1, 1, 1, '', 'MONCADA TORRES HADA MABEL', '', 16, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(17, 1, 1, 1, 1, '', 'PACHERRES CORNEJO ALINA VALERIA', '', 17, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(18, 1, 1, 1, 1, '', 'PEÑA VASQUEZ GENESIS ANAHI', '', 18, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(19, 1, 1, 1, 1, '', 'PULACHE CHUICA DIANA GUADALUPE', '', 19, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(20, 1, 1, 1, 1, '', 'SANDOVAL ADRIANZEN JOSSELIN', '', 20, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(21, 1, 1, 1, 1, '', 'SEMINARIO VELASQUEZ GUADALUPE GABRIELA', '', 21, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(22, 1, 1, 1, 1, '', 'VALLADOLID COLOMA JUYMI', '', 22, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL),
(23, 1, 1, 1, 1, '', 'ZAPATA ALZAMORA CAMILA', '', 23, 'F', 1, 0, '2026-04-02 11:25:18', '2026-04-02 11:25:18', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_status_history`
--

CREATE TABLE `student_status_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `old_status` tinyint(1) DEFAULT NULL,
  `new_status` tinyint(1) NOT NULL,
  `reason` text DEFAULT NULL,
  `changed_by` int(11) UNSIGNED DEFAULT NULL,
  `change_date` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` char(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `full_name`, `gender`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'González Pérez, María lolo', 'F', '2026-03-14 07:56:41', '2026-04-03 11:20:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'docente1@escuela.com', '$2y$10$ukFQmSEOCACffxCPkopC2.O3/Uusaow/WFW72/ESzX2mlyivGeydO', '2026-03-14 07:54:54', '2026-04-04 00:00:45', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indices de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_classrooms_teacher` (`teacher_id`),
  ADD KEY `fk_classrooms_year` (`academic_year_id`),
  ADD KEY `fk_classrooms_course` (`course_id`),
  ADD KEY `fk_classrooms_grade` (`grade_id`);

--
-- Indices de la tabla `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_competencies_teacher` (`teacher_id`),
  ADD KEY `fk_competencies_year` (`academic_year_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_courses_teachers` (`teacher_id`),
  ADD KEY `fk_courses_academic_years` (`academic_year_id`);

--
-- Indices de la tabla `course_groups`
--
ALTER TABLE `course_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `diagnostic_evaluations`
--
ALTER TABLE `diagnostic_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_diagnostic_evaluation` (`teacher_id`,`academic_year_id`,`period_id`,`student_id`,`competency_id`,`course_id`,`aula_id`),
  ADD KEY `diagnostic_evaluations_ibfk_2` (`period_id`),
  ADD KEY `diagnostic_evaluations_ibfk_3` (`student_id`),
  ADD KEY `diagnostic_evaluations_ibfk_4` (`competency_id`),
  ADD KEY `diagnostic_evaluations_ibfk_5` (`course_id`),
  ADD KEY `diagnostic_evaluations_ibfk_6` (`aula_id`),
  ADD KEY `fk_diag_eval_academic_year` (`academic_year_id`);

--
-- Indices de la tabla `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `session_competency_id` (`session_competency_id`);

--
-- Indices de la tabla `evaluation_audit`
--
ALTER TABLE `evaluation_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indices de la tabla `evidences`
--
ALTER TABLE `evidences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`);

--
-- Indices de la tabla `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `header_templates`
--
ALTER TABLE `header_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `historical_attendance`
--
ALTER TABLE `historical_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hist_att_main` (`period_id`,`student_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indices de la tabla `historical_evaluations`
--
ALTER TABLE `historical_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hist_eval_main` (`period_id`,`student_id`,`course_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `classroom_id` (`classroom_id`),
  ADD KEY `competency_id` (`competency_id`);

--
-- Indices de la tabla `historical_session_evaluations`
--
ALTER TABLE `historical_session_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hist_sess_eval_parent` (`historical_evaluation_id`),
  ADD KEY `session_competency_id` (`session_competency_id`);

--
-- Indices de la tabla `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`header_template_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indices de la tabla `institution_logos`
--
ALTER TABLE `institution_logos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institution_id` (`institution_id`);

--
-- Indices de la tabla `password_reset_sessions`
--
ALTER TABLE `password_reset_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_session_token` (`session_token`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indices de la tabla `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `schedule_entries`
--
ALTER TABLE `schedule_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `academic_period_id` (`academic_period_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indices de la tabla `schedule_settings`
--
ALTER TABLE `schedule_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`,`academic_year_id`),
  ADD KEY `academic_year_id` (`academic_year_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `period_id` (`period_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `grade_id` (`grade_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indices de la tabla `session_competencies`
--
ALTER TABLE `session_competencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_competencies_competency_id` (`competency_id`),
  ADD KEY `fk_session_competencies_teacher_id` (`teacher_id`),
  ADD KEY `fk_session_competencies_period_id` (`period_id`),
  ADD KEY `fk_session_competencies_course_id` (`course_id`),
  ADD KEY `fk_session_competencies_grade_id` (`grade_id`),
  ADD KEY `fk_session_competencies_classroom_id` (`classroom_id`);

--
-- Indices de la tabla `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_students_teacher` (`teacher_id`),
  ADD KEY `idx_students_classroom` (`classroom_id`),
  ADD KEY `idx_students_course` (`course_id`),
  ADD KEY `idx_students_grade` (`grade_id`);

--
-- Indices de la tabla `student_status_history`
--
ALTER TABLE `student_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indices de la tabla `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `competencies`
--
ALTER TABLE `competencies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `course_groups`
--
ALTER TABLE `course_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `diagnostic_evaluations`
--
ALTER TABLE `diagnostic_evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `evaluation_audit`
--
ALTER TABLE `evaluation_audit`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evidences`
--
ALTER TABLE `evidences`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `header_templates`
--
ALTER TABLE `header_templates`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historical_attendance`
--
ALTER TABLE `historical_attendance`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historical_evaluations`
--
ALTER TABLE `historical_evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historical_session_evaluations`
--
ALTER TABLE `historical_session_evaluations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `institutions`
--
ALTER TABLE `institutions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `institution_logos`
--
ALTER TABLE `institution_logos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `password_reset_sessions`
--
ALTER TABLE `password_reset_sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `periods`
--
ALTER TABLE `periods`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `schedule_entries`
--
ALTER TABLE `schedule_entries`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `schedule_settings`
--
ALTER TABLE `schedule_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `session_competencies`
--
ALTER TABLE `session_competencies`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `student_status_history`
--
ALTER TABLE `student_status_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendances_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attendances_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `classrooms`
--
ALTER TABLE `classrooms`
  ADD CONSTRAINT `fk_classrooms_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_classrooms_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_classrooms_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_classrooms_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `competencies`
--
ALTER TABLE `competencies`
  ADD CONSTRAINT `competencies_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_competencies_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_competencies_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_academic_years` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_courses_teachers` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `course_groups`
--
ALTER TABLE `course_groups`
  ADD CONSTRAINT `course_groups_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `diagnostic_evaluations`
--
ALTER TABLE `diagnostic_evaluations`
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_4` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_5` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `diagnostic_evaluations_ibfk_6` FOREIGN KEY (`aula_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_diag_eval_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_4` FOREIGN KEY (`session_competency_id`) REFERENCES `session_competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluation_audit`
--
ALTER TABLE `evaluation_audit`
  ADD CONSTRAINT `evaluation_audit_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_audit_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `evidences`
--
ALTER TABLE `evidences`
  ADD CONSTRAINT `evidences_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `header_templates`
--
ALTER TABLE `header_templates`
  ADD CONSTRAINT `header_templates_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historical_attendance`
--
ALTER TABLE `historical_attendance`
  ADD CONSTRAINT `historical_attendance_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_attendance_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_attendance_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_attendance_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historical_evaluations`
--
ALTER TABLE `historical_evaluations`
  ADD CONSTRAINT `historical_evaluations_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_evaluations_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_evaluations_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_evaluations_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_evaluations_ibfk_5` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_evaluations_ibfk_6` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historical_session_evaluations`
--
ALTER TABLE `historical_session_evaluations`
  ADD CONSTRAINT `historical_session_evaluations_ibfk_1` FOREIGN KEY (`historical_evaluation_id`) REFERENCES `historical_evaluations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historical_session_evaluations_ibfk_2` FOREIGN KEY (`session_competency_id`) REFERENCES `session_competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `institutions`
--
ALTER TABLE `institutions`
  ADD CONSTRAINT `institutions_ibfk_1` FOREIGN KEY (`header_template_id`) REFERENCES `header_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `institutions_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `institution_logos`
--
ALTER TABLE `institution_logos`
  ADD CONSTRAINT `institution_logos_ibfk_1` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_reset_sessions`
--
ALTER TABLE `password_reset_sessions`
  ADD CONSTRAINT `password_reset_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `schedule_entries`
--
ALTER TABLE `schedule_entries`
  ADD CONSTRAINT `schedule_entries_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_entries_ibfk_2` FOREIGN KEY (`academic_period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_entries_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_entries_ibfk_4` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `schedule_settings`
--
ALTER TABLE `schedule_settings`
  ADD CONSTRAINT `schedule_settings_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `schedule_settings_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_5` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sessions_ibfk_6` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `session_competencies`
--
ALTER TABLE `session_competencies`
  ADD CONSTRAINT `fk_session_competencies_classroom_id` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_competencies_competency_id` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_competencies_course_id` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_competencies_grade_id` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_competencies_period_id` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_session_competencies_teacher_id` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_classrooms` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_courses` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_grades` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_teachers` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `student_status_history`
--
ALTER TABLE `student_status_history`
  ADD CONSTRAINT `student_status_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
