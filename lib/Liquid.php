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


    const ERROR_MODE_LAX = 'lax';
    const ERROR_MODE_WARN = 'warn';
    const ERROR_MODE_STRICT = 'strict';

    public static $QuotedFragment;
    public static $TagAttributes;
    public static $AnyStartingTag;
    public static $PartialTemplateParser;
    public static $TemplateParser;
    public static $VariableParser;
    public static $PART_QuotedFragment;

    public static function init()
    {
        /**
         * PHP does not support evaluating statements when setting constants / class variables.
         */


        $partialTemplateParser               = static::TagStart . '.*?' . static::TagEnd . '|'. static::VariableStart . '.*?' . static::VariableIncompleteEnd;
        $anyStartingTag                      = '\{\{|\{\%';

        static::$PART_QuotedFragment         = static::QuotedString . '|(?:[^\s,\|\'"]|' . static::QuotedString . ')+';

        static::$QuotedFragment              = '/' . static::$PART_QuotedFragment . '/S';
        static::$TagAttributes               = '/(\w+)\s*\:\s*(' . static::$PART_QuotedFragment . ')/S';
        static::$AnyStartingTag              = '/'. $anyStartingTag . '/S';
        static::$PartialTemplateParser       = '/' . $partialTemplateParser . '/Sm';
        static::$TemplateParser              = '/(' . $partialTemplateParser . '|' . $anyStartingTag . ')/Sm';

        static::$VariableParser              = '/\[[^\]]+\]|' . static::VariableSegment . '+\??/S';
    }
}

Liquid::init();
