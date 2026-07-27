<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shipping_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Shipping Client URL',
                'key'       => '{shipping_client_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-shipping-to-contact',
                    'shipping-delivered-to-contact',
                    'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff',
                ],
              
            ],
            [
                'name'      => 'Tracking Number',
                'key'       => '{tracking_number}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-shipping-to-contact',
                   'shipping-delivered-to-contact',
                   'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff'
                ],
               
            ],
            [
                'name'      => 'Delivery Status',
                'key'       => '{delivery_status}',
                'available' => [
                    
                ],
                'templates' => [
                   'logistic-shipping-to-contact',
                   'shipping-delivered-to-contact',
                   'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff'
                ],
               
            ],
            [
                'name'      => 'Recipient Firstname',
                'key'       => '{recipient_firstname}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-shipping-to-contact',
                   'shipping-delivered-to-contact',
                   'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff'
                ],
            ],
            [
                'name'      => 'Recipient Lastname',
                'key'       => '{recipient_lastname}',
                'available' => [
                    
                ],
                      'templates' => [
                         'logistic-shipping-to-contact',
                   'shipping-delivered-to-contact',
                   'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff'
                      ],
            ],
            [
                'name'      => 'Shipping Admin URL',
                'key'       => '{shipping_admin_url}',
                'available' => [
                    
                ],
                'templates' => [
                    'logistic-shipping-to-contact',
                    'shipping-delivered-to-contact',
                    'pickup-approved',
                    'pickup-rejected',
                    'logistic-shipping-assign-driver',
                     'logistic-shipping-created-to-contact',
                     'logistic-shipping-shipment-tracking',
                     'logistic-pickup-created-to-staff'
                ],
              
            ],
        ];
    }

    /**
     * Merge fields for invoices
     * @param  mixed $shipping_id invoice id
     * @param  mixed $payment_id payment id
     * @return array
     */
    public function format($shipping_id)
    {

        $fields = [];
        $this->ci->db->where('id', $shipping_id);
        $shipping = $this->ci->db->get(db_prefix() . 'lg_shippings')->row();

        if (!$shipping) {
            return $fields;
        }

        $this->ci->db->where('id', $shipping->recipient_id);
        $recipient = $this->ci->db->get(db_prefix() . 'lg_recipients')->row();

        $fields['{recipient_firstname}']  = isset($recipient->first_name) ? $recipient->first_name : '';
        $fields['{recipient_lastname}']  = isset($recipient->last_name) ? $recipient->last_name : '';

       
        $fields['{tracking_number}']  = $shipping->shipping_prefix.$shipping->number_code;
        $fields['{delivery_status}']  = format_lg_package_status($shipping->delivery_status, 1);
        $fields['{shipping_client_url}']       = site_url('logistic/client/shipping_detail/' . $shipping_id);
        $fields['{shipping_admin_url}']       = admin_url('logistic/shipping_detail/' . $shipping_id);
 
        return $fields;
    }
}
