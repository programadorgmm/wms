<?php

namespace Natue\Bundle\CoreBundle;


use Symfony\Component\EventDispatcher\Event;

class AppEvent extends Event
{

    /**
     * @var array
     */
    private $businessData;

    /**
     * AppEvent constructor.
     * @param array $businessData
     */
    public function __construct(array $businessData)
    {
        $this->businessData = $businessData;
    }

    /**
     * @return array
     */
    public function getBusinessData()
    {
        return $this->businessData;
    }

}