<?php

namespace Francerz\WebhookDeployer;

class RepositoryHandler
{
    private $name;
    private $branch;
    private $event = '*';
    private $paths = [];
    private $commands = [];
    
    public function __construct(string $fullname, string $branch = 'master', string $event = 'push')
    {
        $this->name = $fullname;
        $this->branch = $branch;
        $this->event = $event;
    }
    
    public function getKey()
    {
        return "{$this->name}@{$this->branch}:{$this->event}";
    }


    public function addPath(string $path)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->addPath($p);
            }
            return;
        }
        $path[] = $path;
    }
    public function getPaths()
    {
        return $this->paths;
    }

    public function addCommand(string $command)
    {
        $this->commands[] = $command;
    }
    public function getCommands()
    {
        return $this->commands;
    }
}