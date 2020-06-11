<?php

namespace Natue\Bundle\CoreBundle\Twig;

class UcWordsExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ucwords', 'ucwords'),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ucwords', 'ucwords'),
        );
    }

    public function getName()
    {
        return 'ucwords';
    }
}