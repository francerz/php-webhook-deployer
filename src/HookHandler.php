<?php

namespace Francerz\WebhookDeployer;

use Exception;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Francerz\Http\HttpFactory;
use Francerz\Http\Utils\Constants\MediaTypes;
use Francerz\WebhookDeployer\Github\Event;
use Psr\Http\Message\ServerRequestInterface;

class HookHandler
{
    private $repositories = [];
    private $http;

    public function __construct()
    {
        $this->http = HttpFactory::getHelper();
    }

    public function addRepository(RepositoryHandler $repo)
    {
        $this->repositories[$repo->getKey()] = $repo;
    }

    private function findRepository(string $key): ?RepositoryHandler
    {
        return $this->repositories[$key] ?? null;
    }

    public function handle(?ServerRequestInterface $request = null)
    {
        if (is_null($request)) {
            $request = $this->http->getCurrentRequest();
        }

        if ($request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->http->createResponse(
                'Webhook successfully installed. Shown as 404 NOT FOUND to cheat robots (^.^ )',
                StatusCodeInterface::STATUS_NOT_FOUND
            );
        }

        try {
            return $this->handleHttpRequest($request);
        } catch (Exception $ex) {
            return $this->http
                ->createResponse(json_encode([
                    'status' => 'error',
                    'error' => $ex->getMessage()
                ]), StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)
                ->withHeader('Content-Type', MediaTypes::APPLICATION_JSON);
        }

        return $this->http->createResponse();
    }

    private function handleHttpRequest(ServerRequestInterface $request)
    {
        $event = Event::fromHttpRequest($request);
        $repository = $this->findRepository($event->getKey());

        if (is_null($repository)) {
            return $this->http
                ->createResponse(json_encode([
                    'status' => StatusCodeInterface::STATUS_NOT_FOUND,
                    'error' => "Not Found {$event->getKey()}"
                ]), StatusCodeInterface::STATUS_NOT_FOUND)
                ->withHeader('Content-Type', MediaTypes::APPLICATION_JSON);
        }

        foreach ($repository->getPaths() as $path) {
            chdir($path);
            foreach ($repository->getCommands() as $cmd) {
                exec("$cmd 2>&1", $output, $ret);
                if ($ret != 0) {
                    throw new Exception(sprintf(
                        "Error executing %s in %s\n%s",
                        $cmd,
                        $path,
                        implode("\n", $output)
                    ));
                }
            }
        }

        return $this->http
            ->createResponse(json_encode([
                'status' => StatusCodeInterface::STATUS_OK,
                'description' => "Success {$repository->getKey()}"
            ]))
            ->withHeader('Content-Type', MediaTypes::APPLICATION_JSON);
    }
}
