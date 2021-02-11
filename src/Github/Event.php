<?php

namespace Francerz\WebhookDeployer\Github;

use Exception;
use Francerz\Http\Utils\MessageHelper;
use Psr\Http\Message\RequestInterface;

class Event
{
    private $repoName;
    private $branch;
    private $event;

    public static function fromHttpRequest(RequestInterface $req)
    {
        $content = MessageHelper::getContent($req);
        if (is_array($content)) {
            $content = json_decode($content['payload']);
        }
        if (!is_object($content)) {
            throw new Exception('Bad payload data');
        }

        $event = new Event();
        $event->repoName = $content->repository->full_name;
        $event->branch = explode('/', $content->ref)[2];
        $event->event = $req->getHeaderLine('X-Github-Event');

        return $event;
    }

    public function getRepositoryName()
    {
        return $this->repoName;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getKey()
    {
        return "{$this->repoName}@{$this->branch}:{$this->event}";
    }
}