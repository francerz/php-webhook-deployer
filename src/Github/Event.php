<?php

namespace Francerz\WebhookDeployer\Github;

use Exception;
use Francerz\Http\Utils\HttpHelper;
use Psr\Http\Message\RequestInterface;

class Event
{
    private $repoName;
    private $type;

    public static function fromHttpRequest(RequestInterface $req)
    {
        $content = HttpHelper::getContent($req);
        if (is_array($content)) {
            $content = json_decode($content['payload']);
        }
        if (!is_object($content)) {
            throw new Exception('Bad payload data');
        }

        $event = $req->getHeaderLine('X-Github-Event');
        switch ($event) {
            case 'push':
                $event = new PushEvent();
                $event->repoName = $content->repository->full_name;
                $event->branch = explode('/', $content->ref)[2];
                return $event;
            default:
                $event = new Event($event);
                $event->repoName = $content->repository->full_name;
                return $event;
        }
    }

    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getRepositoryName()
    {
        return $this->repoName;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getKey()
    {
        return "{$this->event}:{$this->repoName}";
    }
}
