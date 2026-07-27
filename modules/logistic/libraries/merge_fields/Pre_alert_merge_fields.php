<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pre_alert_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            
            [
                'name'      => 'Tracking Purchase',
                'key'       => '{tracking_purchase}',
                'available' => [
                    
                ],
                'templates' => [
                   'new-pre-alert-created'
                ],
               
            ],
            [
                'name'      => 'Delivery Date',
                'key'       => '{delivery_date}',
                'available' => [
                  
                ],
                      'templates' => [
                        'new-pre-alert-created'
                      ],
            ],
            [
                'name'      => 'Store/Supplier',
                'key'       => '{store_supplier}',
                'available' => [
                    
                ],
                      'templates' => [
                        'new-pre-alert-created'
                      ],
            ],
             [
                'name'      => 'Pre alert list admin url',
                'key'       => '{pre_alert_list_url}',
                'available' => [
                    
                ],
                      'templates' => [
                        'new-pre-alert-created'
                      ],
            ],
             [
                'name'      => 'Convert Link',
                'key'       => '{convert_link}',
                'available' => [
                    
                ],
                      'templates' => [
                        'new-pre-alert-created'
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
    public function format($pre_alert_id)
    {

        $fields = [];
        $this->ci->db->where('id', $pre_alert_id);
        $pre_alert = $this->ci->db->get(db_prefix() . 'lg_pre_alert')->row();

        if (!$pre_alert) {
            return $fields;
        }

        $this->ci->db->where('id', $pre_alert->recipient_id);
        $recipient = $this->ci->db->get(db_prefix() . 'lg_recipients')->row();
       
        $fields['{tracking_purchase}']  = $pre_alert->tracking_purchase;
        $fields['{delivery_date}']  = _dt($pre_alert->delivery_date);
        $fields['{store_supplier}']       = $pre_alert->store_supplier;
        $fields['{pre_alert_list_url}'] = admin_url('logistic/pre_alert_list');
        $fields['{convert_link}'] = admin_url('logistic/register_package/0/0/'.$pre_alert_id);
 
        return $fields;
    }
}
