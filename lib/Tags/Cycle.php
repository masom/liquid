<?php

namespace Liquid\Tags;

use Liquid\Exceptions\SyntaxError;
use \Liquid\Liquid;
use Liquid\Utils\Registers;


class Cycle extends \Liquid\Tag
{
    protected static $SimpleSyntax;
    protected static $NamedSyntax;

    protected $variables;
    protected $name;

    public static function init()
    {
        static::$SimpleSyntax = '/\A' . Liquid::$PART_QuotedFragment . '+/';
        static::$NamedSyntax = '/\A(' . Liquid::$PART_QuotedFragment . ')\s*\:\s*(.*)/s';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array $options
     * @throws SyntaxError
     */
    public function __construct($tag_name, $markup, $options)
    {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        switch (true) {
            case preg_match(static::$NamedSyntax, $markup, $matches):
                $this->variables = $this->variables_from_string($markup);
                $this->name = "'{$this->variables}'";
                break;
            case preg_match(static::$SimpleSyntax, $markup, $matches):
                $this->variables = $this->variables_from_string($matches[2]);
                $this->name = $matches[1];
                break;

            default:
                throw new SyntaxError("Syntax Error in 'cycle' - Valid syntax: cycle [name :] var [, var2, var3 ...]");
        }
    }

    public function render(&$context)
    {
        $registers = $context->registers();

        $name =& $this->name;
        $variables =& $this->variables;
        $result = null;

        $context->stack(function ($context) use ($registers, &$name, &$variables, &$result) {
            $key = $context[$name];

            $iteration = $registers['cycle'][$key];

            $result = $context[$variables[$iteration]];
            $iteration++;
            if ($iteration >= count($variables)) {
                $iteration = 0;
            }

            $registers['cycle'][$key] = $iteration;
        });

        return $result;
    }

    /**
     * @param $markup
     *
     * @return array
     */
    private function variables_from_string(&$markup)
    {
        $variables = explode(',', $markup);

        foreach ($variables as &$var) {
            $matches = null;
            preg_match('/\s*(' . Liquid::$PART_QuotedFragment . ')\s*/', $var, $matches);

            $var = isset($matches[1]) && $matches[1] ? $matches[1] : null;
        }

        return array_filter($variables);
    }
}

Cycle::init();
