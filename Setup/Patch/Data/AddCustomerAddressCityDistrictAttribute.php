<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace Albert\CustomApi\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Creates a customer attribute for managing a customer's external system ID
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class AddCustomerAddressCityDistrictAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Add customer attribute with settings
        $customerSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'city_id',
            [
                'type' => 'static',
                'label' => 'City',
                'input' => 'hidden',
                'source' => \Albert\CustomApi\Model\ResourceModel\Address\Attribute\Source\City::class,
                'required' => false,
                'sort_order' => 101,
                'position' => 101,
            ]
        );
        $customerSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'district',
            [
                'type' => 'static',
                'label' => 'District',
                'input' => 'text',
                'required' => false,
                'sort_order' => 102,
                'position' => 102,
            ]
        );
        $customerSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'district_id',
            [
                'type' => 'static',
                'label' => 'District',
                'input' => 'hidden',
                'source' => \Albert\CustomApi\Model\ResourceModel\Address\Attribute\Source\District::class,
                'required' => false,
                'sort_order' => 102,
                'position' => 102,
            ]
        );
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(AddressMetadataInterface::ENTITY_TYPE_ADDRESS);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $cityIdAttribute = $customerSetup->getEavConfig()->getAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'city_id');

        $cityIdAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => [
                'adminhtml_customer',
                'adminhtml_customer_address',
                'customer_account_edit',
                'customer_address_edit'
            ],
        ]);

        // Save attribute using its resource model
        $cityIdAttribute->save();


        $districtAttribute = $customerSetup->getEavConfig()->getAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'district');

        $districtAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => [
                'adminhtml_customer',
                'adminhtml_customer_address',
                'customer_account_edit',
                'customer_address_edit'
            ],
        ]);

        // Save attribute using its resource model
        $districtAttribute->save();


        $districtIdAttribute = $customerSetup->getEavConfig()->getAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, 'district_id');

        $districtIdAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => [
                'adminhtml_customer',
                'adminhtml_customer_address',
                'customer_account_edit',
                'customer_address_edit'
            ],
        ]);

        // Save attribute using its resource model
        $districtIdAttribute->save();

        return $this;

    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

}
