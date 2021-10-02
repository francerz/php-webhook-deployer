<?php

namespace Francerz\WebhookDeployer\Github;

class PushEvent extends Event
{
    /**
     * @var string
     */
    protected $branch;

    public function __construct()
    {
        parent::__construct('push');
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getKey()
    {
        return "{$this->event}:{$this->repoName}@{$this->branch}";
    }
}