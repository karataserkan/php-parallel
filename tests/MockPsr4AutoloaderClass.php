<?php

namespace app\tests;

use app\Psr4AutoloaderClass;

class MockPsr4AutoloaderClass extends Psr4AutoloaderClass
{
	protected $files = array();

    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    protected function requireFile($file)
    {
        return in_array($file, $this->files);
    }
}
