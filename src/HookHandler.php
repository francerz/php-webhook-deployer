<?php

namespace Francerz\WebhookDeployer;

use Exception;
use Francerz\Http\HttpFactory;
use Francerz\Http\Utils\HttpFactoryManager;
use Francerz\Http\Utils\MessageHelper;
use Francerz\WebhookDeployer\Github\Event;

class HookHandler
{
    private $repositories = [];
    private $httpFactory;

    public function __construct()
    {
        $this->httpFactory = new HttpFactoryManager(new HttpFactory());
        MessageHelper::setHttpFactoryManager($this->httpFactory);
    }

    public function addRepository(RepositoryHandler $repo)
    {
        $this->repositories[$repo->getKey()] = $repo;
    }

    private function findRepository(Event $event) : ?RepositoryHandler
    {
        $key = $event->getKey();
        if (array_key_exists($key, $this->repositories)) {
            return $this->repositories[$key];
        }
        return null;
    }

    public function handle()
    {
        $request = MessageHelper::getCurrentRequest();
        $event = Event::fromHttpRequest($request);

        $repository = $this->findRepository($event);

        if (is_null($repository)) {
            return;
        }

        foreach($repository->getPaths() as $path) {
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