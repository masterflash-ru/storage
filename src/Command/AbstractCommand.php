<?php

namespace Mf\Storage\Command;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Zend\I18n\Translator\Translator;
use Exception;

/**
 * общий функционал
 */
abstract class AbstractCommand extends Command
{
    
    /**
    * ServiceManager ZF3 полностью инициализаированный
    */
    protected $ServiceManager;
    

    /**
    * переводчик ZF3
    */
    protected $translator;
    
    /**
    * Объект работы с хранилищем
    */
    protected $storage;

    /**
    * инициализация приложения ZF3, 
    */
    public function __construct($ServiceManager)
    {
        $this->ServiceManager=$ServiceManager;
        $this->storage=$this->ServiceManager->get("FilesLib");
    
        $this->translator = new Translator();
        $this->translator->addTranslationFile("PhpArray", __DIR__."/../i18/ru.php","default","ru_RU");
        parent::__construct();
    }
    
    
    
}
