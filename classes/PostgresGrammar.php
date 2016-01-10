<?php namespace Bm\Field\Classes;

use Illuminate\Database\Query\Grammars\PostgresGrammar;

/**
 * Grammar
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class PostgresGrammar extends PostgresGrammar
{
    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  string  $value
     * @param  bool    $prefixAlias
     * @return string
     */
    public function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) return $this->getValue($value);

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on it
        // own, and then joins them both back together with the "as" connector.
        if (strpos(strtolower($value), ' as ') !== false)
        {
            $segments = explode(' ', $value);

            if ($prefixAlias) $segments[2] = $this->tablePrefix.$segments[2];

            return $this->wrap($segments[0]).' as '.$this->wrapValue($segments[2]);
        }

        $wrapped = array();
        $sufix = '';

        if ($cast = preg_match('/(.+)(\:\:[a-z]+)/', $value, $matches)) {
            $value = $matches[1];
        }

        $segments = explode('.', $value);

        // If the value is not an aliased table expression, we'll just wrap it like
        // normal, so if there is more than one segment, we will wrap the first
        // segments as if it was a table and the rest as just regular values.
        foreach ($segments as $key => $segment)
        {
            if ($key == 0 && count($segments) > 1)
            {
                $wrapped[] = $this->wrapTable($segment);
            }
            else
            {
                $wrapped[] = $this->wrapValue($segment);
            }
        }

        return $cast
            ? '(' . implode('.', $wrapped) . ')' . $matches[2]
            : implode('.', $wrapped);
    }
    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value === '*') return $value;

        $parts = explode('->>', $value);
        
        if (count($parts) > 1) {
            return '"'.str_replace('"', '""', $parts[0]).'"->>'.$parts[1];
        }

        return '"'.str_replace('"', '""', $value).'"';
    }
}
