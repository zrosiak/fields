<?php namespace Bm\Field\Classes;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Rozszerzenie Twiga o metodę excerpt
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class TwigExcerpt extends Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Bm_Field_TwigExcerpt';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('excerpt', [$this, 'excerpt'], array()),
        );
    }

    /**
     * Zwraca string przycięty do ostatniego wystąpienia znaku w danym limicie
     * lub przycięcie do limitu i ...
     * @param string $value
     * @param integer $limit limit znaków
     * @param string $characters lista znaków kończących zdanie
     * @param string $separator znak kończący string przycięty po limicie
     * @return string
     */
    public function excerpt(
        $value,
        $limit = 160,
        $characters = '.?!',
        $separator = '...'
    ) {
        $value = strip_tags($value);

        if (strlen($value) > $limit) {
            $string = substr(strip_tags($value), 0, $limit);
            $check_position = function($string, $characters) {
                foreach (str_split($characters) as $character) {
                    $position = strrpos($string, $character);

                    if ($position !== false) {
                        return ++$position;
                    }
                }

                return false;
            };
            $position = $check_position($string, $characters);

            $value = $position === false
                ? substr($string, 0, $check_position($string, ' ') - 1 ?: $limit) . $separator
                : substr($string, 0, $position);
        }

        return $value;
    }
}
