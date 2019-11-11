<?php
/**
 */

namespace Mf\Storage\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputArgument;



class Clear extends AbstractCommand
{

    protected static $defaultName = 'clear';


    protected function configure()
    {

        $this
            ->setDescription(
                $this->translator->translate('Clear storage')
            )
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'Clear all storage',
                null
            )
            ->setHelp("Clear storage" );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        $this->storage->clearStorage();
    }
    
}