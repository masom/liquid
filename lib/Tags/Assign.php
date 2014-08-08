<?php

namespace Liquid\Tags;

use \Liquid\Liquid;
use \Liquid\Variable;


class Assign extends \Liquid\Tag
{
    protected static $init = false;
    protected static $Syntax;

    public static function init()
    {
        static::$init = true;

        static::$Syntax = '/(' . Liquid::VariableSignature . ')\s*=\s*(.*)\s*/s';
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array $options
     *
     * @throws \Liquid\Exceptions\SyntaxError
     */
    public function __construct($tag_name, &$markup, $options)
    {
        parent::__construct($tag_name, $markup, $options);

        $matches = null;
        if (preg_match(static::$Syntax, $markup, $matches)) {
            $this->to = $matches[1];
            $this->from = new Variable($matches[2]);
        } else {
            throw new \Liquid\Exceptions\SyntaxError("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
        }
    }

    /**
     * @param \Liquid\Context $context
     *
     * @return null
     */
    public function render(&$context)
    {
        $val = $this->from->render($context);

        $context->scopes_last_set($this->to, $val);
        $context->increment_used_resources('assign_score_current', $val);

        return null;
    }

    /**
     * @return bool
     */
    public function is_blank()
    {
        return true;
    }
}

Assign::init();
