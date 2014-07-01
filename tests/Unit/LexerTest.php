<?php

namespace Liquid\Tests\Unit;

use \Liquid\Lexer;


class LexerText extends \Liquid\Tests\TestCase {

    public function test_strings() {
        $lexer = new Lexer(' \'this is a test""\' "wat \'lol\'"');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_STRING, '\'this is a test""\''),
            array(Lexer::TOKEN_STRING, "\"wat 'lol'\""),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_integer() {
        $lexer = new Lexer('hi 50');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_ID, 'hi'),
            array(Lexer::TOKEN_NUMBER, '50'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_float() {
        $lexer = new Lexer('hi 5.0');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_ID, 'hi'),
            array(Lexer::TOKEN_NUMBER, '5.0'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_comparison() {
        $lexer = new Lexer('== <> contains');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_COMPARISON, '=='),
            array(Lexer::TOKEN_COMPARISON, '<>'),
            array(Lexer::TOKEN_COMPARISON, 'contains'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
    }

    public function test_specials() {
        $lexer = new Lexer('| .:');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_PIPE, '|'),
            array(Lexer::TOKEN_DOT, '.'),
            array(Lexer::TOKEN_COLON, ':'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);

        $lexer = new Lexer('[,]');
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_OPENSQUARE, '['),
            array(Lexer::TOKEN_COMMA, ','),
            array(Lexer::TOKEN_CLOSESQUARE, ']'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_fancy_identifiers() {
        $lexer = new Lexer('hi! five?');
        $tokens = $lexer->tokenize();

        $expected = array(
            array(Lexer::TOKEN_ID, 'hi!'),
            array(Lexer::TOKEN_ID, 'five?'),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_whitespace() {
        $lexer = new Lexer("five|\n\t ==");
        $tokens = $lexer->tokenize();
        $expected = array(
            array(Lexer::TOKEN_ID, 'five'),
            array(Lexer::TOKEN_PIPE, '|'),
            array(Lexer::TOKEN_COMPARISON, '=='),
            array(Lexer::TOKEN_ENDOFSTRING)
        );
        $this->assertEquals($expected, $tokens);
    }

    public function test_unexpected_character() {
        try {
            $lexer = new Lexer('%');
            $lexer->tokenize();
            $this->fail('A SyntaxError should have been raised.');
        } catch(\Liquid\Exceptions\SyntaxError $e) {
        }
    }
}
