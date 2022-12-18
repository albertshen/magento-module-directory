<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model;

/**
 * District
 *
 * @method string getDistrictId()
 * @method string getCityId()
 * @method \AlbertMage\Directory\Model\District setRegionId(int $value)
 * @method string getCode()
 * @method \AlbertMage\Directory\Model\District setCode(string $value)
 * @method string getDefaultName()
 * @method \AlbertMage\Directory\Model\District setDefaultName(string $value)
 *
 * @api
 * @since 100.0.2
 */
class District extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AlbertMage\Directory\Model\ResourceModel\District::class);
    }

    /**
     * Retrieve district name
     *
     * If name is not declared, then default_name is used
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($name === null) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    /**
     * Load district by code
     *
     * @param string $code
     * @param string $cityId
     * @return $this
     */
    public function loadByCode($code, $cityId)
    {
        if ($code) {
            $this->_getResource()->loadByCode($this, $code, $cityId);
        }
        return $this;
    }

    /**
     * Load district by name
     *
     * @param string $name
     * @param string $cityId
     * @return $this
     */
    public function loadByName($name, $cityId)
    {
        $this->_getResource()->loadByName($this, $name, $cityId);
        return $this;
    }
}
