<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\InstitutionServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InstitutionController
{
    use Helpers\AuthHelperTrait;
    use Helpers\FileHelperTrait;

    private InstitutionServiceInterface $service;
    private UserServiceInterface $userService;
    private \App\Services\InstitutionLogoServiceInterface $logoService;
    private array $uploadSettings;

    public function __construct(
        InstitutionServiceInterface $service,
        UserServiceInterface $userService,
        \App\Services\InstitutionLogoServiceInterface $logoService,
        array $uploadSettings
    ) {
        $this->service = $service;
        $this->userService = $userService;
        $this->logoService = $logoService;
        $this->uploadSettings = $uploadSettings;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $institutions = $this->service->getAllInstitutions($teacherId);

        $baseUrl = $this->getBaseUrl($request);
        foreach ($institutions as $inst) {
            foreach ($inst->logos as $logo) {
                if ($logo->url && !str_starts_with($logo->url, 'http')) {
                    $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
                }
            }
        }


        return $this->jsonResponse($response, $institutions);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $institution = $this->service->getInstitutionById($id, $teacherId);

        if (!$institution) {
            return $this->jsonResponse($response, ['error' => 'Institution not found'], 404);
        }

        $baseUrl = $this->getBaseUrl($request);
        foreach ($institution->logos as $logo) {
            if ($logo->url && !str_starts_with($logo->url, 'http')) {
                $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
            }
        }

        return $this->jsonResponse($response, $institution);
    }

    public function store(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();
        $data['teacher_id'] = $teacherId;

        if (isset($data['id'])) {
            $institution = $this->service->updateInstitution((int)$data['id'], $teacherId, $data);
        } else {
            $institution = $this->service->createInstitution($data);
        }

        if ($institution) {
            $errors = $this->handleLogos($request, $institution, $teacherId);
            // Re-fetch to include newly added logos in the response
            $id = $institution->id;
            $institution = $this->service->getInstitutionById($id, $teacherId);
            
            $baseUrl = $this->getBaseUrl($request);
            foreach ($institution->logos as $logo) {
                if ($logo->url && !str_starts_with($logo->url, 'http')) {
                    $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
                }
            }

            if (!empty($errors)) {
                return $this->jsonResponse($response, [
                    'institution' => $institution,
                    'upload_warnings' => $errors
                ], 201);
            }
        }

        return $this->jsonResponse($response, $institution, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $data['teacher_id'] = $teacherId;

        $institution = $this->service->updateInstitution($id, $teacherId, $data);

        if (!$institution) {
            return $this->jsonResponse($response, ['error' => 'Institution not found'], 404);
        }

        $errors = $this->handleLogos($request, $institution, $teacherId);
        
        // Re-fetch to include newly added logos in the response
        $institution = $this->service->getInstitutionById($id, $teacherId);

        $baseUrl = $this->getBaseUrl($request);
        foreach ($institution->logos as $logo) {
            if ($logo->url && !str_starts_with($logo->url, 'http')) {
                $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
            }
        }

        if (!empty($errors)) {
            return $this->jsonResponse($response, [
                'institution' => $institution,
                'upload_warnings' => $errors
            ]);
        }

        return $this->jsonResponse($response, $institution);
    }

    private function handleLogos(Request $request, $institution, int $teacherId): array
    {
        $errors = [];
        $uploadedFiles = $request->getUploadedFiles();
        
        // Handle both 'logos_attachments' and 'logos_attachments[]' literal keys
        $attachments = $uploadedFiles['logos_attachments'] ?? $uploadedFiles['logos_attachments[]'] ?? null;
        
        if ($attachments) {
            if (!is_array($attachments)) {
                $attachments = [$attachments];
            }

            foreach ($attachments as $uploadedFile) {
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    if ($this->isImage($uploadedFile)) {
                        $directory = $this->uploadSettings['base_path'] . '/logos';
                        $filename = $this->moveUploadedFile($directory, $uploadedFile);
                        $url = $this->uploadSettings['public_url'] . '/logos/' . $filename;

                        $this->logoService->createLogo([
                            'institution_id' => $institution->id,
                            'name' => $uploadedFile->getClientFilename(),
                            'url' => $url
                        ], $teacherId);
                    } else {
                        $errors[] = "File " . $uploadedFile->getClientFilename() . " is not a valid image. Type: " . $uploadedFile->getClientMediaType();
                    }
                } else {
                    $errors[] = "Error uploading " . $uploadedFile->getClientFilename() . ": " . $this->getUploadErrorMessage($uploadedFile->getError());
                }
            }
        } else {
            // Optional: warning if we expected logos but none were found in those keys
            // $errors[] = "No files found in 'logos_attachments' or 'logos_attachments[]' fields.";
        }

        return $errors;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            default => 'Unknown upload error',
        };
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $deleted = $this->service->deleteInstitution($id, $teacherId);

        return $this->jsonResponse($response, ['success' => $deleted]);
    }
}
