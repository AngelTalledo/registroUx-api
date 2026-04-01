<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\HeaderTemplateServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HeaderTemplateController
{
    use Helpers\AuthHelperTrait;

    private HeaderTemplateServiceInterface $service;
    private UserServiceInterface $userService;

    public function __construct(HeaderTemplateServiceInterface $service, UserServiceInterface $userService)
    {
        $this->service = $service;
        $this->userService = $userService;
    }

    public function index(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $templates = $this->service->getAllTemplates($teacherId);

        return $this->jsonResponse($response, $templates);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $template = $this->service->getTemplateById($id, $teacherId);

        if (!$template) {
            return $this->jsonResponse($response, ['error' => 'Template not found'], 404);
        }

        return $this->jsonResponse($response, $template);
    }

    public function store(Request $request, Response $response): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $data = $request->getParsedBody();
        $data['teacher_id'] = $teacherId;

        if (isset($data['id'])) {
            $template = $this->service->updateTemplate((int)$data['id'], $teacherId, $data);
        } else {
            $template = $this->service->createTemplate($data);
        }

        return $this->jsonResponse($response, $template, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        $data['teacher_id'] = $teacherId;

        $template = $this->service->updateTemplate($id, $teacherId, $data);

        if (!$template) {
            return $this->jsonResponse($response, ['error' => 'Template not found'], 404);
        }

        return $this->jsonResponse($response, $template);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $teacherId = $this->resolveTeacherIdOrResponse($request, $response, $this->userService);
        if ($teacherId instanceof Response) return $teacherId;

        $id = (int)$args['id'];
        $deleted = $this->service->deleteTemplate($id, $teacherId);

        return $this->jsonResponse($response, ['success' => $deleted]);
    }
}
