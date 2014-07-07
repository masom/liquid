<?php
namespace Liquid\Tests\Integration;

use \Liquid\Template;

class VariableTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_simple_variable() {
        $template = Template::parse('{{test}}');
        $this->assertEquals('worked', $template->render(array('test'=>'worked')));
        $this->assertEquals('worked wonderfully', $template->render(array('test'=>'worked wonderfully')));
    }

    public function test_simple_with_whitespaces() {
        $template = Template::parse('  {{test}}  ');
        $this->assertEquals('  worked  ', $template->render(array('test'=>'worked')));
        $this->assertEquals('  worked wonderfully  ', $template->render(array('test'=>'worked wonderfully')));
    }

    public function test_ignore_unknown() {
        $template = Template::parse('{{ test }}');
        $this->assertEquals('', $template->render());
    }

    public function test_hash_scoping() {
        $template = Template::parse('{{ test.test }}');
        $this->assertEquals('worked', $template->render(array('test' => array('test' => 'worked'))));
    }

    public function test_false_renders_as_false() {
        $this->assertEquals('false', Template::parse('{{ foo }}')->render(array('foo' => false)));
    }
}
