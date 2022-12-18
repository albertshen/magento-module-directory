<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model\ResourceModel\District;

use Magento\Framework\App\ObjectManager;

/**
 * Regions collection
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Locale district name table name
     *
     * @var string
     */
    protected $_districtNameTable;

    /**
     * City table name
     *
     * @var string
     */
    protected $_cityTable;

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
     * Define main, city, locale district name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AlbertMage\Directory\Model\District::class, \AlbertMage\Directory\Model\ResourceModel\District::class);

        $this->_cityTable = $this->getTable('directory_country_region_city');
        $this->_districtNameTable = $this->getTable('directory_country_region_city_district_name');

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

        $this->addBindParam(':district_locale', $locale);
        $this->getSelect()->joinLeft(
            ['dname' => $this->_districtNameTable],
            'main_table.district_id = dname.district_id AND dname.locale = :district_locale',
            ['name']
        );

        return $this;
    }

    /**
     * Filter by city_id
     *
     * @param string|array $cityId
     * @return $this
     */
    public function addCityFilter($cityId)
    {
        if (!empty($cityId)) {
            if (is_array($cityId)) {
                $this->addFieldToFilter('main_table.city_id', ['in' => $cityId]);
            } else {
                $this->addFieldToFilter('main_table.city_id', $cityId);
            }
        }
        return $this;
    }

    /**
     * Filter by city code 
     *
     * @param string $cityCode
     * @return $this
     */
    public function addCityCodeFilter($cityCode)
    {
        $this->getSelect()->joinLeft(
            ['city' => $this->_countryTable],
            'main_table.city_id = country.city_id'
        )->where(
            'city.code = ?',
            $cityCode
        );

        return $this;
    }


    /**
     * Filter by district code
     *
     * @param string|array $districtCode
     * @return $this
     */
    public function addDistrictCodeFilter($districtCode)
    {
        if (!empty($districtCode)) {
            if (is_array($districtCode)) {
                $this->addFieldToFilter('main_table.code', ['in' => $districtCode]);
            } else {
                $this->addFieldToFilter('main_table.code', $districtCode);
            }
        }
        return $this;
    }

    /**
     * Filter by district name
     *
     * @param string|array $districtName
     * @return $this
     */
    public function addDistrictNameFilter($districtName)
    {
        if (!empty($districtName)) {
            if (is_array($districtName)) {
                $this->addFieldToFilter('main_table.default_name', ['in' => $districtName]);
            } else {
                $this->addFieldToFilter('main_table.default_name', $districtName);
            }
        }
        return $this;
    }

    /**
     * Filter district by its code or name
     *
     * @param string|array $district
     * @return $this
     */
    public function addDistrictCodeOrNameFilter($district)
    {
        if (!empty($district)) {
            $condition = is_array($district) ? ['in' => $district] : $district;
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
            'value' => 'district_id',
            'title' => 'default_name',
            'city_id' => 'city_id',
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
                ['title' => '', 'value' => '', 'label' => __('Please select a district.')]
            );
        }
        return $options;
    }
}
