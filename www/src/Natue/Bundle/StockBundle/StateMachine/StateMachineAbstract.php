<?php

namespace Natue\Bundle\StockBundle\StateMachine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

use Finite\Exception\StateException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use Finite\Loader\ArrayLoader;

abstract class StateMachineAbstract
{
    const STATE_TYPE_INITIAL = 'initial';
    const STATE_TYPE_NORMAL  = 'normal';
    const STATE_TYPE_FINAL   = 'final';

    /**
     * @var StateMachine
     */
    protected $stateMachine;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param StateMachine  $stateMachine
     * @param EntityManager $entityManager
     */
    public function __construct(
        StateMachine $stateMachine,
        EntityManager $entityManager
    ) {
        $this->stateMachine  = $stateMachine;
        $this->entityManager = $entityManager;
    }

    /**
     * Apply transition on Entity
     *
     * @param string            $transitionName
     * @param StatefulInterface $entity
     *
     * @return void
     */
    public function applyTransitionOnEntity(
        $transitionName,
        StatefulInterface $entity
    ) {
        $this->stateMachine->setObject($entity);
        $this->getArrayLoader()->load($this->stateMachine);
        $this->stateMachine->initialize();
        $this->stateMachine->apply($transitionName);
    }

    /**
     * Apply transition on Collection
     *
     * @param string          $transitionName
     * @param ArrayCollection $collection
     *
     * @throws StateException
     * @return void
     */
    public function applyTransitionOnCollection(
        $transitionName,
        ArrayCollection $collection
    ) {
        $transition = $this->getTransitions()[$transitionName];
        $transition['to'] = $transitionName;

        foreach ($collection as $entity) {
            $currentState = $entity->getFiniteState();

            if (!in_array($currentState, $transition['from'])) {
                throw new StateException(sprintf(
                    'The "%s" transition can not be applied to the "%s" state. Item: "%s"',
                    $transition['to'],
                    $currentState,
                    $entity->getId()
                ));
            }

            $entity->setStatus($transition['to']);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * Build ArrayLoader based on
     * class, states and transitions config array
     *
     * @return ArrayLoader
     */
    private function getArrayLoader()
    {
        $arrayConfig = [
            'class'       => $this->getClassName(),
            'states'      => $this->getStates(),
            'transitions' => $this->getTransitions(),
        ];

        // fulfil default attributes
        foreach ($arrayConfig['states'] as $key => $state) {
            $arrayConfig['states'][$key]['properties'] = [];
        }

        foreach ($arrayConfig['transitions'] as $transition => $relations) {
            $arrayConfig['transitions'][$transition]['to'] = $transition;
        }

        return new ArrayLoader($arrayConfig);
    }

    /**
     * Get class name
     *
     * @return string
     */
    abstract protected function getClassName();

    /**
     * Get array with possible statues
     *
     * @return array
     */
    abstract protected function getStates();

    /**
     * Get array with possible transitions
     *
     * @return array
     */
    abstract protected function getTransitions();
}
