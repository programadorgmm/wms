<?php

namespace Natue\Bundle\CoreBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AbstractBundle
 * @package Natue\Bundle\CoreBundle
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
abstract class AbstractBundle extends Bundle
{
    /**
     * @param array $classes Associative array of custom type classes and its type names
     * @return void
     */
    protected function loadCustomTypes(array $classes)
    {
        foreach ($classes as $class => $type) {
            $this->loadCustomType($class, $type);
        }
    }

    /**
     * @param string $class Fully qualified class name
     * @param string $type Type name
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function loadCustomType($class, $type)
    {
        if (! Type::hasType($type)) {
            Type::addType($type, $class);
        }
    }
}
