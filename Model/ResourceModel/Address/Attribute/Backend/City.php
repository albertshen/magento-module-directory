<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Directory\Model\ResourceModel\Address\Attribute\Backend;

/**
 * Address city attribute backend
 *
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class City extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \AlbertMage\Directory\Model\CityFactory
     */
    protected $_cityFactory;

    /**
     * @param \AlbertMage\Directory\Model\CityFactory $cityFactory
     */
    public function __construct(\AlbertMage\Directory\Model\CityFactory $cityFactory)
    {
        $this->_cityFactory = $cityFactory;
    }

    /**
     * Prepare object for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $city = $object->getData('city');
        if (is_numeric($city)) {
            $cityModel = $this->_createCityInstance();
            $cityModel->load($city);
            if ($cityModel->getId() && $object->getCountryId() == $cityModel->getCountryId()) {
                $object->setCityId($cityModel->getId())->setCity($cityModel->getName());
            }
        }
        return $this;
    }

    /**
     * @return \AlbertMage\Directory\Model\City
     */
    protected function _createCityInstance()
    {
        return $this->_cityFactory->create();
    }
}
