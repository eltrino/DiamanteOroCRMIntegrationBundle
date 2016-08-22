<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */

namespace Diamante\OroCRMIntegrationBundle\Command;


use Oro\Bundle\InstallerBundle\CommandExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IntegrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('diamante:integrate');
        $this->setDescription('DiamanteDesk integration routine for OroCRM');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Integrating DiamanteDesk...</info>');

        $executor = $this->getCommandExecutor($input, $output);

        $executor->runCommand('cache:clear');
        $executor->runCommand('oro:migration:load', [
            '--force'             => true,
            '--bundles' => [
                'DiamanteUserBundle',
                'DiamanteDeskBundle',
                'DiamanteEmbeddedFormBundle',
                'DiamanteAutomationBundle'
            ],
        ]);
        $executor->runCommand('oro:migration:data:load');
        $executor->runCommand('diamante:desk:data');

        $executor->runCommand('assets:install');
        $executor->runCommand('assetic:dump');

        $output->writeln('<info>DiamanteDesk successfully integrated!</info>');
    }

    private function getCommandExecutor(InputInterface $input, OutputInterface $output)
    {
        $commandExecutor = new CommandExecutor(
            $input->hasOption('env') ? $input->getOption('env') : null,
            $output,
            $this->getApplication(),
            $this->getContainer()->get('oro_cache.oro_data_cache_manager')
        );

        return $commandExecutor;
    }
}