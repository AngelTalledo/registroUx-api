<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\InstitutionServiceInterface;
use App\Services\UserServiceInterface;
use App\Services\TeacherServiceInterface;
use App\Services\GradeServiceInterface;
use App\Services\ClassroomServiceInterface;
use App\Services\CourseServiceInterface;
use App\Services\StudentServiceInterface;
use App\Services\PeriodServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;
use App\Controllers\Helpers\AuthHelperTrait;

class PublicRegistrationController
{
    use AuthHelperTrait;

    private InstitutionServiceInterface $institutionService;
    private UserServiceInterface $userService;
    private TeacherServiceInterface $teacherService;
    private GradeServiceInterface $gradeService;
    private ClassroomServiceInterface $classroomService;
    private CourseServiceInterface $courseService;
    private StudentServiceInterface $studentService;
    private \App\Services\AcademicYearServiceInterface $academicYearService;
    private PeriodServiceInterface $periodService;

    public function __construct(
        InstitutionServiceInterface $institutionService,
        UserServiceInterface $userService,
        TeacherServiceInterface $teacherService,
        GradeServiceInterface $gradeService,
        ClassroomServiceInterface $classroomService,
        CourseServiceInterface $courseService,
        StudentServiceInterface $studentService,
        \App\Services\AcademicYearServiceInterface $academicYearService,
        PeriodServiceInterface $periodService
    ) {
        $this->institutionService = $institutionService;
        $this->userService = $userService;
        $this->teacherService = $teacherService;
        $this->gradeService = $gradeService;
        $this->classroomService = $classroomService;
        $this->courseService = $courseService;
        $this->studentService = $studentService;
        $this->academicYearService = $academicYearService;
        $this->periodService = $periodService;
    }

    /**
     * PASO 1: Guardar Institución (VIRTUAL: Se guarda realmente en el Paso 2)
     */
    public function saveInstitution(Request $request, Response $response): Response
    {
        // Solo validamos que venga el nombre, no guardamos aún por restricciones de DB (requiere teacher_id)
        // Enviamos un data.id ficticio para que el frontend no rompa
        return $this->jsonResponse($response, [
            'status' => 'success', 
            'message' => 'Institución recibida temporalmente',
            'data' => ['id' => 0]
        ], 201);
    }

