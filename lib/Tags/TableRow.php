<?php

namespace Liquid\Tags;

use Liquid\Liquid;
use Liquid\Utils;


class TableRow extends \Liquid\Block
{

    protected static $Syntax;

    public static function init()
    {
        static::$Syntax = '/(\w+)\s+in\s+(' . Liquid::$PART_QuotedFragment . '+)/';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array $options
     *
     * @throws \Liquid\Exceptions\SyntaxError
     */
    public function __construct($tag_name, $markup, $options)
    {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->variable_name = $matches[1];
            $this->collection_name = $matches[2];
            $this->attributes = array();

            preg_match_all(Liquid::$TagAttributes, $markup, $matches);
            foreach ($matches[1] as $key => $match) {
                $this->attributes[$match] = $matches[2][$key];
            }
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }
    }

    /**
     * @param \Liquid\Context $context
     *
     * @return string
     */
    public function render(&$context)
    {
        if (!isset($context[$this->collection_name])) {
            return '';
        }

        $collection = $context[$this->collection_name];
        $from = isset($this->attributes['offset']) && $this->attributes['offset'] ? (int)$context[$this->attributes['offset']] : 0;
        $to = isset($this->attributes['limit']) && $this->attributes['limit'] ? $from + (int)$context[$this->attributes['limit']] : null;

        $collection = Utils::slice_collection($collection, $from, $to);
        $length = count($collection);

        $cols = (int)$context[$this->attributes['cols']];

        $row = 1;
        $col = 0;

        $result = "<tr class=\"row1\">\n";

        $variable_name =& $this->variable_name;

        $nodelist =& $this->nodelist;
        $self = $this;
        $context->stack(function (&$context) use ($self, &$nodelist, &$collection, &$variable_name, &$result, &$length, &$row, &$col, &$cols) {
            foreach ($collection as $index => $item) {
                $context[$variable_name] = $item;
                $context['tablerowloop'] = array(
                    'length' => $length,
                    'index' => $index + 1,
                    'index0' => $index,
                    'col' => $col + 1,
                    'col0' => $col,
                    'rindex' => $length - $index,
                    'rindex0' => $length - $index - 1,
                    'first' => ($index == 0),
                    'last' => ($index == $length - 1),
                    'col_first' => ($col == 0),
                    'col_last' => ($col == $cols - 1)
                );

                $col++;

                $result .= "<td class=\"col{$col}\">" . $self->render_all($nodelist, $context) . '</td>';

                if (($col == $cols) && ($index != $length - 1)) {
                    $col = 0;
                    $row++;
                    $result .= "</tr>\n<tr class=\"row{$row}\">";
                }
            }
        });

        $result .= "</tr>\n";
        return $result;
    }
}

TableRow::init();
