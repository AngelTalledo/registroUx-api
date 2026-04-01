<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ItemServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Psr\Log\LoggerInterface;

class ItemController
{
    private ItemServiceInterface $service;
    private LoggerInterface $logger;

    public function __construct(ItemServiceInterface $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        $this->logger->info("Obteniendo todos los items");
        $items = $this->service.getAllItems();
        
        $response->getBody()->write(json_encode($items));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $this->logger->info("Creando nuevo item", ['data' => $data]);

        // Validation
        $validator = v::key('name', v::stringType()->notEmpty()->length(3, 50));

        try {
            $validator->assert($data);
        } catch (\Respect\Validation\Exceptions\NestedValidationException $e) {
            $errors = $e->getMessages();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'errors' => $errors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $item = $this->service.createItem($data);
        
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $item
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}
