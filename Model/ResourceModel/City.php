<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model\ResourceModel;

/**
 * City Resource Model
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class City extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Table with localized city names
     *
     * @var string
     */
    protected $_cityNameTable;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Define main and locale city name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country_region_city', 'city_id');
        $this->_cityNameTable = $this->getTable('directory_country_region_city_name');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $connection = $this->getConnection();

        $locale = $this->_localeResolver->getLocale();
        $systemLocale = \Magento\Framework\AppInterface::DISTRO_LOCALE_CODE;

        $cityField = $connection->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());

        $condition = $connection->quoteInto('lrn.locale = ?', $locale);
        $select->joinLeft(
            ['lrn' => $this->_cityNameTable],
            "{$cityField} = lrn.city_id AND {$condition}",
            []
        );

        if ($locale != $systemLocale) {
            $nameExpr = $connection->getCheckSql('lrn.city_id is null', 'srn.name', 'lrn.name');
            $condition = $connection->quoteInto('srn.locale = ?', $systemLocale);
            $select->joinLeft(
                ['srn' => $this->_cityNameTable],
                "{$cityField} = srn.city_id AND {$condition}",
                ['name' => $nameExpr]
            );
        } else {
            $select->columns(['name'], 'lrn');
        }

        return $select;
    }

    /**
     * Load object by region id and code or default name
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $regionId
     * @param string $value
     * @param string $field
     * @return $this
     */
    protected function _loadByRegion($object, $regionId, $value, $field)
    {
        $connection = $this->getConnection();
        $locale = $this->_localeResolver->getLocale();
        $joinCondition = $connection->quoteInto('cname.city_id = city.city_id AND cname.locale = ?', $locale);
        $select = $connection->select()->from(
            ['city' => $this->getMainTable()]
        )->joinLeft(
            ['cname' => $this->_cityNameTable],
            $joinCondition,
            ['name']
        )->where(
            'city.region_id = ?',
            $regionId
        )->where(
            "city.{$field} = ?",
            $value
        );

        $data = $connection->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Loads city by city code and region id
     *
     * @param \AlbertMage\Directory\Model\City $city
     * @param string $cityCode
     * @param string $regionId
     *
     * @return $this
     */
    public function loadByCode(\AlbertMage\Directory\Model\City $city, $cityCode, $regionId)
    {
        return $this->_loadByRegion($city, $regionId, (string)$cityCode, 'code');
    }

    /**
     * Load data by region id and default region name
     *
     * @param \AlbertMage\Directory\Model $city
     * @param string $cityName
     * @param string $regionId
     * @return $this
     */
    public function loadByName(\AlbertMage\Directory\Model\City $city, $cityName, $regionId)
    {
        return $this->_loadByRegion($city, $regionId, (string)$cityName, 'default_name');
    }
}
