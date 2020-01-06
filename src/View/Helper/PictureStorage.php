<?php
/*
* помощник view для вывода из хранилища фото имени файла фото, готового для вставки в HTML
* новый формат <picture>
*/

namespace Mf\Storage\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\EscapeHtml;
use Laminas\View\Helper\EscapeHtmlAttr;


/**
 * помощник
 */
class PictureStorage extends AbstractHelper 
{
    /**
     * @var Doctype
     */
    protected $doctypeHelper;

    /**
     * @var EscapeHtml
     */
    protected $escapeHtmlHelper;

    /**
     * @var EscapeHtmlAttr
     */
    protected $escapeHtmlAttrHelper;

	protected $ImagesLib;
    protected $options=[
        "attributes"=>[],
        "default_image"=>""
    ];

public function __construct ($ImagesLib)
{
	$this->ImagesLib=$ImagesLib;
}

public function __invoke($razdel=null,$razdel_id=null,$item_storage_name=null,array $options=[])
{
    $this->setOptions($options);
    if (empty($razdel) && empty($razdel_id) && empty($item_storage_name)){
        return $this;
    }
    
    return $this->render($razdel,$razdel_id,$item_storage_name);
}

/**
* собственно рендер нового тега <picture>
*/
public function render($razdel,$razdel_id,$item_storage_name)
{
    $view=$this->getView();
    $defimg="";
    $images=$this->ImagesLib->loadPictures($razdel,$razdel_id,$item_storage_name);
    $pic=["<picture>"];
    if (!isset($this->options["attributes"]["alt"])){
        $this->options["attributes"]["alt"]="";
    }
    if (empty($images)) {
        //нет изображения, загружаем заглушку
        $images["default"]=$this->options["default_image"];
    } elseif(!isset($images["default"]) && isset($images[$item_storage_name])){
        //это версия 1 хранилища. там один файл, ключ это имя жлемента хранилища
        $images=["default"=>$images[$item_storage_name]];
    }

    foreach ($images as $type=>$item){
        $img=$view->basePath($item);
        switch ($type){
            case "webp":{
                $pic[]="<source type=\"image/webp\" srcset=\"{$img}\">";
                break;
            }
            case "jpf":{
                $pic[]="<source type=\"image/jp2\" srcset=\"{$img}\">";
                break;
            }
            case "default":{
                $attr=$this->createAttributesString($this->options["attributes"]);
                $defimg="<img src=\"{$img}\" {$attr} />";
                break;
            } 
        }
    }
    
    $pic[]=$defimg;
    $pic[]="</picture>";
    return implode("\n",$pic);
}

/**
* установка опций, ключ-имя опции
*/
public function setOptions(array $options=[])
{
    if (!empty($options) && is_array($options)){
        foreach ($options as $k => $v) {
            $this->options["attributes"][$k] = $v;
        }
    }
    return $this;
}

    /**
     * генерация строки атрибут готовых для внедрения в HTML
     *
     * все атрибуты экранируются
     *
     * @param  array $attributes
     * @return string
     */
    public function createAttributesString(array $attributes)
    {
        $attributes = $this->prepareAttributes($attributes);
        $escape     = $this->getEscapeHtmlHelper();
        $escapeAttr = $this->getEscapeHtmlAttrHelper();
        $strings    = [];

        foreach ($attributes as $key => $value) {
            $key = strtolower($key);

            // @todo Escape event attributes like AbstractHtmlElement view helper does in htmlAttribs ??
            try {
                $escapedAttribute = $escapeAttr($value);
                $strings[] = sprintf('%s="%s"', $escape($key), $escapedAttribute);
            } catch (EscaperException $x) {
                // If an escaper exception happens, escape only the key, and use a blank value.
                $strings[] = sprintf('%s=""', $escape($key));
            }
        }

        return implode(' ', $strings);
    }

    /**
     * получить escapeHtml помощник
     *
     * @return EscapeHtml
     */
    protected function getEscapeHtmlHelper()
    {
        if ($this->escapeHtmlHelper) {
            return $this->escapeHtmlHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlHelper = $this->view->plugin('escapehtml');
        }

        if (! $this->escapeHtmlHelper instanceof EscapeHtml) {
            $this->escapeHtmlHelper = new EscapeHtml();
        }

        return $this->escapeHtmlHelper;
    }

    /**
     * получить escapeHtmlAttr помощник
     *
     * @return EscapeHtmlAttr
     */
    protected function getEscapeHtmlAttrHelper()
    {
        if ($this->escapeHtmlAttrHelper) {
            return $this->escapeHtmlAttrHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->escapeHtmlAttrHelper = $this->view->plugin('escapehtmlattr');
        }

        if (! $this->escapeHtmlAttrHelper instanceof EscapeHtmlAttr) {
            $this->escapeHtmlAttrHelper = new EscapeHtmlAttr();
        }

        return $this->escapeHtmlAttrHelper;
    }

    /**
     * подготовка к рендерингу, все атрибуты приводятся к нижнему регистру
     * @param  array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attribute = strtolower($key);
        }
        return $attributes;
    }

}
