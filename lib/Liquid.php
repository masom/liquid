<?php

namespace Liquid;

/**
 * Liquid 3.0
 */
class Liquid {

    /** @var array */
    protected $config;

    const FilterSeparator             = '/\|/';
    const ArgumentSeparator           = ',';
    const FilterArgumentSeparator     = ':';
    const VariableAttributeSeparator  = '.';
    const TagStart                    = '/\{\%/';
    const TagEnd                      = '/\%\}/';
    const VariableSignature           = '/\(?[\w\-\.\[\]]\)?/';
    const VariableSegment             = '/[\w\-]/';
    const VariableStart               = '/\{\{/';
    const VariableEnd                 = '/\}\}/';
    const VariableIncompleteEnd       = '/\}\}?/';
    const QuotedString                = '/"[^"]*"|\'[^\']*\'/';

    public static $QuotedFragment;
    public static $TagAttributes;
    public static $AnyStartingTag;
    public static $PartialTemplateParser;
    public static $TemplateParser;
    public static $VariableParser;

    public function __construct()
    {
        /**
         * PHP does not support evaluating statements when setting constants / class variables.
         */
        static::$QuotedFragment              = '/' . static::QuotedString . '|(?:[^\s,\|\'"]|' . static::QuotedString . ')+/o';
        static::$TagAttributes               = '/(\w+)\s*\:\s*(' . static::QuotedFragment . ')/o';
        static::$AnyStartingTag              = '/\{\{|\{\%/';
        static::$PartialTemplateParser       = '/' . static::TagStart . '.*?' . static::TagEnd . '|'. static::VariableStart . '.*?' . static::VariableIncompleteEnd . '/om';
        static::$TemplateParser              = '/(' . static::PartialTemplateParser . '|' . static::AnyStartingTag . ')/om';
        static::$VariableParser              = '/\[[^\]]+\]|' . static::VariableSegment . '+\??/o';
    }
}
