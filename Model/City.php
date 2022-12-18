<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

namespace AlbertMage\Directory\Model;

/**
 * City
 *
 * @method string getCityId()
 * @method string getRegionId()
 * @method \AlbertMage\Directory\Model\City setRegionId(int $value)
 * @method string getCode()
 * @method \AlbertMage\Directory\Model\City setCode(string $value)
 * @method string getDefaultName()
 * @method \AlbertMage\Directory\Model\City setDefaultName(string $value)
 *
 * @api
 * @since 100.0.2
 */
class City extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\AlbertMage\Directory\Model\ResourceModel\City::class);
    }

    /**
     * Retrieve city name
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
     * Load city by code
     *
     * @param string $code
     * @param string $regionId
     * @return $this
     */
    public function loadByCode($code, $regionId)
    {
        if ($code) {
            $this->_getResource()->loadByCode($this, $code, $regionId);
        }
        return $this;
    }

    /**
     * Load city by name
     *
     * @param string $name
     * @param string $regionId
     * @return $this
     */
    public function loadByName($name, $regionId)
    {
        $this->_getResource()->loadByName($this, $name, $regionId);
        return $this;
    }
}
