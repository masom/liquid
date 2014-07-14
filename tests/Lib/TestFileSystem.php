<?php


namespace Liquid\Tests\Lib;


class TestFileSystem {
    public function read_template_file($template_path, $context) {
        switch ($template_path) {
            case "product":
                return "Product: {{ product.title }} ";

            case "locale_variables":
                return "Locale: {{echo1}} {{echo2}}";

            case "variant":
                return "Variant: {{ variant.title }}";

            case "nested_template":
                return "{% include 'header' %} {% include 'body' %} {% include 'footer' %}";

            case "body":
                return "body {% include 'body_detail' %}";

            case "nested_product_template":
                return "Product: {{ nested_product_template.title }} {%include 'details'%} ";

            case "recursively_nested_template":
                return "-{% include 'recursively_nested_template' %}";

            case "pick_a_source":
                return "from TestFileSystem";

            default:
                return $template_path;
        }
    }
}
