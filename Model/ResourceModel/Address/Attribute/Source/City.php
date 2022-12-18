<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

/**
 * Customer city attribute source
 * @author Albert Shen <albertshen1206@gmail.com>
 */
namespace AlbertMage\Directory\Model\ResourceModel\Address\Attribute\Source;

class City extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \AlbertMage\Directory\Model\ResourceModel\City\CollectionFactory
     */
    protected $_citiesFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \AlbertMage\Directory\Model\ResourceModel\City\CollectionFactory $citiesFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \AlbertMage\Directory\Model\ResourceModel\City\CollectionFactory $citiesFactory
    ) {
        $this->_citiesFactory = $citiesFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_createCitiesCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \AlbertMage\Directory\Model\ResourceModel\City\Collection
     */
    protected function _createCitiesCollection()
    {
        return $this->_citiesFactory->create();
    }
}
