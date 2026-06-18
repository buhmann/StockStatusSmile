<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatusSmile\Model\Layer\Filter\Item;

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
    /**
     * Get URL for filter item
     *
     * For selected items, returns URL without the filter parameter (remove filter).
     * For unselected items, returns URL with the filter parameter (apply filter).
     *
     * @return string
     * @throws LocalizedException
     */
    public function getUrl()
    {
        $filter = $this->getFilter();
        $requestVar = $filter->getRequestVar();

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

    /**
     * Check if the current item is selected
     *
     * @return bool
     * @throws LocalizedException
     */
    public function getIsSelected(): bool
    {
        $filter = $this->getFilter();
        $selectedValues = [];

        foreach ($filter->getLayer()->getState()->getFilters() as $stateFilter) {
            if ($stateFilter->getFilter()->getRequestVar() === $filter->getRequestVar()) {
                $selectedValues[] = $stateFilter->getValue();
            }
        }

        return in_array((string)$this->getValue(), $selectedValues, true);
    }
}
