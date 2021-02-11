<?php

use Francerz\WebhookDeployer\Commands;
use Francerz\WebhookDeployer\HookHandler;
use Francerz\WebhookDeployer\RepositoryHandler;

$handler = new HookHandler();

$repo = new RepositoryHandler('tecnm-colima/siitec2-pagos','master','push');
$repo->addPath('/var/www/repos/siitec2-pagos');
$repo->addCommand(Commands::gitCheckoutClear());
$repo->addCommand(Commands::gitPull(true));
$repo->addCommand(Commands::composerInstall());
$handler->addRepository($repo);

$repo = new RepositoryHandler('tecnm-colima/siitec2-docencia','master','push');
$repo->addPath('/var/www/repos/siitec2-docencia');
$repo->addCommand(Commands::gitCheckoutClear());
$repo->addCommand(Commands::gitPull(true));
$repo->addCommand(Commands::composerInstall());
$handler->addRepository($repo);

$handler->handle();