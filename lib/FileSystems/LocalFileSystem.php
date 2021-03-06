<?php

namespace Liquid\FileSystems;

class LocalFileSystem {

    /** @var string */
    protected $root;
    /** @var string pattern */
    protected $pattern;

    const ALLOWED_PATH_PATTERN = '/\A[^.\/][a-zA-Z0-9_\/]+\z/';

    /**
     * @var string $root
     * @var string $pattern
     */
    public function __construct($root, $pattern = '_%s.liquid') {
        $this->root = $root;
        $this->pattern = $pattern;
    }

    /**
     * @param $template_path
     * @param $context
     *
     * @return string
     * @throws \Liquid\Exceptions\FileSystemError
     */
    public function read_template_file($template_path, $context) {
        $full_path = $this->full_path($template_path);

        if (!file_exists($full_path)) {
            throw new \Liquid\Exceptions\FileSystemError("No such template `{$template_path}`");
        }

        return file_get_contents($full_path);
    }

    /**
     * @param $template_path
     *
     * @return string
     * @throws \Liquid\Exceptions\FileSystemError
     */
    public function full_path($template_path) {
        if (!preg_match(static::ALLOWED_PATH_PATTERN, $template_path)) {
            throw new \Liquid\Exceptions\FileSystemError("Illegal template name `{$template_path}`");
        }


        if (strpos($template_path, '/') !== false) {
            $full_path = implode('/', array($this->root, dirname($template_path), sprintf($this->pattern, basename($template_path))));
        } else {
            $full_path = implode('/', array($this->root, sprintf($this->pattern, $template_path)));
        }

        if (!preg_match( '/\A' . realpath($this->root) .'/', realpath($full_path))) {
            throw new \Liquid\Exceptions\FileSystemError("Illegal template path `{$template_path}`");
        }

        return $full_path;
    }
}
