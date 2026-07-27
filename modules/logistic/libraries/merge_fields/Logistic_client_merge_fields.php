<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Logistic_client_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Contact Firstname',
                    'key'       => '{contact_firstname}',
                    'available' => [
                        
                       
                    ],
                    'templates' => [
                        'package-delivered-to-contact',
                        'logistic-package-to-contact',
                        'shipping-delivered-to-contact',
                        'pickup-approved',
                        'pickup-rejected',
                        'logistic-package-created-to-contact',
                        'logistic-package-shipment-tracking',
                         'logistic-shipping-created-to-contact',
                         'logistic-shipping-shipment-tracking',
                         'consolidation-shipment-tracking-to-contact',
                         'logistic-consolidation-created-to-contact'
                    ],
                ],
                [
                    'name'      => 'Contact Lastname',
                    'key'       => '{contact_lastname}',
                    'available' => [
                        
                      
                    ],
                    'templates' => [
                        'package-delivered-to-contact',
                        'logistic-package-to-contact',
                        'shipping-delivered-to-contact',
                        'pickup-approved',
                        'pickup-rejected',
                        'logistic-package-created-to-contact',
                        'logistic-package-shipment-tracking',
                         'logistic-shipping-created-to-contact',
                         'logistic-shipping-shipment-tracking',
                         'consolidation-shipment-tracking-to-contact',
                         'logistic-consolidation-created-to-contact'
                    ],
                ],

                 [
                    'name'      => 'Company Name ',
                    'key'       => '{company_name}',
                    'available' => [
          
                      
                    ],
                          'templates' => [
                             'package-delivered-to-contact',
                             'logistic-package-to-contact',
                             'shipping-delivered-to-contact',
                             'pickup-approved',
                             'pickup-rejected',
                            'logistic-package-created-to-contact',
                            'logistic-package-shipment-tracking',
                             'logistic-shipping-created-to-contact',
                             'logistic-shipping-shipment-tracking',
                             'consolidation-shipment-tracking-to-contact',
                             'logistic-consolidation-created-to-contact'
                          ],
                ],

            ];
    }


    /**
     * Merge fields for Contacts and Customers
     * @param  mixed $client_id
     * @param  string $contact_id
     * @param  string $password   password is used when sending welcome email, only 1 time
     * @return array
     */
    public function format($client_id, $contact_id = '')
    {
        $fields = [];

        if ($contact_id == '') {
            $contact_id = get_primary_contact_user_id($client_id);
        }

        $fields['{contact_firstname}']                 = '';
        $fields['{contact_lastname}']                  = '';
       

        if ($client_id == '') {
            return $fields;
        }

        $client = $this->ci->clients_model->get($client_id);

        if (!$client) {
            return $fields;
        }

        $this->ci->db->where('userid', $client_id);
        $this->ci->db->where('id', $contact_id);
        $contact = $this->ci->db->get(db_prefix() . 'contacts')->row();

        if ($contact) {
            $fields['{contact_firstname}']          = e($contact->firstname);
            $fields['{contact_lastname}']           = e($contact->lastname);
          
        }

        $fields['{company_name}']           = e($client->company);

        return $fields;
    }
}