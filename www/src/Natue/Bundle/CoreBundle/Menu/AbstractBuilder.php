<?php

namespace Natue\Bundle\CoreBundle\Menu;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Extend builder
 */
abstract class AbstractBuilder extends ContainerAware
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    protected $securityContext;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    protected $translate;

    /** @var \Symfony\Component\HttpFoundation\Request  */
    protected $request;

    /**
     * @var Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->securityContext = $this->container->get('security.context');
        $this->translate       = $this->container->get('translator');
        $this->request         = $this->container->get('request');
    }

    /**
     * Build a menu from a arra
     *
     * @param FactoryInterface $factory
     * @param array            $options
     * @param array            $menuTree   Menu array
     * @param string           $class      CSS class
     * @param bool             $checkToken Check if user is logged in
     *
     * @return mixed
     */
    public function buildFromArray(
        FactoryInterface $factory,
        $options,
        $menuTree,
        $class = 'nav navbar-nav',
        $checkToken = true
    ) {
        $menu = $factory->createItem('root');
        $menu->setCurrent($this->request->getRequestUri());
        $menu->setChildrenAttribute('class', $class);

        if ($checkToken && !$this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $menu;
        }

        $menuTree = $this->checkPremissions($menuTree);

        if (count($menuTree) <= 0) {
            return $menu;
        }

        return $this->processArrayMenu($menuTree, $menu);
    }

    /**
     * Check permissions per item
     *
     * @param array $menuTree Menu array
     *
     * @return mixed
     */
    protected function checkPremissions($menuTree)
    {
        foreach ($menuTree as $menuLabel => $menuItem) {

            if (isset($menuItem['child']) && is_array($menuItem['child'])) {
                $return                        = $this->checkPremissions($menuItem['child']);
                $menuTree[$menuLabel]['child'] = $return;

                if (count($menuTree[$menuLabel]['child']) <= 0) {
                    unset($menuTree[$menuLabel]);
                }
            } else {
                if (isset($menuItem['roles'])) {
                    if (!$this->securityContext->isGranted($menuItem['roles'])) {
                        unset($menuTree[$menuLabel]);
                    }
                }
            }
        }

        return $menuTree;
    }

    /**
     * Process the array to create the menu
     *
     * @param array  $menuTree Menu array
     * @param object $menu     Menu object
     *
     * @return mixed
     */
    protected function processArrayMenu($menuTree, $menu)
    {
        foreach ($menuTree as $menuLabel => $menuItem) {

            if (isset($menuItem['dropdown-header'])) {
                $this->addDivider($menu);
                $this->addDropdownHeader($menuLabel, $menu);
                $this->processArrayMenu($menuItem['child'], $menu);
            } else {
                if (isset($menuItem['child']) && is_array($menuItem['child'])) {

                    $dropdown = $menu->addChild(
                        $this->translate->trans($menuLabel),
                        [
                            'dropdown' => true,
                            'caret'    => true,
                            'icon'     => (isset($menuItem['icon']) ? $menuItem['icon'] : null)
                        ]
                    );

                    $this->processArrayMenu($menuItem['child'], $dropdown);
                } else {

                    $menu->addChild(
                        $this->translate->trans($menuLabel),
                        ['route' => $menuItem['route']]
                    );
                }
            }
        }

        return $menu;
    }

    /**
     * add a divider to the dropdown Menu
     *
     * @param ItemInterface $dropdown The dropdown Menu
     * @param bool          $vertical Whether to add a vertical or horizontal divider.
     *
     * @return ItemInterface
     */
    protected function addDivider(ItemInterface $dropdown, $vertical = false)
    {
        $class = $vertical ? 'divider-vertical' : 'divider';

        return $dropdown->addChild('divider_' . rand())
            ->setLabel('')
            ->setAttribute('class', $class);
    }

    /**
     * Add a nav header to menu
     *
     * @param string $label Label of header
     * @param object $menu  Menu object
     *
     * @return mixed
     */
    protected function addDropdownHeader($label, $menu)
    {
        return $menu->addChild($this->translate->trans($label))
            ->setAttribute('class', 'dropdown-header');
    }
}
