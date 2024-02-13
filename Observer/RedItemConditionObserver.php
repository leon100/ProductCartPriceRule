<?php

declare(strict_types=1);

namespace Mageleon\ProductCartPriceRule\Observer;

use Mageleon\ProductCartPriceRule\Model\Rule\Condition\RedItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RedItemConditionObserver implements ObserverInterface
{
    /**
     * Add Condition label to Cart Price Rule
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): RedItemConditionObserver
    {
        $additional = $observer->getData('additional');
        $conditions = (array) $additional->getConditions();

        $conditions = array_merge_recursive($conditions, [
            $this->getRedItemCondition()
        ]);

        $additional->setConditions($conditions);
        return $this;
    }

    /**
     * Retrieve red item condition
     *
     * @return array
     */
    private function getRedItemCondition(): array
    {
        return [
            'label'=> __('The cart has an item with red color'),
            'value'=> RedItem::class
        ];
    }
}
