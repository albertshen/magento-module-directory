<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */

/**
 * Customer district attribute source
 * @author Albert Shen <albertshen1206@gmail.com>
 */
namespace AlbertMage\Directory\Model\ResourceModel\Address\Attribute\Source;

class District extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \AlbertMage\Directory\Model\ResourceModel\District\CollectionFactory
     */
    protected $_districtsFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \AlbertMage\Directory\Model\ResourceModel\District\CollectionFactory $districtsFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \AlbertMage\Directory\Model\ResourceModel\District\CollectionFactory $districtsFactory
    ) {
        $this->_districtsFactory = $districtsFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_createDistrictsCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return \AlbertMage\Directory\Model\ResourceModel\District\Collection
     */
    protected function _createDistrictsCollection()
    {
        return $this->_districtsFactory->create();
    }
}
