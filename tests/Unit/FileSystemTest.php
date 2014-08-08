<?php

namespace Liquid\Tests\Unit;

use \Liquid\FileSystems\BlankFileSystem;
use \Liquid\FileSystems\LocalFileSystem;

class FileSystemTest extends \Liquid\Tests\TestCase
{

    public function test_default()
    {
        $fs = new BlankFileSystem();
        try {
            $fs->read_template_file('dummy', array('dummy' => 'smarty'));
            $this->fail('A FileSystemError should have been raised.');
        } catch (\Liquid\Exceptions\FileSystemError $e) {
        }
    }

    public function test_local()
    {
        $file_system = new LocalFileSystem('/some/path');
        $this->assertEquals('/some/path/_mypartial.liquid', $file_system->full_path('mypartial'));
        $this->assertEquals('/some/path/dir/_mypartial.liquid', $file_system->full_path('dir/mypartial'));

        try {
            $file_system->full_path('../dir/mypartial');
            $this->fail('A FileSystemError should have been raised.');
        } catch (\Liquid\Exceptions\FileSystemError $e) {
        }

        try {
            $file_system->full_path('/dir/../../dir/mypartial');
            $this->fail('A FileSystemError should have been raised.');
        } catch (\Liquid\Exceptions\FileSystemError $e) {
        }

        try {
            $file_system->full_path('/etc/paswd');
            $this->fail('A FileSystemError should have been raised.');
        } catch (\Liquid\Exceptions\FileSystemError $e) {
        }
    }

    public function test_custom_template_filename_patterns()
    {
        $file_system = new LocalFileSystem('/some/path', '%s.html');
        $this->assertEquals('/some/path/mypartial.html', $file_system->full_path('mypartial'));
        $this->assertEquals('/some/path/dir/mypartial.html', $file_system->full_path('dir/mypartial'));
    }
}
