<?php

namespace Francerz\WebhookDeployer;

class RepositoryHandler
{
    private $name;
    private $branch;
    private $event = '*';
    private $paths = [];
    private $commands = [];
    
    public function __construct(string $fullname, string $event = 'push', string $branch = 'master')
    {
        $this->name = $fullname;
        $this->branch = $branch;
        $this->event = $event;
    }
    
    public function getKey()
    {
        return "{$this->event}:{$this->name}@{$this->branch}";
    }


    public function addPath(string $path)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->addPath($p);
            }
            return;
        }
        $this->paths[] = $path;
    }
    public function getPaths()
    {
        return $this->paths;
    }

    public function addCommand(string $command)
    {
        $this->commands[] = $command;
    }

    public function addCommands(array $commands)
    {
        foreach ($commands as $c) $this->addCommand($c);
    }
    public function getCommands()
    {
        return $this->commands;
    }
}