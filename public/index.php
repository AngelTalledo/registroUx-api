<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use App\Middleware\ErrorHandlerMiddleware;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\JwtAuthentication;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/config/settings.php';
$containerBuilder->addDefinitions($settings);

// Build Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Set Base Path to handle subdirectories in XAMPP
$app->setBasePath((function () {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    // On Windows, dirname might return \ so we normalize it
    $basePath = str_replace('\\', '/', dirname($scriptName));
    return $basePath === '/' ? '' : $basePath;
})());

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Handle method overrides (_METHOD field in form data)
$app->add(new \Slim\Middleware\MethodOverrideMiddleware());


// Add Routing Middleware
$app->addRoutingMiddleware();

// Set up Logger
$container->set('logger', function ($c) {
    $loggerFactory = require __DIR__ . '/../app/config/logger.php';
    return $loggerFactory($c);
});

// Set up Database (Eloquent)
$container->set('db', function ($c) {
    $dbFactory = require __DIR__ . '/../app/config/database.php';
    return $dbFactory($c);
});

// Force DB initialization
$container->get('db');

// Add Controller to container
$container->set(\App\Controllers\ItemController::class, function ($c) {
    return new \App\Controllers\ItemController($c->get(\App\Services\ItemServiceInterface::class), $c->get('logger'));
});

