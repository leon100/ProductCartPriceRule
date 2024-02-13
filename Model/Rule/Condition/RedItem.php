<?php
declare(strict_types=1);

namespace Mageleon\ProductCartPriceRule\Model\Rule\Condition;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Psr\Log\LoggerInterface;

/**
 * @method setAttributeOption(array $array)
 */
class RedItem extends AbstractCondition
{
    private const FALSE = 'false';
    private const TRUE = 'true';
    private const CHECKED_ATTRIBUTE = 'color';
    private const CHECKED_VALUE = 'red';
    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions(): RedItem
    {
        $this->setAttributeOption([
            'red_item' => __('The cart has an item with red color')
        ]);
        return $this;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType(): string
    {
        return 'select';
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * Retrieve value select options
     *
     * @return array
     */
    public function getValueSelectOptions(): array
    {
        $select['red_item'] = [
            'value' => self::TRUE,
            'label' => 'True'
        ];

        return $select;
    }

    /**
     * Validate products in the cart
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model): bool
    {
        $result = self::FALSE;

        if (! $model instanceof AddressInterface) {
            $model->setData($this->getData('attribute'), $result);
            return parent::validate($model);
        }

        foreach ($model->getAllItems() as $quoteItem) {
            $product = $quoteItem->getProduct();
            if (!$product instanceof Product) {
                try {
                    $product = $this->productRepository->getById($quoteItem->getProductId());
                } catch (NoSuchEntityException $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            if (is_string($product->getAttributeText(self::CHECKED_ATTRIBUTE))) {
                $value = mb_strtolower($product->getAttributeText(self::CHECKED_ATTRIBUTE));
                if (self::CHECKED_VALUE === $value) {
                    $result = self::TRUE;
                    break;
                }
            }
        }
        $model->setData($this->getData('attribute'), $result);
        return parent::validate($model);
    }
}
