<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model\ResourceModel\City;

use Magento\Framework\App\ObjectManager;

/**
 * Cities collection
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Locale city name table name
     *
     * @var string
     */
    protected $_cityNameTable;

    /**
     * Region table name
     *
     * @var string
     */
    protected $_regionTable;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;


    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param mixed $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_localeResolver = $localeResolver;
        $this->_resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define main, region, locale city name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AlbertMage\Directory\Model\City::class, \AlbertMage\Directory\Model\ResourceModel\City::class);

        $this->_regionTable = $this->getTable('directory_country_region');
        $this->_cityNameTable = $this->getTable('directory_country_region_city_name');

        $this->addOrder('name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
        $this->addOrder('default_name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $locale = $this->_localeResolver->getLocale();

        $this->addBindParam(':city_locale', $locale);
        $this->getSelect()->joinLeft(
            ['cname' => $this->_cityNameTable],
            'main_table.city_id = cname.city_id AND cname.locale = :city_locale',
            ['name']
        );

        return $this;
    }

    /**
     * Filter by region_id
     *
     * @param string|array $regionId
     * @return $this
     */
    public function addRegionFilter($regionId)
    {
        if (!empty($regionId)) {
            if (is_array($regionId)) {
                $this->addFieldToFilter('main_table.region_id', ['in' => $regionId]);
            } else {
                $this->addFieldToFilter('main_table.region_id', $regionId);
            }
        }
        return $this;
    }

    /**
     * Filter by region code 
     *
     * @param string $regionCode
     * @return $this
     */
    public function addRegionCodeFilter($regionCode)
    {
        $this->getSelect()->joinLeft(
            ['region' => $this->_regionTable],
            'main_table.region_id = country.region_id'
        )->where(
            'region.code = ?',
            $regionCode
        );

        return $this;
    }


    /**
     * Filter by City code
     *
     * @param string|array $cityCode
     * @return $this
     */
    public function addCityCodeFilter($cityCode)
    {
        if (!empty($cityCode)) {
            if (is_array($cityCode)) {
                $this->addFieldToFilter('main_table.code', ['in' => $cityCode]);
            } else {
                $this->addFieldToFilter('main_table.code', $cityCode);
            }
        }
        return $this;
    }

    /**
     * Filter by city name
     *
     * @param string|array $cityName
     * @return $this
     */
    public function addCityNameFilter($cityName)
    {
        if (!empty($cityName)) {
            if (is_array($cityName)) {
                $this->addFieldToFilter('main_table.default_name', ['in' => $cityName]);
            } else {
                $this->addFieldToFilter('main_table.default_name', $cityName);
            }
        }
        return $this;
    }

    /**
     * Filter city by its code or name
     *
     * @param string|array $city
     * @return $this
     */
    public function addCityCodeOrNameFilter($city)
    {
        if (!empty($city)) {
            $condition = is_array($city) ? ['in' => $city] : $city;
            $this->addFieldToFilter(
                ['main_table.code', 'main_table.default_name'],
                [$condition, $condition]
            );
        }
        return $this;
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $propertyMap = [
            'value' => 'city_id',
            'title' => 'default_name',
            'region_id' => 'region_id',
        ];

        foreach ($this as $item) {
            $option = [];
            foreach ($propertyMap as $code => $field) {
                $option[$code] = $item->getData($field);
            }
            $option['label'] = $item->getName();
            $options[] = $option;
        }

        if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a city.')]
            );
        }
        return $options;
    }
}
