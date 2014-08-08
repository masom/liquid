<?php

namespace Liquid\Tests\Unit;

use \Liquid\Lexer;
use \Liquid\Parser;

class ParserTest extends \Liquid\Tests\TestCase
{

    public function test_consume()
    {
        $p = new Parser('wat: 7');
        $this->assertEquals('wat', $p->consume(Lexer::TOKEN_ID));
        $this->assertEquals(':', $p->consume(Lexer::TOKEN_COLON));
        $this->assertEquals('7', $p->consume(Lexer::TOKEN_NUMBER));
    }

    public function test_jump()
    {
        $p = new Parser('wat: 7');

        $p->jump(2);
        $this->assertEquals('7', $p->consume(Lexer::TOKEN_NUMBER));
    }

    public function test_try_consume()
    {
        $p = new Parser('wat: 7');
        $this->assertEquals('wat', $p->try_consume(Lexer::TOKEN_ID));
        $this->assertFalse($p->try_consume(Lexer::TOKEN_DOT));
        $this->assertEquals(':', $p->consume(Lexer::TOKEN_COLON));
        $this->assertEquals('7', $p->try_consume(Lexer::TOKEN_NUMBER));
    }

    public function test_try_id()
    {
        $p = new Parser('wat 6 Peter Hegemon');

        $this->assertEquals('wat', $p->try_id('wat'));
        $this->assertFalse($p->try_id('endgame'));
        $this->assertEquals('6', $p->consume(Lexer::TOKEN_NUMBER));
        $this->assertEquals('Peter', $p->try_id('Peter'));
        $this->assertFalse($p->try_id('Achilles'));
    }

    public function test_look()
    {
        $p = new Parser('wat 6 Peter Hegemon');

        $this->assertTrue($p->look(Lexer::TOKEN_ID));
        $this->assertEquals('wat', $p->consume(Lexer::TOKEN_ID));
        $this->assertFalse($p->look(Lexer::TOKEN_COMPARISON));
        $this->assertTrue($p->look(Lexer::TOKEN_NUMBER));
        $this->assertTrue($p->look(Lexer::TOKEN_ID, 1));
        $this->assertFalse($p->look(Lexer::TOKEN_NUMBER, 1));
    }

    public function test_expressions()
    {
        $p = new Parser('hi.there hi[5].! hi.there.bob');

        $this->assertEquals('hi.there', $p->expression());
        $this->assertEquals('hi[5].!', $p->expression());
        $this->assertEquals('hi.there.bob', $p->expression());

        $p = new Parser("567 6.0 'lol' \"wut\"");
        $this->assertEquals('567', $p->expression());
        $this->assertEquals('6.0', $p->expression());
        $this->assertEquals("'lol'", $p->expression());
        $this->assertEquals('"wut"', $p->expression());
    }

    public function test_ranges()
    {
        $p = new Parser('(5..7) (1.5..9.6) (young..old) (hi[5].wat..old)');
        $this->assertEquals('(5..7)', $p->expression());
        $this->assertEquals('(1.5..9.6)', $p->expression());
        $this->assertEquals('(young..old)', $p->expression());
        $this->assertEquals('(hi[5].wat..old)', $p->expression());
    }

    public function test_arguments()
    {
        $p = new Parser('filter: hi.there[5], keyarg: 7');

        $this->assertEquals('filter', $p->consume(Lexer::TOKEN_ID));
        $this->assertEquals(':', $p->consume(Lexer::TOKEN_COLON));
        $this->assertEquals('hi.there[5]', $p->argument());
        $this->assertEquals(',', $p->consume(Lexer::TOKEN_COMMA));
        $this->assertEquals('keyarg: 7', $p->argument());
    }

    public function test_invalid_expression()
    {
        try {
            $p = new Parser('==');
            $p->expression();
            $this->fail('A SyntaxError should have been raised.');
        } catch (\Liquid\Exceptions\SyntaxError $e) {
        }
    }
}
