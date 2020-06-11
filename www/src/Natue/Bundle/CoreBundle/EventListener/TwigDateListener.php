<?php

namespace Natue\Bundle\CoreBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Twig date listener
 */
class TwigDateListener
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $format;

    /**
     * @param \Twig_Environment $twig
     * @param string            $format
     */
    public function __construct(\Twig_Environment $twig, $format)
    {
        $this->twig   = $twig;
        $this->format = $format;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->twig->getExtension('core')->setDateFormat($this->format, '%d days');
    }
}
