<?php

namespace Natue\Bundle\StockBundle\Tests\StateMachine;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * StateMachine document test
 */
class StockItemTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $stateMachine = $this->getMockBuilder('Finite\StateMachine\StateMachine')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->getMockBuilder('Natue\Bundle\StockBundle\StateMachine\StockItem')
            ->setConstructorArgs([$stateMachine, $entityManager])
            ->getMock();
    }
}
