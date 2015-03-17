<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Fieldset
 */
class Fieldset extends AbstractComponent
{
    const UI_ELEMENT_FIELDSET = 'fieldset';

    const NAME = 'date';

    /**
     * @var bool
     */
    protected $collapsible = false;

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function getLegendText()
    {
        return $this->getData('config/label');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCollapsible()
    {
        return $this->getData('config/collapsible', $this->collapsible);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getData('config/source');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getData('config/content');
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->getData('children');
    }
}