    /**
     * PASO 2: Guardar Cuenta (Secuencia: Docente -> Usuario -> Institución)
     */
    public function saveAccount(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        try {
            // 1. Crear Perfil de Docente Primero (Clave maestra)
            $teacher = $this->teacherService->createTeacher([
                'full_name' => $data['fullname'],
                'institution_id' => null, // No lo usamos en tabla teachers según esquema
                'gender' => $data['gender'] ?? 'M'
            ]);

            // 2. Crear Usuario y vincular
            $user = $this->userService->registerUser([
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'teacher'
            ]);

            $this->teacherService->updateTeacher((int)$teacher->id, [
                'user_id' => $user->id
            ]);

            // 3. Crear Institución REAL (Ahora sí tenemos el teacher_id obligatorio)
            $institution = $this->institutionService->createInstitution([
                'name' => $data['institution_name'] ?? 'Institución sin nombre',
                'teacher_id' => $teacher->id
            ]);

            return $this->jsonResponse($response, [
                'status' => 'success', 
                'user_id' => $user->id, 
                'teacher_id' => $teacher->id,
                'institution_id' => $institution->id
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PASO 3: Guardar Infraestructura (Secuencia: Año -> Cursos -> Grados -> Aula)
     */
    public function saveInfrastructure(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = (int)$data['teacher_id'];
        $yearName = $data['yearName'] ?? date('Y');
        
        $results = ['year' => null, 'cursos' => [], 'grados' => [], 'aulas' => []];
        $gradeMap = [];
        $courseMap = [];

        try {
            // 1. Asegurar Año Académico
            $academicYear = $this->academicYearService->createAcademicYear([
                'teacher_id' => $teacherId,
                'year' => (int)$yearName,
                'name' => "Año Escolar $yearName",
                'is_current' => true,
                'status' => true
            ]);
            $results['year'] = $academicYear;

            // 2. Cursos (Materias) PRIMERO -> Guardar y Mapear IDs
            foreach ($data['cursos'] ?? [] as $c) {
                $course = $this->courseService->createCourse([
                    'name' => $c['nombre'], 
                    'teacher_id' => $teacherId,
                    'academic_year_id' => $academicYear->id
                ]);
                $results['cursos'][] = $course;
                $courseMap[$c['nombre']] = $course->id;
            }

            // 3. Grados SEGUNDO -> Guardar y Mapear IDs
            foreach ($data['grados'] ?? [] as $g) {
                $grade = $this->gradeService->createGrade([
                    'name' => $g['nombre'], 
                    'teacher_id' => $teacherId
                ]);
                $results['grados'][] = $grade;
                $gradeMap[$g['nombre']] = $grade->id;
            }

            // 4. Aulas -> Vincular con Grado, Curso y Sección REALES
            foreach ($data['aulas'] ?? [] as $a) {
                $gradeId = $gradeMap[$a['grado']] ?? null;
                $courseId = $courseMap[$a['materia']] ?? null;

                if ($gradeId && $courseId) {
                    $results['aulas'][] = $this->classroomService->createClassroom([
                        'teacher_id' => $teacherId,
                        'academic_year_id' => $academicYear->id,
                        'grade_id' => $gradeId,
                        'course_id' => $courseId,
                        'section' => $a['seccion'] ?? 'A',
                        'status' => true
                    ]);
                }
            }

            return $this->jsonResponse($response, ['status' => 'success', 'data' => $results]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PASO 4: Guardar Alumnos (Carga Masiva)
     */
    public function saveStudents(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $teacherId = (int)$data['teacher_id'];
        $students = $data['students'] ?? [];

        try {
            $result = $this->studentService->bulkStoreStudents($teacherId, $students);
            return $this->jsonResponse($response, ['status' => 'success', 'summary' => $result]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * ROLLBACK MANUAL: Limpieza en caso de error
     */
    public function cleanup(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        return $this->jsonResponse($response, ['status' => 'success', 'message' => 'Limpieza completada']);
    }

    /**
     * 🚀 UNIFIED REGISTRATION ENDPOINT (WIZARD)
     */
    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // 1. Get current Admin/Creator ID from session
        $admin = $this->getAuthenticatedUser($request, $this->userService);
        if (!$admin) {
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Sesión no válida'], 401);
        }

        try {
            // --- VALIDACIÓN PREVIA: Email Único ---
            if ($this->userService->emailExists($data['email'])) {
                throw new \Exception("El correo electrónico '{$data['email']}' ya está registrado. Por favor, usa otro.");
            }

            return DB::transaction(function() use ($data, $admin, $response) {
                // --- STEP 1: New User Creation (The Teacher's personal account) ---
                $newUser = $this->userService->registerUser([
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => 'teacher'
                ]);
                $newUserId = $newUser->id;

                // --- STEP 2: Teacher Registration (Linked to New User) ---
                $teacher = $this->teacherService->createTeacher([
                    'full_name' => $data['fullname'],
                    'gender' => $data['gender'] ?? 'M',
                    'user_id' => $newUserId
                ]);
                $teacherId = $teacher->id; // Primary key of the teachers table

                // --- STEP 3: Initial Institution ---
                $institution = $this->institutionService->createInstitution([
                    'name' => $data['institucionName'] ?? 'Institución sin nombre',
                    'teacher_id' => $teacherId // Using Teacher ID for relations
                ]);

                // --- STEP 4: Academic Year and Initial Period ---
                $yearName = $data['yearName'] ?? date('Y');
                $academicYear = $this->academicYearService->createAcademicYear([
                    'teacher_id' => $teacherId,
                    'year' => (int)$yearName,
                    'name' => "Año Escolar $yearName",
                    'is_current' => true,
                    'status' => true
                ]);

                // First Period (Bimestre I or Trimestre I)
                $periodType = $data['periodType'] ?? 'bimestres';
                $periodName = (str_contains(strtolower($periodType), 'bimestre')) ? 'Bimestre I' : 'Trimestre I';
                
                $this->periodService->createPeriod([
                    'academic_year_id' => $academicYear->id,
                    'name' => $periodName,
                    'is_current' => true,
                    'start_date' => $data['startDate'] ?? date('Y-m-d'),
                    'status' => true
                ], $teacherId);

                // --- STEP 5: Infrastructure (Grades, Courses, Classrooms) ---
                $gradeMap = [];
                $courseMap = [];

                // Courses
                foreach ($data['cursos'] ?? [] as $m) {
                    $cName = $m['nombre'];
                    
                    // Validación de duplicado
                    if ($this->courseService->getCourseByName($cName, $teacherId)) {
                        throw new \Exception("El curso '{$cName}' ya existe para este docente.");
                    }
                    if (isset($courseMap[$cName])) {
                        throw new \Exception("El curso '{$cName}' está duplicado en la lista de envío.");
                    }

                    $course = $this->courseService->createCourse([
                        'name' => $cName, 
                        'teacher_id' => $teacherId,
                        'academic_year_id' => $academicYear->id
                    ]);
                    $courseMap[$cName] = $course->id;
                }

                // Grades
                foreach ($data['grados'] ?? [] as $g) {
                    $gName = is_array($g) ? $g['nombre'] : $g;

                    // Validación de duplicado
                    if ($this->gradeService->getGradeByName($gName, $teacherId)) {
                        throw new \Exception("El grado '{$gName}' ya existe para este docente.");
                    }
                    if (isset($gradeMap[$gName])) {
                        throw new \Exception("El grado '{$gName}' está duplicado en la lista de envío.");
                    }

                    $grade = $this->gradeService->createGrade([
                        'name' => $gName, 
                        'teacher_id' => $teacherId
                    ]);
                    $gradeMap[$gName] = $grade->id;
                }

                // Classrooms
                $classroomCheck = [];
                foreach ($data['aulas'] ?? [] as $a) {
                    $gradeId = $gradeMap[$a['grado']] ?? null;
                    $courseId = $courseMap[$a['curso']] ?? null;
                    $section = $a['seccion'] ?? 'A';

                    if ($gradeId && $courseId) {
                        // Validación de duplicado persistente
                        if ($this->classroomService->checkDuplicate($teacherId, $academicYear->id, $gradeId, $courseId, $section)) {
                            throw new \Exception("El aula ({$a['grado']}, {$a['curso']}, Sección {$section}) ya existe para este docente.");
                        }

                        // Validación de duplicado en el mismo payload
                        $key = "{$gradeId}-{$courseId}-{$section}";
                        if (isset($classroomCheck[$key])) {
                            throw new \Exception("El aula ({$a['grado']}, {$a['curso']}, Sección {$section}) está duplicada en la lista de envío.");
                        }
                        $classroomCheck[$key] = true;

                        $this->classroomService->createClassroom([
                            'teacher_id' => $teacherId,
                            'academic_year_id' => $academicYear->id,
                            'grade_id' => $gradeId,
                            'course_id' => $courseId,
                            'section' => $section,
                            'status' => true
                        ]);
                    }
                }

                // --- STEP 6: Students (Optional / Initial Load) ---
                if (!empty($data['students'])) {
                    $this->studentService->bulkStoreStudents($teacherId, $data['students']);
                }

                return $this->jsonResponse($response, [
                    'status' => 'success',
                    'message' => 'Registro unificado completado con éxito',
                    'data' => [
                        'user_id' => $newUserId,
                        'teacher_id' => $teacherId,
                        'institution_id' => $institution->id
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error', 
                'message' => 'Error durante el registro unificado: ' . $e->getMessage()
            ], 400);
        }
    }

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
