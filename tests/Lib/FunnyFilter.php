<?php


namespace Liquid\Tests\Lib;


class FunnyFilter {
    public function make_funny($input){
        return 'LOL';
    }

    public function cite_funny($input) {
        return "LOL: {$input}";
    }

    public function add_smiley($input, $smiley = ":-)") {
        return "{$input} {$smiley}";
    }

    public function add_tag($input, $tag = "p", $id = "foo") {
        return "<{$tag} id=\"{$id}\">{$input}</{$tag}>";
    }

    public function paragraph($input) {
        return "<p>{$input}</p>";
    }

    public function link_to($name, $url) {
        return "<a href=\"{$url}\">{$name}</a>";
    }
}
