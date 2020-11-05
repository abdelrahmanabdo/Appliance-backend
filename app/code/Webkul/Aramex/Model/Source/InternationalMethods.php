<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Aramex
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Aramex\Model\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;

/**
 * Generic source
 */
class InternationalMethods implements GenericInterface
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $code = '';
    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=>'DPX', 'label'=>'Value Express Parcels'],
            ['value'=>'EPX', 'label'=>'Economy Parcel Express'],
            ['value'=>'GDX', 'label'=>'Ground Document Express'],
            ['value'=>'GPX', 'label'=>'Ground Parcel Express'],
            ['value'=>'PDX', 'label'=>'Priority Document Express'],
            ['value'=>'PPX', 'label'=>'Priority Parcel Express'],
        ];
        
        return $options;
    }

    public function toKeyArray()
    {
        $result  = [];
        $options = $this->toOptionArray();
        foreach ($options as $option) {
             $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}
