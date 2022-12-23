<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Directory\Model\ResourceModel\Address\Attribute\Backend;

/**
 * Address district attribute backend
 *
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class District extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \AlbertMage\Directory\Model\DistrictFactory
     */
    protected $_districtFactory;

    /**
     * @param \AlbertMage\Directory\Model\DistrictFactory $districtFactory
     */
    public function __construct(\AlbertMage\Directory\Model\DistrictFactory $districtFactory)
    {
        $this->_districtFactory = $districtFactory;
    }

    /**
     * Prepare object for save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $district = $object->getData('district');
        if (is_numeric($district)) {
            $districtModel = $this->_createDistrictInstance();
            $districtModel->load($district);
            if ($districtModel->getId() && $object->getCountryId() == $districtModel->getCountryId()) {
                $object->setDistrictId($districtModel->getId())->setDistrict($districtModel->getName());
            }
        }
        return $this;
    }

    /**
     * @return \AlbertMage\Directory\Model\District
     */
    protected function _createDistrictInstance()
    {
        return $this->_districtFactory->create();
    }
}
