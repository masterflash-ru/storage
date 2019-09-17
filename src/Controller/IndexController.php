<?php
/**
 */

namespace Mf\Storage\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class IndexController extends AbstractActionController
{
    protected $ImagesLib;
    
    public function __construct($ImagesLib)
    {
        $this->ImagesLib=$ImagesLib;
    }
    public function indexAction()
    {
        $view=new ViewModel();
        $view->setTerminal(true);
        $this->ImagesLib->clearStorage();
        return $view;
    }
}
