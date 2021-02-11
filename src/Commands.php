<?php

namespace Francerz\WebhookDeployer;

abstract class Commands
{
    public static function gitCheckoutClear() : string
    {
        return 'git checkout -- .';
    }

    public static function gitPull($force = false) : string
    {
        $cmd = 'git pull';
        $cmd.= $force ? ' -f' : '';
        return $cmd;
    }

    public static function composerInstall($dev = false) : string
    {
        $cmd = 'composer install';
        $cmd.= $dev ? '' : ' --no-dev';
        return $cmd;
    }
}