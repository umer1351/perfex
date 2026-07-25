<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Package_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Package Client URL',
                'key'       => '{package_client_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-package-to-contact',
                    'package-delivered-to-contact',
                    'logistic-package-created-to-contact',
                    'logistic-package-shipment-tracking',
                    'logistic-package-assign-driver'
                ],
              
            ],
            [
                'name'      => 'Tracking Number',
                'key'       => '{tracking_number}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-package-to-contact',
                   'package-delivered-to-contact',
                   'logistic-package-created-to-contact',
                   'logistic-package-shipment-tracking',
                   'logistic-package-assign-driver',
                ],
               
            ],
            [
                'name'      => 'Delivery Status',
                'key'       => '{delivery_status}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-package-to-contact',
                   'package-delivered-to-contact',
                   'logistic-package-created-to-contact',
                   'logistic-package-shipment-tracking',
                   'logistic-package-assign-driver'
                ],
               
            ],
            [
                'name'      => 'Package Admin URL',
                'key'       => '{package_admin_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-package-to-contact',
                    'package-delivered-to-contact',
                    'logistic-package-created-to-contact',
                    'logistic-package-shipment-tracking',
                    'logistic-package-assign-driver'
                ],
              
            ],

           
        ];
    }

    /**
     * Merge fields for invoices
     * @param  mixed $package_id invoice id
     * @param  mixed $payment_id payment id
     * @return array
     */
    public function format($package_id)
    {
        $fields = [];
        $this->ci->db->where('id', $package_id);
        $package = $this->ci->db->get(db_prefix() . 'lg_packages')->row();

        if (!$package) {
            return $fields;
        }

       
        $fields['{tracking_number}']  = $package->shipping_prefix.$package->number_code;
        $fields['{delivery_status}']  = format_lg_package_status($package->delivery_status, 1);
        $fields['{package_client_url}']       = site_url('logistic/client/package_detail/' . $package_id);
        $fields['{package_admin_url}']       = admin_url('logistic/package_detail/' . $package_id);
        
        return $fields;
    }
}
