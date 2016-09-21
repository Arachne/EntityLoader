<?php

namespace Tests\Unit\Classes;

use Exception;
use Nette\Application\UI\PresenterComponent;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class TestComponent extends PresenterComponent
{
    /**
     * @persistent
     *
     * @var Class5
     */
    public $persistent;

    final public function __construct()
    {
        throw new Exception('This class is there for annotations only.');
    }

    public function handleTestHandle(Class6 $handleEntity)
    {
    }

    public function createComponentSub() : TestComponent
    {
    }
}
