<?php


namespace Liquid\Tests\Integration;


use Liquid\Template;
use Liquid\Tests\Lib\FunnyFilter;


class OutputTest extends \Liquid\Tests\IntegrationTestCase {

    protected $assigns = array(
        'best_cars' => 'bmw',
        'car' => array('bmw' => 'good', 'gm' => 'bad')
    );

    public function test_variable() {
        $text = ' {{best_cars}} ';

        $expected = ' bmw ';
        $this->assertEquals($expected, Template::parse($text)->render($this->assigns));
    }

    public function test_variable_traversing() {
        $text = ' {{car.bmw}} {{car.gm}} {{car.bmw}} ';

        $expected = ' good bad good ';
        $this->assertEquals($expected, Template::parse($text)->render($this->assigns));
    }

    public function test_variable_piping() {
        $text = ' {{ car.gm | make_funny }} ';
        $expected = ' LOL ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_variable_piping_with_input() {
        $text = ' {{ car.gm | cite_funny }} ';
        $expected = ' LOL: bad ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_variable_piping_with_args() {
        $text = ' {{ car.gm | add_smiley : \':-(\' }} ';
        $expected = ' bad :-( ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_variable_piping_with_no_args() {
        $text = ' {{ car.gm | add_smiley }} ';
        $expected = ' bad :-) ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_multiple_variable_piping_with_args() {
        $text = ' {{ car.gm | add_smiley : \':-(\' | add_smiley : \':-(\'}} ';
        $expected = ' bad :-( :-( ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_variable_piping_with_multiple_args() {
        $text = ' {{ car.gm | add_tag : \'span\', \'bar\'}} ';
        $expected = ' <span id="bar">bad</span> ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_variable_piping_with_variable_args() {
        $text = ' {{ car.gm | add_tag : \'span\', car.bmw}} ';
        $expected = ' <span id="good">bad</span> ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_multiple_pipings() {
        $text = ' {{ best_cars | cite_funny | paragraph }} ';
        $expected = ' <p>LOL: bmw</p> ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }

    public function test_link_to() {
        $text = ' {{ \'Typo\' | link_to: \'http://typo.leetsoft.com\' }} ';
        $expected = ' <a href="http://typo.leetsoft.com">Typo</a> ';

        $this->assertEquals($expected, Template::parse($text)->render($this->assigns, array('filters' => array(new FunnyFilter()))));
    }
}
