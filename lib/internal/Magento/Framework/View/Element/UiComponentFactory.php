<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Object;
use Magento\Framework\Exception;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;

/**
 * Class UiComponentFactory
 */
class UiComponentFactory extends Object
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * UI component manager
     *
     * @var ManagerInterface
     */
    protected $componentManager;

    /**
     * Argument interpreter
     *
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $componentManager
     * @param InterpreterInterface $argumentInterpreter
     * @param array $data
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $componentManager,
        InterpreterInterface $argumentInterpreter,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        $this->componentManager = $componentManager;
        $this->argumentInterpreter = $argumentInterpreter;
        parent::__construct($data);
    }

    /**
     * Create child components
     *
     * @param array $bundleComponents
     * @param ContextInterface $renderContext
     * @param string $identifier
     * @return UiComponentInterface
     */
    protected function createChildComponent(
        array $bundleComponents,
        ContextInterface $renderContext,
        $identifier
    ) {
        list($className, $arguments) = $this->argumentsResolver($identifier, $bundleComponents);
        $components = [];
        foreach ($bundleComponents['children'] as $childrenIdentifier => $childrenData) {
            $children = $this->createChildComponent(
                $childrenData,
                $renderContext,
                $childrenIdentifier
            );
            $components[$childrenIdentifier] = $children;
        }

        $arguments['components'] = $components;
        if (!isset($arguments['context'])) {
            $arguments['context'] = $renderContext;
        }

        return $this->objectManager->create($className, $arguments);
    }

    /**
     * Resolve arguments
     *
     * @param string $identifier
     * @param array $componentData
     * @return array
     */
    protected function argumentsResolver($identifier, array $componentData)
    {
        $attributes = $componentData[ManagerInterface::COMPONENT_ATTRIBUTES_KEY];
        $className = $attributes['class'];
        unset($attributes['class']);
        $arguments = [];
        foreach ($componentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY] as $name => $argument) {
            $arguments[$name] = $this->argumentInterpreter->evaluate($argument);
        }

        if (!isset($arguments['data'])) {
            $arguments['data'] = [];
        }

        $arguments['data'] = array_merge($arguments['data'], ['name' => $identifier], $attributes);
        return [$className, $arguments];
    }

    /**
     * Create component object
     *
     * @param string $identifier
     * @param string $name
     * @param array $arguments
     * @return UiComponentInterface
     * @throws Exception
     */
    public function create($identifier, $name = null, array $arguments = [])
    {
        if ($name === null) {
            $bundleComponents = $this->componentManager->prepareData($identifier)->getData($identifier);
            if (empty($bundleComponents)) {
                throw new Exception('You use an empty set.');
            }
            list($className, $componentArguments) = $this->argumentsResolver(
                $identifier,
                $bundleComponents[$identifier]
            );
            if (!isset($arguments['context'])) {
                throw new Exception('Context, is required argument.');
            }
            $componentContext = $arguments['context'];
            $components = [];
            foreach ($bundleComponents[$identifier]['children'] as $childrenIdentifier => $childrenData) {
                $children = $this->createChildComponent(
                    $childrenData,
                    $componentContext,
                    $childrenIdentifier
                );
                $components[$childrenIdentifier] = $children;
            }
            $componentArguments['components'] = $components;

            /** @var \Magento\Framework\View\Element\UiComponentInterface $component */
            $component = $this->objectManager->create($className, array_merge($componentArguments, $arguments));

            return $component;
        } else {
            $defaultData = $this->componentManager->createRawComponentData($name);
            list($className, $componentArguments) = $this->argumentsResolver($identifier, $defaultData);
            /** @var \Magento\Framework\View\Element\UiComponentInterface $component */
            $component = $this->objectManager->create($className, array_merge($componentArguments, $arguments));

            return $component;
        }
    }
}
