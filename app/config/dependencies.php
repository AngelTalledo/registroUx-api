<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        // --- Core Shared Services ---
        
        'logger' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerFactory = require __DIR__ . '/logger.php';
            return $loggerFactory($c);
        },

        \Psr\Log\LoggerInterface::class => get('logger'),

        'db' => function (ContainerInterface $c) {
            $dbFactory = require __DIR__ . '/database.php';
            return $dbFactory($c);
        },

        // --- Interface Mappings (Services) ---

        \App\Services\AcademicYearServiceInterface::class => autowire(\App\Services\Implements\AcademicYearService::class),
        \App\Services\AttendanceReportServiceInterface::class => function (ContainerInterface $c) {
            return new \App\Services\Implements\AttendanceReportService($c->get('settings')['uploads']);
        },
        \App\Services\AttendanceServiceInterface::class => autowire(\App\Services\Implements\AttendanceService::class),
        \App\Services\AuthServiceInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['jwt'];
            return new \App\Services\Implements\AuthService($settings['secret'], $settings['algorithm']);
        },
        \App\Services\ClassroomServiceInterface::class => autowire(\App\Services\Implements\ClassroomService::class),
        \App\Services\CompetencyServiceInterface::class => autowire(\App\Services\Implements\CompetencyService::class),
        \App\Services\CourseServiceInterface::class => autowire(\App\Services\Implements\CourseService::class),
        \App\Services\DiagnosticEvaluationServiceInterface::class => autowire(\App\Services\Implements\DiagnosticEvaluationService::class),
        \App\Services\EvaluationExcelServiceInterface::class => function (ContainerInterface $c) {
            return new \App\Services\Implements\EvaluationExcelService($c->get('settings')['uploads']);
        },
        \App\Services\EvaluationReportServiceInterface::class => function (ContainerInterface $c) {
            return new \App\Services\Implements\EvaluationReportService($c->get('settings')['uploads']);
        },
        \App\Services\EvaluationServiceInterface::class => autowire(\App\Services\Implements\EvaluationService::class),
        \App\Services\GradeServiceInterface::class => autowire(\App\Services\Implements\GradeService::class),
        \App\Services\HeaderTemplateServiceInterface::class => autowire(\App\Services\Implements\HeaderTemplateService::class),
        \App\Services\HistoricalClosingServiceInterface::class => autowire(\App\Services\Implements\HistoricalClosingService::class),
        \App\Services\InstitutionLogoServiceInterface::class => autowire(\App\Services\Implements\InstitutionLogoService::class),
        \App\Services\InstitutionServiceInterface::class => autowire(\App\Services\Implements\InstitutionService::class),
        \App\Services\ItemServiceInterface::class => autowire(\App\Services\Implements\ItemService::class),
        \App\Services\PasswordResetServiceInterface::class => autowire(\App\Services\Implements\PasswordResetService::class),
        \App\Services\PeriodServiceInterface::class => autowire(\App\Services\Implements\PeriodService::class),
        \App\Services\ScheduleEntryServiceInterface::class => autowire(\App\Services\Implements\ScheduleEntryService::class),
        \App\Services\ScheduleSettingServiceInterface::class => autowire(\App\Services\Implements\ScheduleSettingService::class),
        \App\Services\SessionCompetencyServiceInterface::class => autowire(\App\Services\Implements\SessionCompetencyService::class),
        \App\Services\SessionServiceInterface::class => autowire(\App\Services\Implements\SessionService::class),
        \App\Services\StudentServiceInterface::class => autowire(\App\Services\Implements\StudentService::class),
        \App\Services\TeacherServiceInterface::class => autowire(\App\Services\Implements\TeacherService::class),
        \App\Services\UserServiceInterface::class => autowire(\App\Services\Implements\UserService::class),

        // --- Middleware with Dependencies ---
        
        \App\Middleware\JwtMiddleware::class => autowire(),
        
        // --- Controllers & Repositories ---
        // (Optional: PHP-DI will autowire these automatically because they use concrete classes
        // but we can register them here if we want to ensure they are singletons)
        
        \App\Repositories\EvaluationRepository::class => autowire(),
        \App\Controllers\DashboardController::class => autowire(),
        
        \App\Controllers\InstitutionController::class => function (ContainerInterface $c) {
            return new \App\Controllers\InstitutionController(
                $c->get(\App\Services\InstitutionServiceInterface::class),
                $c->get(\App\Services\UserServiceInterface::class),
                $c->get(\App\Services\InstitutionLogoServiceInterface::class),
                $c->get('settings')['uploads']
            );
        },

        \App\Controllers\InstitutionLogoController::class => function (ContainerInterface $c) {
            return new \App\Controllers\InstitutionLogoController(
                $c->get(\App\Services\InstitutionLogoServiceInterface::class),
                $c->get(\App\Services\UserServiceInterface::class),
                $c->get('settings')['uploads']
            );
        },
    ]);
};
