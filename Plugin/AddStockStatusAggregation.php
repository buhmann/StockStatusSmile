<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatusSmile\Plugin;

use Smile\ElasticsuiteCore\Search\Request\ContainerConfiguration;
use Buhmann\StockStatus\ViewModel\ConfigProvider;

/**
 * Plugin to add stock_status aggregation to ElasticSuite search requests
 *
 * This plugin dynamically adds a term bucket aggregation for stock_status field
 * to category and search page requests, enabling filter counts in layered navigation.
 */
class AddStockStatusAggregation
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Add stock_status aggregation to the search request
     *
     * @param ContainerConfiguration $subject
     * @param array $result
     * @param mixed $query
     * @param array $filters
     * @param array $queryFilters
     * @return array
     */
    public function afterGetAggregations(
        ContainerConfiguration $subject,
        array $result,
        $query = null,
        $filters = [],
        $queryFilters = []
    ): array {
        // Skip if filter is disabled in configuration
        if (!$this->configProvider->isStockFilterEnabled()) {
            return $result;
        }

        $containerName = $subject->getName();

        // Only modify category and search page requests
        if (!in_array($containerName, ['catalog_view_container', 'quick_search_container'])) {
            return $result;
        }

        $indexField = $this->configProvider->getIndexField();

        // Check if aggregation already exists
        foreach ($result as $key => $agg) {
            if (isset($agg['name']) && $agg['name'] === $indexField) {
                return $result;
            }
            if ($key === $indexField) {
                return $result;
            }
        }

        // Add term bucket aggregation for stock_status
        $result[$indexField] = [
            'type' => 'termBucket',
            'name' => $indexField,
            'field' => $indexField,
            'childBuckets' => [],
            'metrics' => [],
            'pipelines' => [],
            'isNumeric' => false,
        ];

        return $result;
    }
}
