<?php namespace Bm\Field\Classes;

use Twig_Extension;
use Twig_SimpleFilter;
use October\Rain\Database\Attach\Resizer;

/**
 * Rozszerzenie Twiga o metodę thumbnail
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class TwigThumbnail extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Bm_Field_TwigThumbnail';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('thumbnail', [$this, 'thumbnail'], array('is_variadic' => true)),
        );
    }

    /**
     * Tworzy miniaturke i zwraca do niej ścieżkę
     * @param string $file ścieżka do pliku
     * @param array $options szerokość, wysokość, tryb
     * @return string
     */
    public function thumbnail($file, array $options = array())
    {
        $public_path = \App::publicPath();
        
        if (is_file($public_path . $file)) {
            $fileinfo = pathinfo($file);
            $imagesize = getimagesize($public_path . $file);
            $width = isset($options[0]) ? $options[0] : null;
            $height = isset($options[1]) ? $options[1] : null;
            $mode = isset($options[2]) ? $options[2] : 'landscape';
            $offset = isset($options[3]) ? $options[3] : [0, 0];
            $thumb = $fileinfo['dirname'] . '/thumb_' .
                $fileinfo['filename'] . '_' .
                $width . 'x' .
                $height . '_' .
                $mode . '.' .
                $fileinfo['extension'];
    
            if (
                file_exists($public_path . $thumb)
                && !filemtime($public_path . $thumb) <= filemtime($public_path . $file)
            ) {
                $file = $thumb;
            } elseif (
                $imagesize[0] != $width
                && $resizer = Resizer::open($public_path . $file)->resize($width, $height, $mode, $offset)
            ) {
                $resizer->save($public_path . $thumb);
                $file = $thumb;
            }
        }

        return $file;
    }
}
