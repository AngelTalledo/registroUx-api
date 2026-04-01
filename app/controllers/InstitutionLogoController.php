<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\InstitutionLogoServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InstitutionLogoController
{
    use Helpers\AuthHelperTrait;
    use Helpers\FileHelperTrait;

    private InstitutionLogoServiceInterface $service;
    private UserServiceInterface $userService;
    private array $uploadSettings;

    public function __construct(InstitutionLogoServiceInterface $service, UserServiceInterface $userService, array $uploadSettings)
    {
        $this->service = $service;
        $this->userService = $userService;
        $this->uploadSettings = $uploadSettings;
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $institutionId = (int)$args['institution_id'];
        $logos = $this->service->getLogosByInstitution($institutionId, $teacherId);

        $baseUrl = $this->getBaseUrl($request);
        
        $logos->transform(function ($logo) use ($baseUrl) {
            if ($logo->url && !str_starts_with($logo->url, 'http')) {
                $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
            }
            return $logo;
        });

        return $this->jsonResponse($response, $logos);
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $institutionId = (int)$args['institution_id'];
        $uploadedFiles = $request->getUploadedFiles();

        // Handle file upload
        if (empty($uploadedFiles['image'])) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'No image uploaded'
            ], 400);
        }

        $uploadedFile = $uploadedFiles['image'];

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Upload error'
            ], 400);
        }

        // Validate image
        $mimeType = $uploadedFile->getClientMediaType();
        if (!str_starts_with($mimeType, 'image/')) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Invalid file type. Only images are allowed.'
            ], 400);
        }

        $directory = $this->uploadSettings['base_path'] . '/logos';
        $filename = $this->moveUploadedFile($directory, $uploadedFile);

        $url = $this->uploadSettings['public_url'] . '/logos/' . $filename;

        $body = $request->getParsedBody();
        $name = $body['name'] ?? pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME);

        $logo = $this->service->createLogo([
            'institution_id' => $institutionId,
            'name' => $name,
            'url' => $url
        ], $teacherId);

        if (!$logo) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Institution not found or unauthorized'
            ], 403);
        }

        $baseUrl = $this->getBaseUrl($request);
        if ($logo->url && !str_starts_with($logo->url, 'http')) {
            $logo->url = rtrim($baseUrl, '/') . '/' . ltrim($logo->url, '/');
        }

        return $this->jsonResponse($response, $logo, 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $deleted = $this->service->deleteLogo($id, $teacherId);

        return $this->jsonResponse($response, ['success' => $deleted]);
    }
}