$container->set(\App\Controllers\DashboardController::class, function ($c) {
    return new \App\Controllers\DashboardController(
        new \App\Repositories\AcademicYearRepository(),
        new \App\Repositories\PeriodRepository(),
        new \App\Repositories\ScheduleEntryRepository(),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\UserController::class, function ($c) {
    return new \App\Controllers\UserController($c->get(\App\Services\UserServiceInterface::class), $c->get('logger'));
});

$container->set(\App\Controllers\TeacherController::class, function ($c) {
    return new \App\Controllers\TeacherController($c->get(\App\Services\TeacherServiceInterface::class), $c->get('logger'));
});

$container->set(\App\Controllers\AcademicYearController::class, function ($c) {
    return new \App\Controllers\AcademicYearController(
        $c->get(\App\Services\AcademicYearServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\GradeController::class, function ($c) {
    return new \App\Controllers\GradeController(
        $c->get(\App\Services\GradeServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\CourseController::class, function ($c) {
    return new \App\Controllers\CourseController(
        $c->get(\App\Services\CourseServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\ClassroomController::class, function ($c) {
    return new \App\Controllers\ClassroomController(
        $c->get(\App\Services\ClassroomServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\AuthController::class, function ($c) {
    return new \App\Controllers\AuthController(
        $c->get(\App\Services\UserServiceInterface::class), 
        $c->get('logger'),
        $c->get(\App\Services\AuthServiceInterface::class)
    );
});

$container->set(\App\Controllers\CompetencyController::class, function ($c) {
    return new \App\Controllers\CompetencyController(
        $c->get(\App\Services\CompetencyServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\StudentController::class, function ($c) {
    return new \App\Controllers\StudentController(
        $c->get(\App\Services\StudentServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\SessionController::class, function ($c) {
    return new \App\Controllers\SessionController(
        $c->get(\App\Services\SessionServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\AttendanceController::class, function ($c) {
    return new \App\Controllers\AttendanceController(
        $c->get(\App\Services\AttendanceServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\EvaluationController::class, function ($c) {
    return new \App\Controllers\EvaluationController(
        $c->get(\App\Services\EvaluationServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\SessionCompetencyController::class, function ($c) {
    return new \App\Controllers\SessionCompetencyController(
        $c->get(\App\Services\SessionCompetencyServiceInterface::class),
        $c->get(\App\Services\SessionServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\AttendanceReportController::class, function ($c) {
    return new \App\Controllers\AttendanceReportController(
        $c->get(\App\Services\AttendanceReportServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\EvaluationReportController::class, function ($c) {
    return new \App\Controllers\EvaluationReportController(
        $c->get(\App\Services\EvaluationReportServiceInterface::class),
        $c->get(\App\Services\EvaluationExcelServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\InstitutionLogoController::class, function ($c) {
    return new \App\Controllers\InstitutionLogoController(
        $c->get(\App\Services\InstitutionLogoServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('settings')['uploads']
    );
});

$container->set(\App\Controllers\HeaderTemplateController::class, function ($c) {
    return new \App\Controllers\HeaderTemplateController(
        $c->get(\App\Services\HeaderTemplateServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\InstitutionController::class, function ($c) {
    return new \App\Controllers\InstitutionController(
        $c->get(\App\Services\InstitutionServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get(\App\Services\InstitutionLogoServiceInterface::class),
        $c->get('settings')['uploads']
    );
});

$container->set(\App\Controllers\ScheduleEntryController::class, function ($c) {
    return new \App\Controllers\ScheduleEntryController(
        $c->get(\App\Services\ScheduleEntryServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\ScheduleSettingController::class, function ($c) {
    return new \App\Controllers\ScheduleSettingController(
        $c->get(\App\Services\ScheduleSettingServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class)
    );
});

$container->set(\App\Controllers\HistoricalClosingController::class, function ($c) {
    return new \App\Controllers\HistoricalClosingController(
        $c->get(\App\Services\HistoricalClosingServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Controllers\PasswordResetController::class, function ($c) {
    return new \App\Controllers\PasswordResetController(
        $c->get(\App\Services\PasswordResetServiceInterface::class),
        $c->get('logger')
    );
});

// Bind interfaces to implementations
$container->set(\App\Services\ItemServiceInterface::class, function ($c) {
    return new \App\Services\Implements\ItemService(new \App\Repositories\ItemRepository());
});

$container->set(\App\Services\UserServiceInterface::class, function ($c) {
    return new \App\Services\Implements\UserService(new \App\Repositories\UserRepository());
});

$container->set(\App\Services\TeacherServiceInterface::class, function ($c) {
    return new \App\Services\Implements\TeacherService(new \App\Repositories\TeacherRepository());
});

$container->set(\App\Services\AcademicYearServiceInterface::class, function ($c) {
    return new \App\Services\Implements\AcademicYearService(new \App\Repositories\AcademicYearRepository());
});

$container->set(\App\Services\GradeServiceInterface::class, function ($c) {
    return new \App\Services\Implements\GradeService(new \App\Repositories\GradeRepository());
});

$container->set(\App\Services\CourseServiceInterface::class, function ($c) {
    return new \App\Services\Implements\CourseService(new \App\Repositories\CourseRepository());
});

$container->set(\App\Services\ClassroomServiceInterface::class, function ($c) {
    return new \App\Services\Implements\ClassroomService(new \App\Repositories\ClassroomRepository());
});

$container->set(\App\Services\PeriodServiceInterface::class, function ($c) {
    return new \App\Services\Implements\PeriodService(
        new \App\Repositories\PeriodRepository(),
        new \App\Repositories\AcademicYearRepository()
    );
});

$container->set(\App\Controllers\PeriodController::class, function ($c) {
    return new \App\Controllers\PeriodController(
        $c->get(\App\Services\PeriodServiceInterface::class),
        $c->get(\App\Services\UserServiceInterface::class),
        $c->get('logger')
    );
});

$container->set(\App\Services\StudentServiceInterface::class, function ($c) {
    return new \App\Services\Implements\StudentService(new \App\Repositories\StudentRepository());
});

$container->set(\App\Services\SessionServiceInterface::class, function ($c) {
    return new \App\Services\Implements\SessionService(new \App\Repositories\SessionRepository());
});

$container->set(\App\Services\AttendanceServiceInterface::class, function ($c) {
    return new \App\Services\Implements\AttendanceService(new \App\Repositories\AttendanceRepository());
});

$container->set(\App\Services\CompetencyServiceInterface::class, function ($c) {
    return new \App\Services\Implements\CompetencyService(new \App\Repositories\CompetencyRepository());
});

$container->set(\App\Services\EvaluationServiceInterface::class, function ($c) {
    return new \App\Services\Implements\EvaluationService(new \App\Repositories\EvaluationRepository());
});

$container->set(\App\Services\SessionCompetencyServiceInterface::class, function ($c) {
    return new \App\Services\Implements\SessionCompetencyService(
        new \App\Repositories\SessionCompetencyRepository(),
        new \App\Repositories\AcademicYearRepository(),
        new \App\Repositories\CompetencyRepository()
    );
});

$container->set(\App\Services\AttendanceReportServiceInterface::class, function ($c) {
    return new \App\Services\Implements\AttendanceReportService($c->get('settings')['uploads']);
});

$container->set(\App\Services\EvaluationExcelServiceInterface::class, function ($c) {
    return new \App\Services\Implements\EvaluationExcelService($c->get('settings')['uploads']);
});

$container->set(\App\Services\EvaluationReportServiceInterface::class, function ($c) {
    return new \App\Services\Implements\EvaluationReportService($c->get('settings')['uploads']);
});

$container->set(\App\Services\ScheduleEntryServiceInterface::class, function ($c) {
    return new \App\Services\Implements\ScheduleEntryService(new \App\Repositories\ScheduleEntryRepository());
});

$container->set(\App\Services\InstitutionLogoServiceInterface::class, function ($c) {
    return new \App\Services\Implements\InstitutionLogoService(new \App\Repositories\InstitutionLogoRepository());
});

$container->set(\App\Services\HeaderTemplateServiceInterface::class, function ($c) {
    return new \App\Services\Implements\HeaderTemplateService(new \App\Repositories\HeaderTemplateRepository());
});

$container->set(\App\Services\InstitutionServiceInterface::class, function ($c) {
    return new \App\Services\Implements\InstitutionService(new \App\Repositories\InstitutionRepository());
});

$container->set(\App\Services\ScheduleSettingServiceInterface::class, function ($c) {
    return new \App\Services\Implements\ScheduleSettingService(new \App\Repositories\ScheduleSettingRepository());
});

$container->set(\App\Services\HistoricalClosingServiceInterface::class, function ($c) {
    return new \App\Services\Implements\HistoricalClosingService();
});

$container->set(\App\Services\PasswordResetServiceInterface::class, function ($c) {
    return new \App\Services\Implements\PasswordResetService();
});

$container->set(\App\Services\AuthServiceInterface::class, function ($c) {
    $settings = $c->get('settings')['jwt'];
    return new \App\Services\Implements\AuthService($settings['secret'], $settings['algorithm']);
});

// Set up custom JWT Middleware
$container->set(\App\Middleware\JwtMiddleware::class, function ($c) {
    return new \App\Middleware\JwtMiddleware($c->get(\App\Services\AuthServiceInterface::class));
});

// Add Custom Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $container->get('settings')['displayErrorDetails'],
    $container->get('settings')['logError'],
    $container->get('settings')['logErrorDetails'],
    $container->get('logger')
);
$errorMiddleware->setDefaultErrorHandler(new ErrorHandlerMiddleware($app));

// Add CORS Middleware (Outer most middleware)
$app->add(new \App\Middleware\CorsMiddleware());

// Define Routes
$routes = require __DIR__ . '/../app/routes/api.php';
$routes($app);

$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'message' => 'Welcome to the registroUx-api',
        'health_check' => '/api/health',
        'status' => 'active'
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/health', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
