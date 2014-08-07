<?php

namespace Liquid;

use \Liquid\Utils\Nodes;

class Tag
{

    protected $to;
    protected $from;

    protected $tag_name;
    protected $markup;
    protected $options;

    /** @var Nodes */
    protected $nodelist;

    /** @var array */
    protected $warnings;

    /** @var boolean */
    protected $blank;

    public static function __callStatic($method, $args)
    {
        if ($method == 'parse') {
            $tag = new static($args[0], $args[1], $args[3]);
            $tag->parse($args[2]);
            return $tag;
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "::{$method}` is undefined.");
    }

    public function __call($method, $arguments)
    {
        if ($method == 'parse') {
            return null;
        }

        throw new \BadMethodCallException("Method `" . __CLASS__ . "->{$method}` is undefined.");
    }

    /**
     * @param string $tag_name
     * @param string $markup
     * @param array $options
     */
    public function __construct($tag_name, $markup, array $options)
    {
        $this->tag_name = $tag_name;
        $this->markup = $markup;
        $this->options = $options + array('error_mode' => Template::error_mode());
    }

    /**
     * @return array
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * @return Nodes
     */
    public function nodelist()
    {
        return $this->nodelist;
    }

    /**
     * @return array
     */
    public function warnings()
    {
        return $this->warnings;
    }

    /**
     * @return string
     */
    public function name()
    {
        $lastNsPos = strrpos(__CLASS__, '\\');
        $namespace = substr(__CLASS__, 0, $lastNsPos);

        return 'liquid::' . strtolower(substr(__CLASS__, $lastNsPos + 1));
    }

    /**
     * @param Context $context
     *
     * @return null
     */
    public function render(&$context)
    {
        return null;
    }

    /**
     * @return bool
     */
    public function is_blank()
    {
        return false;
    }

    /**
     * @param string $markup
     *
     * @return mixed
     */
    public function parse_with_selected_parser(&$markup)
    {
        switch ($this->options['error_mode']) {
            case Liquid::ERROR_MODE_STRICT:
                return $this->strict_parse_with_error_context($markup);
            case Liquid::ERROR_MODE_LAX:
                return $this->lax_parse($markup);
            case Liquid::ERROR_MODE_WARN:
                try {
                    return $this->strict_parse_with_error_context($markup);
                } catch (\Liquid\Exceptions\SyntaxError $e) {
                    $this->warnings[] = $e;
                    return $this->lax_parse($markup);
                }
                break;
        }
    }

    /**
     * @param $markup
     *
     * @return mixed
     * @throws \Exception
     * @throws Exceptions\SyntaxError
     */
    private function strict_parse_with_error_context(&$markup)
    {
        try {
            return $this->strict_parse($markup);
        } catch (\Liquid\Exceptions\SyntaxError $e) {
            $e->setMessage($e->getMessage() . ' in "' . trim($markup) . '"');
            throw $e;
        }
    }
}
