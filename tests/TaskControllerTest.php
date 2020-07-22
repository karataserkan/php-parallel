<?php

declare (strict_types = 1);

namespace app\tests;

use PHPUnit\Framework\TestCase;
use app\controllers\TaskController;

final class TaskControllerTest extends TestCase
{
    public function testCanGetFile()
    {
        $controller = new TaskController();
        $params = ['index.php', 'task/test'];
        $this->expectOutputString('test');
        $controller->test($params);
    }
}
