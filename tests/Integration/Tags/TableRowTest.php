<?php


namespace Liquid\Tests\Integration\Tags;


use Liquid\Tests\IntegrationTestCase;
use Liquid\Tests\Lib\ArrayDrop;


class TableRowTest extends IntegrationTestCase {
    public function test_table_row() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
            '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}',
            array('numbers' => array(1,2,3,4,5,6))
        );

        $this->assert_template_result(
            "<tr class=\"row1\">\n</tr>\n",
            '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}',
            array('numbers' => array())
        );
    }

    public function test_table_row_with_different_cols() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td><td class=\"col4\"> 4 </td><td class=\"col5\"> 5 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 6 </td></tr>\n",
            '{% tablerow n in numbers cols:5%} {{n}} {% endtablerow %}',
            array('numbers' => array(1,2,3,4,5,6))
        );
    }

    public function test_table_col_counter() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\">1</td><td class=\"col2\">2</td></tr>\n<tr class=\"row2\"><td class=\"col1\">1</td><td class=\"col2\">2</td></tr>\n<tr class=\"row3\"><td class=\"col1\">1</td><td class=\"col2\">2</td></tr>\n",
            '{% tablerow n in numbers cols:2%}{{tablerowloop.col}}{% endtablerow %}',
            array('numbers' => array(1,2,3,4,5,6))
        );
    }

    public function test_quoted_fragment() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
            "{% tablerow n in collections.frontpage cols:3%} {{n}} {% endtablerow %}",
            array('collections' => array('frontpage' => array(1,2,3,4,5,6)))
        );

        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
            "{% tablerow n in collections['frontpage'] cols:3%} {{n}} {% endtablerow %}",
            array('collections' => array('frontpage' => array(1,2,3,4,5,6)))
        );

      }

    public function test_enumerable_drop() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
            '{% tablerow n in numbers cols:3%} {{n}} {% endtablerow %}',
            array('numbers' => new ArrayDrop(array(1,2,3,4,5,6)))
        );
     }

    public function test_offset_and_limit() {
        $this->assert_template_result(
            "<tr class=\"row1\">\n<td class=\"col1\"> 1 </td><td class=\"col2\"> 2 </td><td class=\"col3\"> 3 </td></tr>\n<tr class=\"row2\"><td class=\"col1\"> 4 </td><td class=\"col2\"> 5 </td><td class=\"col3\"> 6 </td></tr>\n",
            '{% tablerow n in numbers cols:3 offset:1 limit:6%} {{n}} {% endtablerow %}',
            array('numbers' => array(0,1,2,3,4,5,6,7))
        );
    }
}
