<?php

namespace Liquid;

use \Liquid\Template;

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
    const QuotedString                = '(?:"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')';


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
        $partialTemplateParser = static::TagStart . '(?:' . static::QuotedString . '|.*?)*' . static::TagEnd . '|' . static::VariableStart . '(?:' . static::QuotedString . '|.*?)*' . static::VariableEnd;
        $anyStartingTag                      = '\{\{|\{\%';

        static::$PART_QuotedFragment         = static::QuotedString . '|(?:[^\s,\|\'"]|' . static::QuotedString . ')+';

        static::$QuotedFragment              = '/' . static::$PART_QuotedFragment . '/S';
        static::$TagAttributes               = '/(\w+)\s*\:\s*(' . static::$PART_QuotedFragment . ')/S';
        static::$AnyStartingTag              = '/'. $anyStartingTag . '/S';
        static::$PartialTemplateParser       = '/' . $partialTemplateParser . '/Sm';
        static::$TemplateParser              = '/(' . $partialTemplateParser . '|' . $anyStartingTag . ')/Sm';

        static::$VariableParser              = '/\[[^\]]+\]|' . static::VariableSegment . '+\??/S';


        /**
         * Register the standard tags.
         */
        Template::register_tag('assign', '\Liquid\Tags\Assign');
        Template::register_tag('break', '\Liquid\Tags\BreakTag');
        Template::register_tag('capture', '\Liquid\Tags\Capture');
        Template::register_tag('case', '\Liquid\Tags\CaseTag');
        Template::register_tag('comment', '\Liquid\Tags\Comment');
        Template::register_tag('continue', '\Liquid\Tags\ContinueTag');
        Template::register_tag('cycle', '\Liquid\Tags\Cycle');
        Template::register_tag('decrement', '\Liquid\Tags\Decrement');
        Template::register_tag('for', '\Liquid\Tags\ForTag');
        Template::register_tag('ifchanged', '\Liquid\Tags\IfChanged');
        Template::register_tag('if', '\Liquid\Tags\IfTag');
        Template::register_tag('include', '\Liquid\Tags\IncludeTag');
        Template::register_tag('increment', '\Liquid\Tags\Increment');
        Template::register_tag('raw', '\Liquid\Tags\Raw');
        Template::register_tag('tablerow', '\Liquid\Tags\TableRow');
        Template::register_tag('unless', '\Liquid\Tags\Unless');
    }
}

Liquid::init();
