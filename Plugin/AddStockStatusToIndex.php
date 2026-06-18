<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatusSmile\Plugin;

use Smile\ElasticsuiteCatalog\Model\Product\Indexer\Fulltext\Datasource\AttributeData;
use Magento\Framework\App\ResourceConnection;
use Buhmann\StockStatus\ViewModel\ConfigProvider;

/**
 * Plugin to add stock_status field to ElasticSuite product index data
 *
 * This plugin injects the stock_status value from cataloginventory_stock_status
 * table into the product index data during fulltext indexing.
 */
class AddStockStatusToIndex
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
    }

    /**
     * Add stock_status field to indexed product data
     *
     * @param AttributeData $subject
     * @param array $indexData
     * @param int $storeId
     * @param array $productIds
     * @return array
     */
    public function afterAddData(
        AttributeData $subject,
        array $indexData,
        int $storeId,
        array $productIds
    ): array {
        $indexField = $this->configProvider->getIndexField();

        if (empty($productIds)) {
            return $indexData;
        }

        // Skip if stock_status already exists in index data
        if (!empty($indexData)) {
            $firstProductId = array_key_first($indexData);
            if (isset($indexData[$firstProductId][$indexField])) {
                return $indexData;
            }
        }

        // Fetch stock_status from database
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                ['product_id', 'stock_status']
            )
            ->where('product_id IN (?)', $productIds);

        $stockStatuses = $connection->fetchPairs($select);

        // Add stock_status to index data
        foreach ($stockStatuses as $productId => $status) {
            if (!isset($indexData[$productId])) {
                $indexData[$productId] = [];
            }
            $indexData[$productId][$indexField] = (int)$status;
        }

        return $indexData;
    }
}
