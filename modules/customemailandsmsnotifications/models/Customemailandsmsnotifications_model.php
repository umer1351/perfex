<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customemailandsmsnotifications_model extends CI_Model {
	
	protected $table = '';
    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix().'custom_templates';
    }

    public function get($id = '',$where=[])
    {
        if(!empty($where) || $where != ''){
            $this->db->where($where);
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get($this->table)->row();
        }
        return $this->db->get($this->table)->result_array();
    }

    public function add($data){
    	$this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
	
    public function sendMail($request) {

        if (is_staff_logged_in() && !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }

        if($request['customer_or_leads'] == "customers"){

            $to =  $this->db->select('tblcontacts.*');
            $this->db->from('tblcontacts');
            $select_customer = isset($request['select_customer']) ? $request['select_customer'] : [];
            $this->db->where_in('userid', $select_customer);
            $this->db->where('active', '1');
            $to = $this->db->get()->result();
            
        }else{

            $to =  $this->db->select('tblleads.*');
            $this->db->from('tblleads');
            $select_lead = isset($request['select_lead']) ? $request['select_lead'] : [];
            $this->db->where_in('id', $select_lead);
            $to = $this->db->get()->result();

        }

        if (get_option('email_protocol') == "mail" || get_option('email_protocol') == "smtp") {

           $this->load->config('email');
            // Simulate fake template to be parsed
            $template           = new StdClass();
            $template->message  = get_option('email_header') . $request['message'] . get_option('email_footer');
            $template->fromname = get_option('companyname');
            $template->subject  = $request['subject'];

            $template = parse_email_template($template);

            hooks()->do_action('before_send_test_smtp_email');
            $this->email->initialize();
            if (get_option('mail_engine') == 'phpmailer') {
                
                $this->email->set_debug_output(function ($err) {
                    if (!isset($GLOBALS['debug'])) {
                        $GLOBALS['debug'] = '';
                    }
                    $GLOBALS['debug'] .= $err . '<br />';

                    return $err;
                });
                $this->email->set_smtp_debug(3);

            }

            $this->email->set_newline(config_item('newline'));
            $this->email->set_crlf(config_item('crlf'));

            $this->email->from(get_option('smtp_email'), $template->fromname);
            
            foreach ($to as $key => $t) {

                $template->message  = get_option('email_header') . $request['message'] . get_option('email_footer');
                $template = parse_email_template($template);

                $company = '';
                if ($request['customer_or_leads'] == 'customers' && isset($t->userid)) {
                    $company_query = $this->db->select('tblclients.company')
                        ->from('tblclients')
                        ->where('userid', $t->userid)
                        ->get()
                        ->result();
                    $company = isset($company_query[0]->company) ? $company_query[0]->company : '';
                }

                $dynamic_fields = array('{contact_firstname}','{contact_lastname}','{client_company}');

                foreach ($dynamic_fields as $key => $dynamic_field) {
                    
                    if ( str_contains($template->message,$dynamic_field) ) {
                        
                        switch ($dynamic_field) {

                            case '{contact_firstname}':
                                $template->message = str_replace($dynamic_field,$t->firstname,$template->message);
                                break;

                            case '{contact_lastname}':
                                $template->message = str_replace($dynamic_field,$t->lastname,$template->message);
                                break;

                            case '{client_company}':
                                $template->message = str_replace($dynamic_field,$company,$template->message);
                                break;

                        }

                    }

                    if ( str_contains($template->subject,$dynamic_field) ) {
                        
                        switch ($dynamic_field) {

                            case '{contact_firstname}':
                                $template->subject = str_replace($dynamic_field,$t->firstname,$template->subject);
                                break;

                            case '{contact_lastname}':
                                $template->subject = str_replace($dynamic_field,$t->lastname,$template->subject);
                                break;

                            case '{client_company}':
                                $template->subject = str_replace($dynamic_field,$company,$template->subject);
                                break;

                        }

                    }

                }
               
                $this->email->to($t->email);

                if (isset($_FILES['file_mail']) && !empty($_FILES['file_mail']['name'])) {
                    $file_tmp  = $_FILES['file_mail']['tmp_name'];
                    $file_name = $_FILES['file_mail']['name'];
                    $this->email->attach($file_tmp, 'attachment', $file_name);
                }

                $systemBCC = get_option('bcc_emails');

                if ($systemBCC != '') {
                    $this->email->bcc($systemBCC);
                }

                $this->email->subject($template->subject);
                $this->email->message($template->message);

                if ($this->email->send(true)) {
                    hooks()->do_action('smtp_test_email_success');
                    set_alert('success', _l('Message has been sent !'));

                    $activity_log_des = "Email sent to ".$t->email." , Message: ".$request['message'];

                    $data = array(
                            'description' => $activity_log_des,
                            'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                            'staffid' => (get_staff() ? get_staff()->firstname." ".get_staff()->lastname : 'System (Cron)'),
                    );

                    $this->db->insert('tblactivity_log', $data);
                    $this->db->where('id', $request['id']);
                    $this->db->update('tblcustom_email_sms', [
                        'is_delivered' => 1,
                    ]);
                    
                    $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                    $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                    $history_data = [
                        'message_type' => 'email',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_id,
                        'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                        'recipient_contact' => $t->email,
                        'subject' => $request['subject'],
                        'message_content' => $request['message'],
                        'template_id' => isset($request['template']) ? $request['template'] : null,
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                        'gateway' => get_option('email_protocol')
                    ];
                    $this->log_message_history($history_data);

                } else {

                    hooks()->do_action('smtp_test_email_failed');
                    set_alert('warning', _l('Message could not be sent!'));
                    
                    $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                    $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                    $history_data = [
                        'message_type' => 'email',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_id,
                        'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                        'recipient_contact' => $t->email,
                        'subject' => $request['subject'],
                        'message_content' => $request['message'],
                        'template_id' => isset($request['template']) ? $request['template'] : null,
                        'status' => 'failed',
                        'error_message' => 'Email send failed',
                        'gateway' => get_option('email_protocol')
                    ];
                    $this->log_message_history($history_data);

                }
            }

        } else {

            $this->load->library('encryption');

            $fromPass   = $this->encryption->decrypt(get_option('smtp_password'));
            $fromMail   = get_option('smtp_email');
            $host   = get_option('smtp_host');
            $port   = get_option('smtp_port');
            $charset   = get_option('smtp_email_charset');
            $secure   = get_option('smtp_encryption');

            $emailHeader = get_option('email_header');

            $mail = new PHPMailer();

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->isSMTP();

            $mail->Host = $host;

            $mail->Port = $port;

            $mail->SMTPAuth = true;

            $mail->SMTPSecure = $secure;

            $mail->Username = $fromMail;

            $mail->Password = $fromPass;
            
            $mail->setFrom($fromMail, get_option('companyname'));

            foreach ($to as $key => $t) {

                $mail->addBCC($t->email);

                $mail->addReplyTo($fromMail);

                $file_tmp  = $_FILES['file_mail']['tmp_name'];
                $file_name = $_FILES['file_mail']['name'];
               
                $mail->AddAttachment($file_tmp, $file_name);

                $mail->isHTML(true);

                $mail->Subject = $request['subject'];

                $mail->Body = get_option('email_header')."<strong>Message</strong><br><p style='text-align:center'>".$request['message']."</p>".get_option('email_footer');

                if (!$mail->send()) {

                    set_alert('warning', _l('Message could not be sent!'));
                    
                    $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                    $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                    $history_data = [
                        'message_type' => 'email',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_id,
                        'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                        'recipient_contact' => $t->email,
                        'subject' => $request['subject'],
                        'message_content' => $request['message'],
                        'template_id' => isset($request['template']) ? $request['template'] : null,
                        'status' => 'failed',
                        'error_message' => 'Email send failed',
                        'gateway' => get_option('email_protocol')
                    ];
                    $this->log_message_history($history_data);
                }
                else {
                    set_alert('success', _l('Message has been sent !'));

                    $activity_log_des = "Email sent to ".$t->email." , Message: ".$request['message'];

                    $data = array(
                            'description' => $activity_log_des,
                            'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                            'staffid' => (get_staff() ? get_staff()->firstname." ".get_staff()->lastname : 'System (Cron)'),
                    );

                    $this->db->insert('tblactivity_log', $data);
                    $this->db->where('id', $request['id']);
                    $this->db->update('tblcustom_email_sms', [
                        'is_delivered' => 1,
                    ]);
                    
                    $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                    $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                    $history_data = [
                        'message_type' => 'email',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_id,
                        'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                        'recipient_contact' => $t->email,
                        'subject' => $request['subject'],
                        'message_content' => $request['message'],
                        'template_id' => isset($request['template']) ? $request['template'] : null,
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                        'gateway' => get_option('email_protocol')
                    ];
                    $this->log_message_history($history_data);
                }
            }            
        }

    }

    public function sendSMS($request) {

        if (is_staff_logged_in() && !has_permission('customemailandsmsnotifications', '', 'create')) {
             access_denied(_l('sms_title'));
        }

        if( $request['customer_or_leads'] == "customers") {

            $to =  $this->db->select('tblcontacts.*');
            $this->db->from('tblcontacts');
            $select_customer = isset($request['select_customer']) ? $request['select_customer'] : [];
            $this->db->where_in('userid', $select_customer);
            $to = $this->db->get()->result();

        } else {

            $to =  $this->db->select('tblleads.*');
            $this->db->from('tblleads');
            $select_lead = isset($request['select_lead']) ? $request['select_lead'] : [];
            $this->db->where_in('id', $select_lead);
            $to = $this->db->get()->result();

        }
                
        if (get_option('sms_twilio_active') == 1) {

            $this->twilioSms($request,$to);
        }
        else if (get_option('sms_clickatell_active') == 1) {

            $this->clickatellSms($request,$to);
            
        }
        else if (get_option('sms_msg91_active') == 1) {

            $this->msg91Sms($request,$to);
        }
    }   

    public function twilioSms($request,$to) {
        if (is_staff_logged_in() && !has_permission('customemailandsmsnotifications', '', 'create')) {
             access_denied(_l('sms_title'));
        }
        $account_sid   = get_option('sms_twilio_account_sid');
        $auth_token   = get_option('sms_twilio_auth_token');
        $twilio_number   = get_option('sms_twilio_phone_number');

        $client = new Client($account_sid, $auth_token);

        foreach ($to as $key => $t) {
            $message = $client->messages->create(
                $t->phonenumber,
                array(
                    'from' => $twilio_number,
                    'body' => strip_tags($request['message'])
                )
            );

            if ($message->sid) {
                
                $activity_log_des = "SMS sent to ".$t->phonenumber." , Message: ".strip_tags($request['message']);

                $data = array(
                        'description' => $activity_log_des,
                        'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                        'staffid' => (get_staff() ? get_staff()->firstname." ".get_staff()->lastname : 'System (Cron)'),
                );

                $this->db->insert('tblactivity_log', $data);
                $this->db->where('id', $request['id']);
                $this->db->update('tblcustom_email_sms', [
                    'is_delivered' => 1,
                ]);
                
                set_alert('success', _l('Message has been sent !'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'gateway' => 'twilio'
                ];
                $this->log_message_history($history_data);
            }
            else {

                set_alert('warning', _l('Message could not be sent!'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'failed',
                    'error_message' => 'SMS send failed',
                    'gateway' => 'twilio'
                ];
                $this->log_message_history($history_data);
            }
        }

    }

    public function msg91Sms($request,$to) {
        
        foreach ($to as $key => $t) {

            $mobileNumber = $t->phonenumber;
            $message = urlencode(strip_tags($request['message']));

            if($this->sms_msg91->send($mobileNumber, $message)){
                
                $activity_log_des = "SMS sent to ".$t->phonenumber." , Message: ".strip_tags($request['message']);

                $data = array(
                        'description' => $activity_log_des,
                        'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                        'staffid' => (get_staff() ? get_staff()->firstname." ".get_staff()->lastname : 'System (Cron)'),
                );

                $this->db->insert('tblactivity_log', $data);
                $this->db->where('id', $request['id']);
                $this->db->update('tblcustom_email_sms', [
                    'is_delivered' => 1,
                ]);
                set_alert('success', _l('Message has been sent !'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'gateway' => 'msg91'
                ];
                $this->log_message_history($history_data);
            }
            else {

                set_alert('warning', _l('Message could not be sent!'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'failed',
                    'error_message' => 'SMS send failed',
                    'gateway' => 'msg91'
                ];
                $this->log_message_history($history_data);
            }
        }
    }
 
   public function clickatellSms($request, $to) {
    $clickatellApiKey = get_option('sms_clickatell_api_key');
    $clickatellApiUrl = 'https://platform.clickatell.com/messages/http/send';

    foreach ($to as $key => $t) {
        $company = '';
        if ($request['customer_or_leads'] == 'customers' && isset($t->userid)) {
            $company_row = $this->db->select('tblclients.company')
                                ->from('tblclients')
                                ->where('userid', $t->userid)
                                ->get()
                                ->row();
            $company = isset($company_row->company) ? $company_row->company : '';
        }

        $dynamic_fields = array('{contact_firstname}', '{contact_lastname}', '{client_company}');

        foreach ($dynamic_fields as $key => $dynamic_field) {
            if (str_contains($request['message'], $dynamic_field)) {
                switch ($dynamic_field) {
                    case '{contact_firstname}' :
                        $request['message'] = str_replace($dynamic_field, $t->firstname, $request['message']);
                        break;

                    case '{contact_lastname}' :
                        $request['message'] = str_replace($dynamic_field, $t->lastname, $request['message']);
                        break;

                    case '{client_company}' :
                        $request['message'] = str_replace($dynamic_field, $company, $request['message']);
                        break;
                }
            }
        }

        $apiKey = urlencode($clickatellApiKey);
        $toNumber = urlencode($t->phonenumber);
        $content = urlencode(strip_tags($request['message']));
        if($content == ''){
        $content = "Hii";
        }
        $url = "{$clickatellApiUrl}?apiKey={$apiKey}&to={$toNumber}&content={$content}";

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 202) {
                $activity_log_des = "SMS sent to {$t->phonenumber}, Message: " . strip_tags($request['message']);
                $data = array(
                    'description' => $activity_log_des,
                    'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                    'staffid' => (get_staff() ? get_staff()->firstname . " " . get_staff()->lastname : 'System (Cron)'),
                );

                $this->db->insert('tblactivity_log', $data);
                $this->db->where('id', $request['id']);
                $this->db->update('tblcustom_email_sms', [
                    'is_delivered' => 1,
                ]);
                set_alert('success', _l('Message has been sent!'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'gateway' => 'clickatell'
                ];
                $this->log_message_history($history_data);
            } else {
                set_alert('warning', _l('Message could not be sent!'));
                
                $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
                $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
                $history_data = [
                    'message_type' => 'sms',
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $recipient_id,
                    'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => strip_tags($request['message']),
                    'template_id' => isset($request['template']) ? $request['template'] : null,
                    'status' => 'failed',
                    'error_message' => 'SMS send failed',
                    'gateway' => 'clickatell'
                ];
                $this->log_message_history($history_data);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
            set_alert('warning', _l('Message could not be sent!'));
            
            $recipient_type = $request['customer_or_leads'] == 'customers' ? 'customer' : 'lead';
            $recipient_id = $recipient_type == 'customer' ? $t->userid : $t->id;
            $history_data = [
                'message_type' => 'sms',
                'recipient_type' => $recipient_type,
                'recipient_id' => $recipient_id,
                'recipient_name' => isset($t->firstname) ? trim($t->firstname . ' ' . $t->lastname) : (isset($t->name) ? $t->name : ''),
                'recipient_contact' => $t->phonenumber,
                'subject' => null,
                'message_content' => strip_tags($request['message']),
                'template_id' => isset($request['template']) ? $request['template'] : null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'gateway' => 'clickatell'
            ];
            $this->log_message_history($history_data);
        }
    }
}
    
    // ============================================
    // ENHANCED MERGE FIELDS SYSTEM
    // ============================================
    
    public function get_enhanced_merge_fields($recipient, $recipient_type = 'customer')
    {
        $staff = get_staff();
        $merge_fields = [
            '{contact_firstname}' => isset($recipient->firstname) ? $recipient->firstname : '',
            '{contact_lastname}' => isset($recipient->lastname) ? $recipient->lastname : '',
            '{contact_email}' => isset($recipient->email) ? $recipient->email : '',
            '{contact_phone}' => isset($recipient->phonenumber) ? $recipient->phonenumber : '',
            '{company_name}' => get_option('companyname'),
            '{current_date}' => date('Y-m-d'),
            '{current_time}' => date('H:i:s'),
            '{staff_firstname}' => isset($staff->firstname) ? $staff->firstname : '',
            '{staff_lastname}' => isset($staff->lastname) ? $staff->lastname : '',
            '{staff_email}' => isset($staff->email) ? $staff->email : ''
        ];
        
        if ($recipient_type == 'customer' && isset($recipient->userid)) {
            $client = $this->db->select('*')
                               ->from('tblclients')
                               ->where('userid', $recipient->userid)
                               ->get()
                               ->row();
            
            if ($client) {
                $merge_fields['{client_company}'] = isset($client->company) ? $client->company : '';
                $merge_fields['{client_vat_number}'] = isset($client->vat) ? $client->vat : '';
                $merge_fields['{client_address}'] = isset($client->address) ? $client->address : '';
                $merge_fields['{client_city}'] = isset($client->city) ? $client->city : '';
                $merge_fields['{client_state}'] = isset($client->state) ? $client->state : '';
                $merge_fields['{client_zip}'] = isset($client->zip) ? $client->zip : '';
                $merge_fields['{client_country}'] = isset($client->country) ? $client->country : '';
            }
        }
        
        return $merge_fields;
    }
    
    public function apply_merge_fields($text, $merge_fields)
    {
        foreach ($merge_fields as $field => $value) {
            $text = str_replace($field, $value, $text);
        }
        return $text;
    }
    
    // ============================================
    // MESSAGE HISTORY TRACKING
    // ============================================
    
    public function log_message_history($data)
    {
        $history_data = [
            'staff_id' => get_staff_user_id(),
            'message_type' => $data['message_type'],
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'],
            'recipient_name' => $data['recipient_name'],
            'recipient_contact' => $data['recipient_contact'],
            'subject' => isset($data['subject']) ? $data['subject'] : null,
            'message_content' => $data['message_content'],
            'template_id' => isset($data['template_id']) ? $data['template_id'] : null,
            'status' => isset($data['status']) ? $data['status'] : 'pending',
            'error_message' => isset($data['error_message']) ? $data['error_message'] : null,
            'scheduled_at' => isset($data['scheduled_at']) ? $data['scheduled_at'] : null,
            'sent_at' => isset($data['sent_at']) ? $data['sent_at'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'has_attachment' => isset($data['has_attachment']) ? $data['has_attachment'] : 0,
            'attachment_path' => isset($data['attachment_path']) ? $data['attachment_path'] : null,
            'gateway' => isset($data['gateway']) ? $data['gateway'] : null,
            'cost_estimate' => isset($data['cost_estimate']) ? $data['cost_estimate'] : null,
            'sms_segments' => isset($data['sms_segments']) ? $data['sms_segments'] : null
        ];
        
        $this->db->insert(db_prefix().'custom_message_history', $history_data);
        return $this->db->insert_id();
    }
    
    public function update_message_history_status($history_id, $status, $error_message = null, $sent_at = null)
    {
        $update_data = ['status' => $status];
        
        if ($error_message) {
            $update_data['error_message'] = $error_message;
        }
        
        if ($sent_at) {
            $update_data['sent_at'] = $sent_at;
        } else if ($status == 'sent') {
            $update_data['sent_at'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $history_id);
        $this->db->update(db_prefix().'custom_message_history', $update_data);
        
        return $this->db->affected_rows() > 0;
    }
    
    public function get_message_history($id)
    {
        return $this->db->where('id', $id)
                        ->get(db_prefix().'custom_message_history')
                        ->row();
    }
    
    public function get_message_count($status, $days = null)
    {
        $this->db->where('status', $status);
        
        if ($days) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $this->db->where('created_at >=', $date);
        }
        
        return $this->db->count_all_results(db_prefix().'custom_message_history');
    }
    
    public function get_messages_by_type($days = null)
    {
        $this->db->select('message_type, COUNT(*) as count');
        $this->db->from(db_prefix().'custom_message_history');
        
        if ($days) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $this->db->where('created_at >=', $date);
        }
        
        $this->db->group_by('message_type');
        return $this->db->get()->result_array();
    }
    
    public function get_messages_by_date($days = 30)
    {
        $this->db->select('DATE(created_at) as date, COUNT(*) as count, status');
        $this->db->from(db_prefix().'custom_message_history');
        
        $date = date('Y-m-d', strtotime("-{$days} days"));
        $this->db->where('DATE(created_at) >=', $date);
        
        $this->db->group_by('DATE(created_at), status');
        $this->db->order_by('date', 'ASC');
        
        return $this->db->get()->result_array();
    }
    
    public function get_delivery_rate($days = null)
    {
        $this->db->select('status, COUNT(*) as count');
        $this->db->from(db_prefix().'custom_message_history');
        
        if ($days) {
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            $this->db->where('created_at >=', $date);
        }
        
        $this->db->where_in('status', ['sent', 'failed']);
        $this->db->group_by('status');
        
        $results = $this->db->get()->result_array();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($results as $result) {
            if ($result['status'] == 'sent') {
                $sent = $result['count'];
            } else if ($result['status'] == 'failed') {
                $failed = $result['count'];
            }
        }
        
        $total = $sent + $failed;
        $rate = $total > 0 ? ($sent / $total) * 100 : 0;
        
        return [
            'sent' => $sent,
            'failed' => $failed,
            'total' => $total,
            'rate' => round($rate, 2)
        ];
    }
    
    public function delete_message_history($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'custom_message_history');
        return $this->db->affected_rows() > 0;
    }
    
    public function get_messages_for_export($filters = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix().'custom_message_history');
        
        if (!empty($filters['type'])) {
            $this->db->where('message_type', $filters['type']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        
        if (!empty($filters['staff_id'])) {
            $this->db->where('staff_id', $filters['staff_id']);
        }
        
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result_array();
    }
    
    public function resend_message($message)
    {
        // Implementation for resending a message
        // This would call the appropriate send method based on message type
        return true;
    }
    
    // ============================================
    // RECIPIENT GROUPS MANAGEMENT
    // ============================================
    
    public function get_recipient_groups($staff_id = null)
    {
        if ($staff_id) {
            $this->db->where('staff_id', $staff_id);
        }
        
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get(db_prefix().'custom_recipient_groups')->result();
    }
    
    public function get_recipient_group($id)
    {
        return $this->db->where('id', $id)
                        ->get(db_prefix().'custom_recipient_groups')
                        ->row();
    }
    
    public function add_recipient_group($data)
    {
        $this->db->insert(db_prefix().'custom_recipient_groups', $data);
        return $this->db->insert_id();
    }
    
    public function update_recipient_group($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'custom_recipient_groups', $data);
        return $this->db->affected_rows() > 0;
    }
    
    public function delete_recipient_group($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'custom_recipient_groups');
        return $this->db->affected_rows() > 0;
    }
    
    public function get_group_recipient_details($group)
    {
        $recipients = [];
        
        $customer_ids = json_decode($group->customer_ids, true);
        $lead_ids = json_decode($group->lead_ids, true);
        
        if (!empty($customer_ids)) {
            $this->db->select('tblcontacts.*, tblclients.company');
            $this->db->from('tblcontacts');
            $this->db->join('tblclients', 'tblclients.userid = tblcontacts.userid');
            $this->db->where_in('tblcontacts.userid', $customer_ids);
            $this->db->where('tblcontacts.active', 1);
            
            $customers = $this->db->get()->result();
            
            foreach ($customers as $customer) {
                $recipients[] = [
                    'type' => 'customer',
                    'name' => $customer->firstname . ' ' . $customer->lastname,
                    'email' => $customer->email,
                    'phone' => $customer->phonenumber,
                    'company' => $customer->company
                ];
            }
        }
        
        if (!empty($lead_ids)) {
            $this->db->select('*');
            $this->db->from('tblleads');
            $this->db->where_in('id', $lead_ids);
            
            $leads = $this->db->get()->result();
            
            foreach ($leads as $lead) {
                $recipients[] = [
                    'type' => 'lead',
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phonenumber,
                    'company' => $lead->company
                ];
            }
        }
        
        return $recipients;
    }
    
    // ============================================
    // TEMPLATE CATEGORIES
    // ============================================
    
    public function get_template_categories()
    {
        $this->db->order_by('sort_order', 'ASC');
        return $this->db->get(db_prefix().'custom_template_categories')->result_array();
    }
    
    public function get_template_category($id)
    {
        return $this->db->where('id', $id)
                        ->get(db_prefix().'custom_template_categories')
                        ->row();
    }
    
    public function add_template_category($data)
    {
        $this->db->insert(db_prefix().'custom_template_categories', $data);
        return $this->db->insert_id();
    }
    
    public function update_template_category($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'custom_template_categories', $data);
        return $this->db->affected_rows() > 0;
    }
    
    public function delete_template_category($id)
    {
        // First, set category_id to NULL for templates using this category
        $this->db->where('category_id', $id);
        $this->db->update(db_prefix().'custom_templates', ['category_id' => null]);
        
        // Then delete the category
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'custom_template_categories');
        return $this->db->affected_rows() > 0;
    }
    
    public function get_templates_by_category($category_id)
    {
        $this->db->where('category_id', $category_id);
        return $this->db->get(db_prefix().'custom_templates')->result_array();
    }
    
    // ============================================
    // SCHEDULED MESSAGES MANAGEMENT
    // ============================================
    
    public function get_scheduled_message_count()
    {
        return $this->db->where('is_delivered', 0)
                        ->count_all_results(db_prefix().'custom_email_sms');
    }
    
    public function get_upcoming_messages($hours = 24)
    {
        $now = date('Y-m-d H:i:s');
        $future = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
        
        $this->db->select('*');
        $this->db->from(db_prefix().'custom_email_sms');
        $this->db->where('is_delivered', 0);
        $this->db->where("CONCAT(custom_date, ' ', IFNULL(custom_time, '00:00:00')) BETWEEN '{$now}' AND '{$future}'", null, false);
        $this->db->order_by("CONCAT(custom_date, ' ', IFNULL(custom_time, '00:00:00'))", 'ASC', false);
        
        return $this->db->get()->result();
    }
    
    public function get_scheduled_message($id)
    {
        return $this->db->where('id', $id)
                        ->get(db_prefix().'custom_email_sms')
                        ->row();
    }
    
    public function update_scheduled_message($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix().'custom_email_sms', $data);
        return $this->db->affected_rows() > 0;
    }
    
    public function delete_scheduled_message($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'custom_email_sms');
        return $this->db->affected_rows() > 0;
    }
    
    public function get_all_scheduled_messages_for_calendar()
    {
        $this->db->select('id, customer_or_leads, subject, message, mail_or_sms, custom_date, custom_time, is_delivered');
        $this->db->from(db_prefix().'custom_email_sms');
        $this->db->where('is_delivered', 0);
        $this->db->order_by('custom_date', 'ASC');
        $this->db->order_by('IFNULL(custom_time, "00:00:00")', 'ASC', false);
        
        return $this->db->get()->result();
    }
    
    // ============================================
    // BATCHED SCHEDULED SENDING (CRON)
    // ============================================

    /**
     * Recipients processed per cron run when the option is missing or invalid.
     */
    const CRON_BATCH_SIZE_DEFAULT = 75;

    /**
     * Entry point invoked from the after_cron_run hook.
     *  1) Expands any due scheduled message into one queue row per recipient (once each).
     *  2) Sends up to N pending recipients this run.
     *  3) Marks a scheduled message delivered once none of its recipients are pending.
     * Large groups are therefore spread across multiple cron runs and resume safely.
     */
    public function process_scheduled_batches()
    {
        $batch_size = (int) get_option('customemailandsmsnotifications_cron_batch_size');
        if ($batch_size <= 0) {
            $batch_size = self::CRON_BATCH_SIZE_DEFAULT;
        }

        $this->expand_due_schedules();

        $pending = $this->db->where('status', 'pending')
                            ->order_by('id', 'ASC')
                            ->limit($batch_size)
                            ->get(db_prefix().'custom_email_sms_queue')
                            ->result();

        foreach ($pending as $row) {
            $this->process_queue_row($row);
        }

        $this->finalize_completed_schedules();
    }

    /**
     * Turn each due, not-yet-queued scheduled message into one queue row per recipient.
     */
    private function expand_due_schedules()
    {
        $now = date('Y-m-d H:i');

        $schedules = $this->db->where('is_delivered', 0)
                              ->where('(is_queued IS NULL OR is_queued = 0)', null, false)
                              ->get(db_prefix().'custom_email_sms')
                              ->result();

        foreach ($schedules as $schedule) {
            $scheduled_at = date('Y-m-d H:i', strtotime(trim($schedule->custom_date.' '.$schedule->custom_time)));
            if ($now < $scheduled_at) {
                continue; // not due yet
            }

            $ids = json_decode($schedule->select_customer, true);
            if (!is_array($ids)) {
                $ids = [];
            }
            $recipient_type = $schedule->customer_or_leads == 'customers' ? 'customer' : 'lead';

            $rows = [];
            foreach ($ids as $recipient_id) {
                $recipient_id = (int) $recipient_id;
                if ($recipient_id <= 0) {
                    continue;
                }
                $rows[] = [
                    'schedule_id'    => $schedule->id,
                    'recipient_type' => $recipient_type,
                    'recipient_id'   => $recipient_id,
                    'status'         => 'pending',
                    'attempts'       => 0,
                    'created_at'     => date('Y-m-d H:i:s'),
                ];
            }

            if (!empty($rows)) {
                $this->db->insert_batch(db_prefix().'custom_email_sms_queue', $rows);
            }

            // Never expand this schedule again. With no valid recipients, complete it now
            // so it does not sit in the "scheduled" list forever.
            $update = ['is_queued' => 1];
            if (empty($rows)) {
                $update['is_delivered'] = 1;
                $update['delivered_at'] = date('Y-m-d H:i:s');
            }
            $this->db->where('id', $schedule->id)
                     ->update(db_prefix().'custom_email_sms', $update);
        }
    }

    /**
     * Send to a single queued recipient by reusing the existing send methods with a
     * one-element recipient list. The sentinel id (-1) stops those methods from
     * flipping the whole scheduled row to delivered; completion is handled centrally
     * in finalize_completed_schedules() once every recipient is processed.
     */
    private function process_queue_row($row)
    {
        $schedule = $this->db->where('id', $row->schedule_id)
                             ->get(db_prefix().'custom_email_sms')
                             ->row();

        if (!$schedule) {
            // Scheduled message was deleted; drop the orphaned queue row.
            $this->db->where('id', $row->id)
                     ->delete(db_prefix().'custom_email_sms_queue');
            return;
        }

        $request = [
            'id'                => -1,
            'customer_or_leads' => $schedule->customer_or_leads,
            'template'          => $schedule->template,
            'subject'           => $schedule->subject,
            'message'           => $schedule->message,
            'mail_or_sms'       => $schedule->mail_or_sms,
        ];

        if ($schedule->customer_or_leads == 'leads') {
            $request['select_lead'] = [$row->recipient_id];
        } else {
            $request['select_customer'] = [$row->recipient_id];
        }

        // Re-attach a stored file for email sends, preserving any existing $_FILES state.
        $had_files   = isset($_FILES['file_mail']);
        $saved_files = $had_files ? $_FILES['file_mail'] : null;
        if ($schedule->mail_or_sms == 'mail' && !empty($schedule->file_mail)) {
            $file = json_decode($schedule->file_mail, true);
            if ($file && isset($file['tmp_name'])) {
                $_FILES['file_mail']['tmp_name'] = $file['tmp_name'];
                $_FILES['file_mail']['name']     = $file['name'];
            }
        }

        // High-water mark in message history so we can read back this send's outcome.
        $max_row   = $this->db->select_max('id', 'mid')
                              ->get(db_prefix().'custom_message_history')
                              ->row();
        $before_id = (int) ($max_row ? $max_row->mid : 0);

        $error = null;
        try {
            if ($schedule->mail_or_sms == 'mail') {
                $this->sendMail($request);
            } else {
                $this->sendSMS($request);
            }
        } catch (\Throwable $e) {
            // Catch Errors too (not just Exceptions) so one bad recipient cannot abort
            // the whole cron batch; this row is marked failed and the run continues.
            $error = $e->getMessage();
        }

        // Restore $_FILES to whatever it was before this row was processed.
        if ($had_files) {
            $_FILES['file_mail'] = $saved_files;
        } else {
            unset($_FILES['file_mail']);
        }

        if ($error !== null) {
            $this->mark_queue_row($row, 'failed', $error);
            return;
        }

        // The reused send method logs exactly one history row for this recipient.
        $sent = $this->db->where('id >', $before_id)
                         ->where('recipient_id', $row->recipient_id)
                         ->where('status', 'sent')
                         ->count_all_results(db_prefix().'custom_message_history');

        if ($sent > 0) {
            $this->mark_queue_row($row, 'sent');
        } else {
            $this->mark_queue_row($row, 'failed', 'Send failed (see message history)');
        }
    }

    /**
     * Persist a queue row outcome (status, attempt count, timestamp, error).
     */
    private function mark_queue_row($row, $status, $error = null)
    {
        $data = [
            'status'   => $status,
            'attempts' => (int) $row->attempts + 1,
        ];
        if ($status === 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        if ($error !== null) {
            $data['error_message'] = $error;
        }
        $this->db->where('id', $row->id)
                 ->update(db_prefix().'custom_email_sms_queue', $data);
    }

    /**
     * Mark scheduled messages delivered once they have no pending recipients left.
     * Failed recipients still count as processed so a schedule cannot hang forever;
     * they remain visible in the queue/history for manual inspection.
     */
    private function finalize_completed_schedules()
    {
        $rows = $this->db->query(
            'SELECT s.id FROM `'.db_prefix().'custom_email_sms` s
             WHERE s.is_delivered = 0 AND s.is_queued = 1
             AND NOT EXISTS (
                 SELECT 1 FROM `'.db_prefix().'custom_email_sms_queue` q
                 WHERE q.schedule_id = s.id AND q.status = "pending"
             )'
        )->result();

        foreach ($rows as $r) {
            $this->db->where('id', $r->id)
                     ->update(db_prefix().'custom_email_sms', [
                         'is_delivered' => 1,
                         'delivered_at' => date('Y-m-d H:i:s'),
                     ]);
        }
    }

    // ============================================
    // WHATSAPP INTEGRATION (via Twilio)
    // ============================================
    
    public function sendWhatsApp($request, $to)
    {
        if (is_staff_logged_in() && !has_permission('customemailandsmsnotifications', '', 'create')) {
            access_denied(_l('sms_title'));
        }
        
        require_once(FCPATH.'application/vendor/twilio/sdk/src/Twilio/autoload.php');
        
        $account_sid = get_option('sms_whatsapp_account_sid');
        $auth_token = get_option('sms_whatsapp_auth_token');
        $whatsapp_number = get_option('sms_whatsapp_phone_number');
        
        if (!$account_sid || !$auth_token || !$whatsapp_number) {
            set_alert('warning', _l('whatsapp_not_configured'));
            return false;
        }
        
        $client = new \Twilio\Rest\Client($account_sid, $auth_token);
        
        foreach ($to as $key => $t) {
            try {
                // Get enhanced merge fields
                $recipient_type = isset($t->userid) ? 'customer' : 'lead';
                $merge_fields = $this->get_enhanced_merge_fields($t, $recipient_type);
                
                // Apply merge fields to message
                $message_content = $this->apply_merge_fields($request['message'], $merge_fields);
                
                // Format phone number for WhatsApp (must include country code and 'whatsapp:' prefix)
                $to_number = 'whatsapp:' . $t->phonenumber;
                $from_number = 'whatsapp:' . $whatsapp_number;
                
                $message = $client->messages->create(
                    $to_number,
                    array(
                        'from' => $from_number,
                        'body' => strip_tags($message_content)
                    )
                );
                
                if ($message->sid) {
                    // Log to message history
                    $history_data = [
                        'message_type' => 'whatsapp',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_type == 'customer' ? $t->userid : $t->id,
                        'recipient_name' => $t->firstname . ' ' . $t->lastname,
                        'recipient_contact' => $t->phonenumber,
                        'subject' => null,
                        'message_content' => $message_content,
                        'template_id' => (isset($request['template']) ? $request['template'] : null),
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                        'gateway' => 'whatsapp_twilio'
                    ];
                    
                    $this->log_message_history($history_data);
                    
                    // Log activity
                    $activity_log_des = "WhatsApp message sent to " . $t->phonenumber . ", Message: " . strip_tags($message_content);
                    $data = array(
                        'description' => $activity_log_des,
                        'date' => gmdate('Y-m-d h:i:s \G\M\T'),
                        'staffid' => (get_staff() ? get_staff()->firstname . " " . get_staff()->lastname : 'System (Cron)'),
                    );
                    
                    $this->db->insert('tblactivity_log', $data);
                    
                    // Update scheduled message if applicable
                    if (isset($request['id'])) {
                        $this->db->where('id', $request['id']);
                        $this->db->update('tblcustom_email_sms', [
                            'is_delivered' => 1,
                            'delivered_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    
                    set_alert('success', _l('whatsapp_sent_successfully'));
                } else {
                    // Log failed message
                    $history_data = [
                        'message_type' => 'whatsapp',
                        'recipient_type' => $recipient_type,
                        'recipient_id' => $recipient_type == 'customer' ? $t->userid : $t->id,
                        'recipient_name' => $t->firstname . ' ' . $t->lastname,
                        'recipient_contact' => $t->phonenumber,
                        'subject' => null,
                        'message_content' => $message_content,
                        'template_id' => (isset($request['template']) ? $request['template'] : null),
                        'status' => 'failed',
                        'error_message' => 'No SID returned',
                        'gateway' => 'whatsapp_twilio'
                    ];
                    
                    $this->log_message_history($history_data);
                    set_alert('warning', _l('whatsapp_send_failed'));
                }
            } catch (Exception $e) {
                // Log failed message
                $history_data = [
                    'message_type' => 'whatsapp',
                    'recipient_type' => (isset($recipient_type) ? $recipient_type : 'unknown'),
                    'recipient_id' => 0,
                    'recipient_name' => $t->firstname . ' ' . $t->lastname,
                    'recipient_contact' => $t->phonenumber,
                    'subject' => null,
                    'message_content' => $request['message'],
                    'template_id' => (isset($request['template']) ? $request['template'] : null),
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'gateway' => 'whatsapp_twilio'
                ];
                
                $this->log_message_history($history_data);
                
                set_alert('warning', _l('whatsapp_send_failed') . ': ' . $e->getMessage());
            }
        }
        
        return true;
    }
}

/* End of file Customemailandsmsnotifications_model.php */
