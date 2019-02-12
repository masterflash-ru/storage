<?php
/**
*/
namespace Mf\Storage\Filter\Adapter;


//use Exception;


class AbstractFileSystem
{


	protected $_options = [];


public function __construct(array $options = [])
{
	$this->setOptions($options);
}

/**
* установка опций, передается из фильтра
*/
public function setOptions($options)
{
	$this->_options = $options;
}


}
	

