<?php
/**
 * Copyright © Buhmann. All rights reserved.
 */

declare(strict_types=1);

namespace Buhmann\StockStatusSmile\Plugin;

use Smile\ElasticsuiteCore\Api\Index\MappingInterface;
use Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterfaceFactory;
use Buhmann\StockStatus\ViewModel\ConfigProvider;

/**
 * Plugin to fix mapping cache for stock_status field
 *
 * ElasticSuite caches mapping data. When stock_status field is added dynamically,
 * the cache may not reflect the new field. This plugin intercepts getField calls
 * and adds the field to the mapping if it doesn't exist.
 */
class MappingCache
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * @var FieldInterfaceFactory
     */
    private FieldInterfaceFactory $fieldFactory;

    /**
     * @param ConfigProvider $configProvider
     * @param FieldInterfaceFactory $fieldFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        FieldInterfaceFactory $fieldFactory
    ) {
        $this->configProvider = $configProvider;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Intercept getField calls to ensure stock_status exists in mapping
     *
     * @param MappingInterface $subject
     * @param callable $proceed
     * @param string $fieldName
     * @return \Smile\ElasticsuiteCore\Api\Index\Mapping\FieldInterface
     * @throws \LogicException
     */
    public function aroundGetField(
        MappingInterface $subject,
        callable $proceed,
        string $fieldName
    ) {
        try {
            $field = $proceed($fieldName);

            // Convert array to FieldInterface if needed
            if (is_array($field)) {
                return $this->fieldFactory->create($field);
            }

            return $field;
        } catch (\LogicException $e) {
            // Re-throw if error is not about missing field
            if (!str_contains($e->getMessage(), 'does not exists in mapping')) {
                throw $e;
            }

            // Build field definition
            $fieldData = [
                'name' => $this->configProvider->getIndexField(),
                'type' => 'integer',
                'isFilterable' => true,
                'isSearchable' => false,
                'isSortable' => false,
                'isUsedInAutocomplete' => false,
                'nestingPath' => null,
                'isIndexed' => true,
            ];

            // Add field to mapping
            try {
                $reflection = new \ReflectionClass($subject);
                $parentClass = $reflection->getParentClass();

                if ($parentClass) {
                    $fieldsProperty = $parentClass->getProperty('fields');
                    $fieldsProperty->setAccessible(true);
                    $currentFields = $fieldsProperty->getValue($subject);
                    $currentFields[$fieldName] = $fieldData;
                    $fieldsProperty->setValue($subject, $currentFields);
                }
            } catch (\ReflectionException $e) {
                // Mapping update failed, fall through
            }

            // Return field as FieldInterface
            return $this->fieldFactory->create($fieldData);
        }
    }
}
