<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client extends ClientsController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('aiwriter_model');
    }

    public function index()
    {
        if(get_option('aiwriter_allow_for_client') !='1'):
            access_denied('aiwriter');
        endif;

        if (!is_client_logged_in()) {
            if(get_option('aiwriter_allow_for_client_without_login') !='1'):
                redirect(site_url('authentication/login'));
            endif;
        }

        $data['title'] = _l('aiwriter');
        $this->view('client/writer');
        $this->data($data);
        $this->layout();
    }

    public function ajaxAiContent(){

        if(get_option('aiwriter_allow_for_client') !='1'):
            echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
            exit();
        endif;

        if (!is_client_logged_in()) {
            if(get_option('aiwriter_allow_for_client_without_login') !='1'):
                echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong')]);
                exit();
            endif;
        }

        $text  = '';
        $option['keyword']            = $this->input->post('primary_keyword');
        $option['usage_case']         = $this->input->post('usage_case');
        $option['numberVariant']      = $this->input->post('no_of_varient');
        $openai_response = $this->aiwriter_model->get_ajax_ai_content($option);
        if(array_key_exists("choices",$openai_response)):
            foreach ($openai_response['choices'] as $choice):
                $text .= $choice['text'];
            endforeach;
            $text = ltrim($text ?? '');
            echo json_encode(['status'=>true, 'message'=> _l('content_generated'),'data'=>$text]);
        elseif(array_key_exists("error",$openai_response)):
            echo json_encode($openai_response['error']);
        else:
            echo json_encode(['status'=>false, 'message'=>_l('something_went_wrong'),'data'=>$text]);
        endif;

    }

    
}
