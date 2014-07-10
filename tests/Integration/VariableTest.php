<?php
namespace Liquid\Tests\Integration;

use \Liquid\Template;

//TODO More tests have to be implemented.
//
class VariableTest extends \Liquid\Tests\IntegrationTestCase {

    public function test_simple_variable() {
        /** @var Template $template */
        $template = Template::parse('{{test}}');
        $this->assertEquals('worked', $template->render(array('test'=>'worked')));
        $this->assertEquals('worked wonderfully', $template->render(array('test'=>'worked wonderfully')));
    }

    public function test_simple_with_whitespaces() {
        /** @var Template $template */
        $template = Template::parse('  {{test}}  ');
        $this->assertEquals('  worked  ', $template->render(array('test'=>'worked')));
        $this->assertEquals('  worked wonderfully  ', $template->render(array('test'=>'worked wonderfully')));
    }

    public function test_ignore_unknown() {
        /** @var Template $template */
        $template = Template::parse('{{ test }}');
        $this->assertEquals('', $template->render());
    }

    public function test_hash_scoping() {
        /** @var Template $template */
        $template = Template::parse('{{ test.test }}');
        $this->assertEquals('worked', $template->render(array('test' => array('test' => 'worked'))));
    }

    public function test_false_renders_as_false() {
        $this->assertEquals('false', Template::parse('{{ foo }}')->render(array('foo' => false)));
    }

    public function test_preset_assigns() {
        /** @var Template $template */
        $template = Template::parse('{{ test }}');
        $assigns = $template->assigns();
        $assigns['test'] = 'worked';
        $this->assertEquals('worked', $template->render());
    }

    public function test_reuse_parsed_template() {
        /** @var Template $template */
        $template = Template::parse('{{ greeting }} {{ name }}');
        $assigns = $template->assigns();
        $assigns['greeting'] = 'Goodbye';

        $this->assertEquals(array('greeting' => 'Goodbye'), $template->assigns()->getArrayCopy());
        $this->assertEquals('Hello Tobi', $template->render(array('greeting' => 'Hello', 'name' => 'Tobi')));
        $this->assertEquals('Hello ', $template->render(array('greeting' => 'Hello', 'unknown' => 'Tobi')));
        $this->assertEquals('Hello Brian', $template->render(array('greeting' => 'Hello', 'name' => 'Brian')));
        $this->assertEquals('Goodbye Brian', $template->render(array('name' => 'Brian')));
    }

    public function test_assigns_not_polluted_from_template() {
        /** @var Template $template */
        $template = Template::parse("{{ test }}{% assign test = 'bar' %}{{ test }}");
        $assigns = $template->assigns();
        $assigns['test'] = 'baz';

        $this->assertEquals('bazbar', $template->render());
        $this->assertEquals('bazbar', $template->render());
        $this->assertEquals('foobar', $template->render(array('test' => 'foo')));
        $this->assertEquals('bazbar', $template->render());
    }
    /*
    public function test_hash_with_default_proc() {
        $template = Template::parse('Hello {{ test }}');
        assigns = Hash.new { |h,k| raise "Unknown variable '#{k}'" }
        assigns['test'] = 'Tobi'
        $this->assertEquals('Hello Tobi', $template->render(assigns)
        assigns.delete('test')
        e = assert_raises(RuntimeError) {
        $template->render(assigns)
    }
    $this->assertEquals("Unknown variable 'test'", e.message
  }
 */
    public function test_multiline_variable() {
        $this->assertEquals('worked', Template::parse("{{\ntest\n}}")->render(array('test' => 'worked')));
    }
}
