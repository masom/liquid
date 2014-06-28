<?php

namespace Liquid\FileSystem;

class BlankFileSystem {

    public function read_template_file($template_path, $context) {
        throw new \Liquid\Exceptions\FileSystemError("This liquid context does not allow includes.");
    }

}
