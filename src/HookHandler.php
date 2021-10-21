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

    private function findRepository(Event $event): ?RepositoryHandler
    {
        $key = $event->getKey();
        if (array_key_exists($key, $this->repositories)) {
            return $this->repositories[$key];
        }
        return null;
    }

    public function handle()
    {
        $request = $this->http->getCurrentRequest();

        if ($request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->http->createResponse(
                'Webhook successfully installed. Shown as 404 NOT FOUND to cheat robots (^.^ )',
                StatusCodeInterface::STATUS_NOT_FOUND
            );
        }

        try {
            $this->handleHttpRequest($request);
            return $this->http->createResponse(json_encode([
                'status' => 'success'
            ]), StatusCodeInterface::STATUS_OK)
                ->withHeader('Content-Type', MediaTypes::APPLICATION_JSON);
        } catch (Exception $ex) {
            return $this->http->createResponse(json_encode([
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
        $repository = $this->findRepository($event);

        if (is_null($repository)) {
            return;
        }

        foreach ($repository->getPaths() as $path) {
            chdir($path);
            foreach ($repository->getCommands() as $cmd) {
                exec($cmd, $output, $ret);
                if ($ret != 0) {
                    throw new Exception(sprintf('Error executing %s in %s.', $cmd, $path));
                }
            }
        }
    }
}
