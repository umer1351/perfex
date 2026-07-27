<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Consolidation_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Consolidated Client URL',
                'key'       => '{consolidated_client_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-consolidation-to-contact',
                    'logistic-consolidated-assign-driver',
                    'consolidation-shipment-tracking-to-contact',
                    'logistic-consolidation-created-to-contact'
                ],
              
            ],
            [
                'name'      => 'Tracking Number',
                'key'       => '{tracking_number}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-consolidation-to-contact',
                   'logistic-consolidated-assign-driver',
                   'consolidation-shipment-tracking-to-contact',
                   'logistic-consolidation-created-to-contact'
                ],
               
            ],
            [
                'name'      => 'Delivery Status',
                'key'       => '{delivery_status}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-consolidation-to-contact',
                   'logistic-consolidated-assign-driver',
                   'consolidation-shipment-tracking-to-contact',
                   'logistic-consolidation-created-to-contact'
                ],
               
            ],
            [
                'name'      => 'Consolidated Admin URL',
                'key'       => '{consolidated_admin_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-consolidation-to-contact',
                    'logistic-consolidated-assign-driver',
                    'consolidation-shipment-tracking-to-contact',
                    'logistic-consolidation-created-to-contact'
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
    public function format($consolidated_id)
    {
        $fields = [];
        $this->ci->db->where('id', $consolidated_id);
        $consolidated = $this->ci->db->get(db_prefix() . 'lg_consolidated')->row();

        if (!$consolidated) {
            return $fields;
        }

       
        $fields['{tracking_number}']  = $consolidated->shipping_prefix.$consolidated->number_code;
        $fields['{delivery_status}']  = format_lg_package_status($consolidated->delivery_status, 1);
        $fields['{consolidated_client_url}']       = site_url('logistic/client/consolidated_detail/' . $consolidated_id);
        $fields['{consolidated_admin_url}']       = admin_url('logistic/consolidated_detail/' . $consolidated_id);
        
        return $fields;
    }
}
