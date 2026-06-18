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
