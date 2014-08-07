<?php

namespace Liquid;

use \Liquid\Lexer;

class Parser
{

    /** @var array */
    protected $tokens;
    /** @var integer */
    protected $p = 0;

    /**
     * @param string $input
     */
    public function __construct($input)
    {
        $l = new Lexer($input);
        $this->tokens = $l->tokenize();
    }

    /**
     * @param integer $point
     */
    public function jump($point)
    {
        $this->p = $point;
    }

    /**
     * @param string $type
     *
     * @return mixed|null
     * @throws Exceptions\SyntaxError
     */
    public function consume($type = null)
    {
        if (!isset($this->tokens[$this->p])) {
            throw new \Liquid\Exceptions\SyntaxError("Expected `{$type}` but was out of bound");
        }

        $token = $this->tokens[$this->p];

        if ($type && $token[0] != $type) {
            $found = $this->tokens[$this->p][0];
            throw new \Liquid\Exceptions\SyntaxError("Expected `{$type}` but found `{$found}`");
        }

        $this->p++;

        return isset($token[1]) ? $token[1] : null;
    }

    /**
     * Only consumes the token if it matches the type.
     * Returns the token's contents if it was consumed
     * or false otherwise.
     *
     * Was consume?
     *
     * @param string $type
     * @return string
     */
    public function try_consume($type)
    {
        if (!isset($this->tokens[$this->p])) {
            return false;
        }

        $token = $this->tokens[$this->p];

        if ($token[0] !== $type) {
            return false;
        }

        $this->p++;

        return $token[1];
    }

    /**
     * Like try_consume except for an id token of a certain name
     */
    public function try_id($id)
    {
        $token = $this->tokens[$this->p];

        if (!isset($this->tokens[$this->p])) {
            throw new \Liquid\Exceptions\SyntaxError("Expected and id but was out of bound");
        }

        if ($token[0] !== Lexer::TOKEN_ID || $token[1] !== $id) {
            return false;
        }

        $this->p++;

        return $token[1];
    }

    /**
     * @param string $type
     * @param integer $ahead
     *
     * @return bool
     */
    public function look($type, $ahead = 0)
    {
        if (!isset($this->tokens[$this->p + $ahead])) {
            return false;
        }

        $tok = $this->tokens[$this->p + $ahead];
        return $tok[0] === $type;
    }

    public function expression()
    {
        $token = $this->tokens[$this->p];

        if ($token[0] === Lexer::TOKEN_ID) {
            return $this->variable_signature();
        } elseif ($token[0] == Lexer::TOKEN_STRING || $token[0] == Lexer::TOKEN_NUMBER) {
            return $this->consume();
        } elseif ($token[0] === Lexer::TOKEN_OPENROUND) {
            $this->consume();
            $first = $this->expression();
            $this->consume(Lexer::TOKEN_DOTDOT);
            $last = $this->expression();
            $this->consume(Lexer::TOKEN_CLOSEROUND);

            return "({$first}..{$last})";
        } else {
            $token = json_encode($token);
            throw new \Liquid\Exceptions\SyntaxError("`{$token}` is not a valid expression");
        }
    }

    public function argument()
    {
        $str = '';

        if ($this->look(Lexer::TOKEN_ID) && $this->look(Lexer::TOKEN_COLON, 1)) {
            $str .= $this->consume();
            $str .= $this->consume();
            $str .= ' ';
        }

        $str .= $this->expression();

        return $str;
    }

    public function variable_signature()
    {
        $str = $this->consume(Lexer::TOKEN_ID);

        if ($this->look(Lexer::TOKEN_OPENSQUARE)) {
            $str .= $this->consume();
            $str .= $this->expression();
            $str .= $this->consume(Lexer::TOKEN_CLOSESQUARE);
        }

        if ($this->look(Lexer::TOKEN_DOT)) {
            $str .= $this->consume();
            $str .= $this->variable_signature();
        }

        return $str;
    }
}
