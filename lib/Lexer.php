<?php

namespace Liquid;

use \Liquid\Vendor\StringScanner\StringScanner;

class Lexer {

    const TOKEN_COMPARISON = 'comparison';
    const TOKEN_STRING = 'string';
    const TOKEN_NUMBER = 'number';
    const TOKEN_ID = 'id';
    const TOKEN_DOTDOT = 'dotdot';
    const TOKEN_DOT = 'dot';
    const TOKEN_COLON = 'colon';
    const TOKEN_COMMA = 'comma';
    const TOKEN_ENDOFSTRING = 'end_of_string';
    const TOKEN_PIPE = 'pipe';
    const TOKEN_OPENSQUARE = 'open_square';
    const TOKEN_CLOSESQUARE = 'close_square';
    const TOKEN_OPENROUND = 'open_round';
    const TOKEN_CLOSEROUND = 'close_round';

    const IDENTIFIER = '/[\w\-?!]+/';
    const SINGLE_STRING_LITERAL = '/\'[^\\\']*\'/';
    const DOUBLE_STRING_LITERAL = '/"[^\"]*"/';
    const NUMBER_LITERAL = '/-?\d+(\.\d+)?/';
    const DOTDOT = '/\.\./';
    const COMPARISON_OPERATOR = '/==|!=|<>|<=?|>=?|contains/';

    protected static $SPECIALS = array(
        '|' => Lexer::TOKEN_PIPE,
        '.' => Lexer::TOKEN_DOT,
        ':' => Lexer::TOKEN_COLON,
        ',' => Lexer::TOKEN_COMMA,
        '[' => Lexer::TOKEN_OPENSQUARE,
        ']' => Lexer::TOKEN_CLOSESQUARE,
        '(' => Lexer::TOKEN_OPENROUND,
        ')' => Lexer::TOKEN_CLOSEROUND
    );

    /** @var StringScanner */
    protected $ss;

    /** @var array */
    protected $output;

    /**
     * @var string $input
     */
    public function __construct($input) {
        $this->ss = new StringScanner(rtrim($input));
    }

    /**
     * @return array
     * @throws Exceptions\SyntaxError
     */
    public function tokenize() {
        $this->output = array();

        while(!$this->ss->eos) {
            $this->ss->skip('/\s*/');
            $tok = null;
            switch(true) {
                case $t = $this->ss->scan(static::COMPARISON_OPERATOR):
                    $tok = array(static::TOKEN_COMPARISON, $t);
                    break;
                case $t = $this->ss->scan(static::SINGLE_STRING_LITERAL):
                    $tok = array(static::TOKEN_STRING, $t);
                    break;
                case $t = $this->ss->scan(static::DOUBLE_STRING_LITERAL):
                    $tok = array(static::TOKEN_STRING, $t);
                    break;
                case $t = $this->ss->scan(static::NUMBER_LITERAL):
                    $tok = array(static::TOKEN_NUMBER, $t);
                    break;
                case $t = $this->ss->scan(static::IDENTIFIER):
                    $tok = array(static::TOKEN_ID, $t);
                    break;
                case $t = $this->ss->scan(static::DOTDOT):
                    $tok = array(static::TOKEN_DOTDOT, $t);
                    break;
                default:
                    $c = $this->ss->getch();
                    if (isset(static::$SPECIALS[$c])) {
                        $tok = array(static::$SPECIALS[$c], $c);
                    } else {
                        throw new \Liquid\Exceptions\SyntaxError("Unexpected character `{$c}`");
                    }
                    break;
            }
            $this->output[] = $tok;
        }

        $this->output[] = array(static::TOKEN_ENDOFSTRING);

        return $this->output;
    }


}
