<?php namespace Bm\Field\Classes;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Rozszerzenie Twiga o metodę generującą linki
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class TwigPostUrl extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Bm_Field_TwigPostUrl';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('post', [$this, 'postUrl'], array()),
            new Twig_SimpleFunction('category', [$this, 'categoryUrl'], array()),
        );
    }

    /**
     * Zwraca url artykułu
     * @param integer $id
     * @return string
     */
    public function postUrl($id)
    {
        return $this->getUrl($id, '\Bm\Field\Models\Post');
    }

    /**
     * Zwraca url kategorii
     * @param integer $id
     * @return string
     */
    public function categoryUrl($id)
    {
        return $this->getUrl($id, '\Bm\Field\Models\Category');
    }

    /**
     * Zwraca urla danego obiektu
     * @param integer $id
     * @return string
     */
    public function getUrl($id, $model)
    {
        $object = $model::find((int)$id);

        if (isset($object->url)) {
            $url = $object->url;
        }

        return \Url::to($url);
    }
}
