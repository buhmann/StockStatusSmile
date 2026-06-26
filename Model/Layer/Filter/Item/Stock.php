<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatusSmile\Model\Layer\Filter\Item;

use Buhmann\StockStatus\Model\Layer\Filter\Item\StockTrait;
use Buhmann\StockStatus\Model\Layer\Filter\Stock as FilterStock;
use Magento\Framework\Exception\LocalizedException;
use Smile\ElasticsuiteCatalog\Model\Layer\Filter\Item\Attribute;

/**
 * Stock status filter item for ElasticSuite
 *
 * Extends ElasticSuite Attribute item to properly handle
 * selected state for stock status filter.
 */
class Stock extends Attribute
{
    use StockTrait;

    /**
     * Get URL for filter item
     *
     * For selected items, returns URL without the filter parameter (remove filter).
     * For unselected items, returns URL with the filter parameter (apply filter).
     *
     * @return string
     * @throws LocalizedException
     */
    public function getUrl(): string
    {
        /** @var FilterStock $filter */
        $filter = $this->getFilter();
        $requestVar = $filter->getRequestVar();

        if (!$filter->isMultiSelectEnabled()) {
            if ($this->getIsSelected()) {
                $params = [
                    '_current' => true,
                    '_use_rewrite' => true,
                    '_query' => [$requestVar => null],
                ];
                return $this->_url->getUrl('*/*/*', $params);
            }
            return parent::getUrl();
        }

        return $this->buildMultiSelectUrl(
            $requestVar,
            (int)$this->getValue(),
            $filter->getSelectedValues() ?? []
        );
    }

    /**
     * Get remove URL for filter item
     *
     * Removes current value from selected values in multi-select mode.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getRemoveUrl(): string
    {
        /** @var FilterStock $filter */
        $filter = $this->getFilter();
        $requestVar = $filter->getRequestVar();

        if (!$filter->isMultiSelectEnabled()) {
            return parent::getRemoveUrl();
        }

        return $this->buildMultiSelectRemoveUrl(
            $requestVar,
            (int)$this->getValue(),
            $filter->getSelectedValues() ?? []
        );
    }
}
