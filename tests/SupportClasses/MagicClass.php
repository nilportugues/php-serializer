<?php

namespace NilPortugues\Test\Serializer\SupportClasses;

class MagicClass
{
    /**
     * @var bool
     */
    public $show = true;
    /**
     * @var bool
     */
    public $hide = true;
    /**
     * @var bool
     */
    public $woke = false;

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['show'];
    }

    /**
     *
     */
    public function __wakeup()
    {
        $this->woke = true;
    }
}
