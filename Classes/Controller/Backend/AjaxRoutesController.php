<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class AjaxRoutesController
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    )
    {
    }

    public function updateConfigurationWithSuggestion(ServerRequestInterface $request): ResponseInterface
    {
        $field = $request->getParsedBody()['field']
            ?? throw new \InvalidArgumentException(
                'Please provide a number',
                1580585107,
            );
        $value =  $request->getParsedBody()['value']
            ?? throw new \InvalidArgumentException(
                'Please provide a number',
                1580585107,
            );


        //todo : actually do something lol
        $result = ['success' => true];


        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            json_encode(['result' => $result], JSON_THROW_ON_ERROR),
        );
        return $response;
    }
}
