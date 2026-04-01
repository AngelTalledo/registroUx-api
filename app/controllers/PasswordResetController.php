<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\PasswordResetServiceInterface;
use App\Services\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;
use Exception;

class PasswordResetController
{
    private PasswordResetServiceInterface $service;
    private LoggerInterface $logger;

    public function __construct(PasswordResetServiceInterface $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function requestReset(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->logger->info("Intento de solicitud de recuperación de contraseña", ['email' => $data['email'] ?? 'desconocido']);

        $validator = v::key('email', v::email());

        try {
            $validator->assert($data);
            $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
            $userAgent = $request->getHeaderLine('User-Agent') ?? null;

            $token = $this->service->requestReset($data['email'], $ip, $userAgent);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Solicitud de recuperación iniciada. El código ha sido generado.',
                'data' => [
                    'session_token' => $token
                ]
            ]);
        } catch (Exception $e) {
            $this->logger->error('Error en requestReset: ' . $e->getMessage());
            
            // Per user request, return 200 even for errors like "User not found"
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 200);
        }
    }

    public function viewOtp(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = v::key('token', v::stringType()->notEmpty());

        try {
            $validator->assert($data);
            $sessionInfo = $this->service->getOtpByToken($data['token']);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'data' => $sessionInfo
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 200); 
        }
    }

    public function verifyOtp(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = v::key('token', v::stringType()->notEmpty())
                        ->key('otp_code', v::stringType()->length(6, 6)->digit());

        try {
            $validator->assert($data);
            $isVerified = $this->service->verifyOtp($data['token'], $data['otp_code']);

            if ($isVerified) {
                return $this->jsonResponse($response, [
                    'status' => 'success',
                    'message' => 'Código verificado correctamente'
                ]);
            }

            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => 'Código OTP incorrecto'
            ], 400);

        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function resetPassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = v::key('token', v::stringType()->notEmpty())
                        ->key('new_password', v::stringType()->length(6, null));

        try {
            $validator->assert($data);
            $this->service->resetPassword($data['token'], $data['new_password']);

            return $this->jsonResponse($response, [
                'status' => 'success',
                'message' => 'Contraseña actualizada con éxito. Ya puede iniciar sesión.'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse($response, [
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
