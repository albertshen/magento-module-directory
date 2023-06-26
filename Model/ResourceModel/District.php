<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model\ResourceModel;

/**
 * District Resource Model
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class District extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Table with localized district names
     *
     * @var string
     */
    protected $_districtNameTable;

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
     * Define main and locale district name tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country_region_city_district', 'district_id');
        $this->_districtNameTable = $this->getTable('directory_country_region_city_district_name');
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

        $districtField = $connection->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());

        $condition = $connection->quoteInto('lrn.locale = ?', $locale);
        $select->joinLeft(
            ['lrn' => $this->_districtNameTable],
            "{$districtField} = lrn.district_id AND {$condition}",
            []
        );

        if ($locale != $systemLocale) {
            $nameExpr = $connection->getCheckSql('lrn.district_id is null', 'srn.name', 'lrn.name');
            $condition = $connection->quoteInto('srn.locale = ?', $systemLocale);
            $select->joinLeft(
                ['srn' => $this->_districtNameTable],
                "{$districtField} = srn.district_id AND {$condition}",
                ['name' => $nameExpr]
            );
        } else {
            $select->columns(['name'], 'lrn');
        }

        return $select;
    }

    /**
     * Load object by city id and code or default name
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $cityId
     * @param string $value
     * @param string $field
     * @return $this
     */
    protected function _loadByCity($object, $cityId, $value, $field)
    {
        $connection = $this->getConnection();
        $locale = $this->_localeResolver->getLocale();
        $joinCondition = $connection->quoteInto('dname.district_id = district.district_id AND dname.locale = ?', $locale);
        $select = $connection->select()->from(
            ['district' => $this->getMainTable()]
        )->joinLeft(
            ['dname' => $this->_districtNameTable],
            $joinCondition,
            ['name']
        )->where(
            'district.city_id = ?',
            $cityId
        )->where(
            "district.{$field} = ?",
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
     * Loads district by district code and city id
     *
     * @param \AlbertMage\Directory\Model\District $district
     * @param string $districtCode
     * @param string $cityId
     *
     * @return $this
     */
    public function loadByCode(\AlbertMage\Directory\Model\District $district, $districtCode, $cityId)
    {
        return $this->_loadByCity($district, $cityId, (string)$districtCode, 'code');
    }

    /**
     * Load data by region id and default region name
     *
     * @param \AlbertMage\Directory\Model\District $district
     * @param string $districtName
     * @param string $cityId
     * @return $this
     */
    public function loadByName(\AlbertMage\Directory\Model\District $district, $districtName, $cityId)
    {
        return $this->_loadByCity($district, $cityId, (string)$districtName, 'default_name');
    }
}
