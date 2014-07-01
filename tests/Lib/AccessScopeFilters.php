<?php

namespace Liquid\Tests\Lib;

class AccessScopeFilters {
    public function strict_argument_filter(array $arg) {
        return 'strict';
    }

    public function public_filter() {
        return 'public';
    }

    private function private_filter() {
        return 'private';
    }
}
