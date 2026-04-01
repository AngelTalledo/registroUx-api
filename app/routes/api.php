<?php

declare(strict_types=1);

use App\Controllers\ItemController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/items', [ItemController::class, 'index']);
        $group->post('/items', [ItemController::class, 'store']);

        // Password Reset (Public)
        $group->group('/password-reset', function (RouteCollectorProxy $pr) {
            $pr->post('/request', [\App\Controllers\PasswordResetController::class, 'requestReset']);
            $pr->post('/otp', [\App\Controllers\PasswordResetController::class, 'viewOtp']);
            $pr->post('/verify', [\App\Controllers\PasswordResetController::class, 'verifyOtp']);
            $pr->post('/reset', [\App\Controllers\PasswordResetController::class, 'resetPassword']);
        });

        // Auth group
        $group->group('/auth', function (RouteCollectorProxy $auth) {
            $auth->post('/login', [\App\Controllers\AuthController::class, 'login']);
            $auth->get('/session', [\App\Controllers\AuthController::class, 'session']);
            $auth->post('/refresh', [\App\Controllers\AuthController::class, 'refresh']);
        });

        // Usuarios
        $group->get('/users', [\App\Controllers\UserController::class, 'index']);
        $group->post('/users', [\App\Controllers\UserController::class, 'store']);
        $group->get('/users/{id}', [\App\Controllers\UserController::class, 'show']);

        // Docentes
        $group->get('/teachers', [\App\Controllers\TeacherController::class, 'index']);
        $group->post('/teachers', [\App\Controllers\TeacherController::class, 'store']);
        $group->put('/teachers/{id}', [\App\Controllers\TeacherController::class, 'update']);

        // Años Académicos
        $group->get('/academic-years', [\App\Controllers\AcademicYearController::class, 'index']);
        $group->post('/academic-years', [\App\Controllers\AcademicYearController::class, 'store']);
        $group->get('/academic-years/{id}', [\App\Controllers\AcademicYearController::class, 'show']);
        $group->put('/academic-years/{id}', [\App\Controllers\AcademicYearController::class, 'update']);
        $group->delete('/academic-years/{id}', [\App\Controllers\AcademicYearController::class, 'delete']);
        $group->patch('/academic-years/{id}/set-current', [\App\Controllers\AcademicYearController::class, 'setCurrent']);

        // Period Routes
        $group->get('/periods', [\App\Controllers\PeriodController::class, 'index']);
        
        $group->post('/periods', [\App\Controllers\PeriodController::class, 'store']);
        $group->get('/periods/{id}', [\App\Controllers\PeriodController::class, 'show']);
        $group->put('/periods/{id}', [\App\Controllers\PeriodController::class, 'update']);
        $group->delete('/periods/{id}', [\App\Controllers\PeriodController::class, 'delete']);
        $group->patch('/periods/{id}/set-current', [\App\Controllers\PeriodController::class, 'setCurrent']);
    
        // Grados
        $group->get('/grades', [\App\Controllers\GradeController::class, 'index']);
        $group->post('/grades', [\App\Controllers\GradeController::class, 'store']);
        $group->get('/grades/{id}', [\App\Controllers\GradeController::class, 'show']);
        $group->put('/grades/{id}', [\App\Controllers\GradeController::class, 'update']);
        $group->delete('/grades/{id}', [\App\Controllers\GradeController::class, 'delete']);
    
        // Cursos
        $group->get('/courses', [\App\Controllers\CourseController::class, 'index']);
        $group->post('/courses', [\App\Controllers\CourseController::class, 'store']);
        $group->get('/courses/{id}', [\App\Controllers\CourseController::class, 'show']);
        $group->put('/courses/{id}', [\App\Controllers\CourseController::class, 'update']);
        $group->delete('/courses/{id}', [\App\Controllers\CourseController::class, 'delete']);
    
        // Aulas
        $group->get('/classrooms', [\App\Controllers\ClassroomController::class, 'index']);
        $group->post('/classrooms', [\App\Controllers\ClassroomController::class, 'store']);
        $group->get('/classrooms/{id}', [\App\Controllers\ClassroomController::class, 'show']);
        $group->put('/classrooms/{id}', [\App\Controllers\ClassroomController::class, 'update']);
        $group->delete('/classrooms/{id}', [\App\Controllers\ClassroomController::class, 'delete']);
    
        //Competencias
        $group->get('/competencies', [\App\Controllers\CompetencyController::class, 'index']);
        $group->post('/competencies', [\App\Controllers\CompetencyController::class, 'store']);
        $group->get('/competencies/{id}', [\App\Controllers\CompetencyController::class, 'show']);
        $group->put('/competencies/{id}', [\App\Controllers\CompetencyController::class, 'update']);
        $group->delete('/competencies/{id}', [\App\Controllers\CompetencyController::class, 'delete']);
    
        // Estudiantes
        $group->get('/students/my-courses', [\App\Controllers\StudentController::class, 'myCourses']);
        $group->get('/students', [\App\Controllers\StudentController::class, 'index']);
        $group->post('/students', [\App\Controllers\StudentController::class, 'store']);
        $group->get('/students/{id}', [\App\Controllers\StudentController::class, 'show']);
        $group->put('/students/{id}', [\App\Controllers\StudentController::class, 'update']);
        $group->delete('/students/{id}', [\App\Controllers\StudentController::class, 'delete']);    
        
        // Sesiones
        $group->get('/sessions', [\App\Controllers\SessionController::class, 'index']);
        $group->get('/sessions/deletedList', [\App\Controllers\SessionController::class, 'deleted']);
        $group->post('/sessions', [\App\Controllers\SessionController::class, 'store']);
        $group->get('/sessions/{id}', [\App\Controllers\SessionController::class, 'show']);
        $group->put('/sessions/{id}', [\App\Controllers\SessionController::class, 'update']);
        $group->delete('/sessions/{id}', [\App\Controllers\SessionController::class, 'delete']);
        $group->patch('/sessions/{id}/restore', [\App\Controllers\SessionController::class, 'restore']);
        
        // Asistencias
        $group->get('/attendances', [\App\Controllers\AttendanceController::class, 'index']);
        $group->post('/attendances', [\App\Controllers\AttendanceController::class, 'store']);
        $group->get('/attendances/{id}', [\App\Controllers\AttendanceController::class, 'show']);
        $group->put('/attendances/{id}', [\App\Controllers\AttendanceController::class, 'update']);
        $group->delete('/attendances/{id}', [\App\Controllers\AttendanceController::class, 'delete']);
        $group->post('/attendances/report', [\App\Controllers\AttendanceController::class, 'report']);
        $group->post('/reports/attendance', [\App\Controllers\AttendanceReportController::class, 'generateReport']);
        $group->post('/reports/evaluation', [\App\Controllers\EvaluationReportController::class, 'generateReport']);
        $group->post('/attendances/all', [\App\Controllers\AttendanceController::class, 'saveAll']);
  
        // Evaluaciones
        $group->get('/evaluations', [\App\Controllers\EvaluationController::class, 'index']);
        $group->post('/evaluations', [\App\Controllers\EvaluationController::class, 'store']);
        $group->post('/evaluations/upsert', [\App\Controllers\EvaluationController::class, 'upsert']);
        $group->get('/evaluations/{id}', [\App\Controllers\EvaluationController::class, 'show']);
        $group->put('/evaluations/{id}', [\App\Controllers\EvaluationController::class, 'update']);
        $group->delete('/evaluations/{id}', [\App\Controllers\EvaluationController::class, 'delete']);
        $group->post('/evaluations/report', [\App\Controllers\EvaluationController::class, 'report']);
   
        // Evidencias
        $group->get('/evidences', [\App\Controllers\EvidenceController::class, 'index']);
        $group->post('/evidences', [\App\Controllers\EvidenceController::class, 'store']);
        $group->get('/evidences/{id}', [\App\Controllers\EvidenceController::class, 'show']);
        $group->put('/evidences/{id}', [\App\Controllers\EvidenceController::class, 'update']);
        $group->delete('/evidences/{id}', [\App\Controllers\EvidenceController::class, 'delete']);

        // Session Competencies
        $group->get('/session-competencies', [\App\Controllers\SessionCompetencyController::class, 'index']);
        $group->get('/session-competencies/deleted', [\App\Controllers\SessionCompetencyController::class, 'deleted']);
        $group->post('/session-competencies', [\App\Controllers\SessionCompetencyController::class, 'store']);
        $group->post('/restore', [\App\Controllers\SessionCompetencyController::class, 'restore']);

        $group->delete('/session-competencies/{id}', [\App\Controllers\SessionCompetencyController::class, 'delete']);

        // Historical Closing
        $group->post('/historical/close-period', [\App\Controllers\HistoricalClosingController::class, 'closePeriod']);

        // Schedule Settings
        $group->get('/schedule-settings', [\App\Controllers\ScheduleSettingController::class, 'index']);
        $group->post('/schedule-settings', [\App\Controllers\ScheduleSettingController::class, 'store']);

        // Schedule Entries
        $group->get('/schedule-entries', [\App\Controllers\ScheduleEntryController::class, 'index']);
        $group->post('/schedule-entries', [\App\Controllers\ScheduleEntryController::class, 'store']);

        // Dashboard
        $group->get('/dashboard/current-schedule', [\App\Controllers\DashboardController::class, 'currentSchedule']);

        // Institutions and Header Templates
        $group->get('/header-templates', [\App\Controllers\HeaderTemplateController::class, 'index']);
        $group->post('/header-templates', [\App\Controllers\HeaderTemplateController::class, 'store']);
        $group->get('/header-templates/{id}', [\App\Controllers\HeaderTemplateController::class, 'show']);
        $group->put('/header-templates/{id}', [\App\Controllers\HeaderTemplateController::class, 'update']);
        $group->delete('/header-templates/{id}', [\App\Controllers\HeaderTemplateController::class, 'delete']);

        $group->get('/institutions', [\App\Controllers\InstitutionController::class, 'index']);
        $group->post('/institutions', [\App\Controllers\InstitutionController::class, 'store']);
        $group->get('/institutions/{id}', [\App\Controllers\InstitutionController::class, 'show']);
        $group->map(['PUT', 'POST'], '/institutions/{id}', [\App\Controllers\InstitutionController::class, 'update']);
        $group->delete('/institutions/{id}', [\App\Controllers\InstitutionController::class, 'delete']);

        // Institution Logos
        $group->get('/institutions/{institution_id}/logos', [\App\Controllers\InstitutionLogoController::class, 'index']);
        $group->post('/institutions/{institution_id}/logos', [\App\Controllers\InstitutionLogoController::class, 'store']);
        $group->delete('/institution-logos/{id}', [\App\Controllers\InstitutionLogoController::class, 'delete']);
    
    })->add(\App\Middleware\JwtMiddleware::class);
};
