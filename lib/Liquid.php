<?php

namespace Liquid;

/**
 * Liquid 3.0
 */
class Liquid {

    /** @var array */
    protected $config;

    const FilterSeparator             = '\|';
    const ArgumentSeparator           = ',';
    const FilterArgumentSeparator     = ':';
    const VariableAttributeSeparator  = '.';
    const TagStart                    = '\{\%';
    const TagEnd                      = '\%\}';
    const VariableSignature           = '\(?[\w\-\.\[\]]\)?';
    const VariableSegment             = '[\w\-]';
    const VariableStart               = '\{\{';
    const VariableEnd                 = '\}\}';
    const VariableIncompleteEnd       = '\}\}?';
    const QuotedString                = '"[^"]*"|\'[^\']*\'';

    public static $QuotedFragment;
    public static $TagAttributes;
    public static $AnyStartingTag;
    public static $PartialTemplateParser;
    public static $TemplateParser;
    public static $VariableParser;

    public static function init()
    {
        /**
         * PHP does not support evaluating statements when setting constants / class variables.
         */


        $partialTemplateParser               = static::TagStart . '.*?' . static::TagEnd . '|'. static::VariableStart . '.*?' . static::VariableIncompleteEnd;
        $anyStartingTag                      = '\{\{|\{\%';
        $quotedFragment                      = static::QuotedString . '|(?:[^\s,\|\'"]|' . static::QuotedString . ')+';

        static::$QuotedFragment              = '/' . $quotedFragment . '/';
        static::$TagAttributes               = '/(\w+)\s*\:\s*(' . $quotedFragment . ')/';
        static::$AnyStartingTag              = '/'. $anyStartingTag . '/';
        static::$PartialTemplateParser       = '/' . $partialTemplateParser . '/m';
        static::$TemplateParser              = '/(' . $partialTemplateParser . '|' . $anyStartingTag . ')/m';

        static::$VariableParser              = '/\[[^\]]+\]|' . static::VariableSegment . '+\??/';
    }
}

Liquid::init();
