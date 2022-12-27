/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: true,
            districtScope: 'data.district',
            imports: {
                cityOptions: '${ $.parentName }.city_id:indexedOptions',
                update: '${ $.parentName }.city_id:value'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            var option;

            this._super();
            option = _.find(this.cityOptions, function (row) {
                return row['is_default'] === true;
            });
            //this.hideDistrict(option);

            return this;
        },

        /**
         * Method called every time country selector's value gets changed.
         * Updates all validations and requirements for certain country.
         * @param {String} value - Selected country ID.
         */
        // update: function (value) {
        //     console.log(this.customName);
        // },

        /**
         * Set city to customer address form
         *
         * @param {String} value - city
         */
        setDifferedFromDefault: function (value) {
            this._super();

            if (parseFloat(value)) {
                this.source.set(this.districtScope, this.indexedOptions[value].label);
            }
        },

        /**
         * Hide select and corresponding text input field if region must not be shown for selected country.
         *
         * @private
         * @param {Object}option
         */
        hideDistrict: function (option) {
            if (!option || option['is_district_visible'] !== false) {
                return;
            }

            this.setVisible(false);
            if (this.customEntry) {
                this.toggleInput(false);
            }
        }
    });
});


