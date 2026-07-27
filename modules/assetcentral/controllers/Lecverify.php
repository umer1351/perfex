<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once __DIR__ .'/../libraries/leclib.php';

/**
 * LenzCreative verify
 */
class Lecverify extends AdminController{
    public function __construct(){
        parent::__construct();
    }

    /**
     * index
     * @return void
     */
    public function index(){
        show_404();
    }

    /**
     * activate
     * @return json
     */
    public function activate()
    {
        // Simulando uma resposta de ativação bem-sucedida
        $res = array();
        $res['status'] = true;
        $res['message'] = 'Module activated successfully';
        $res['original_url'] = $this->input->post('original_url');

        echo json_encode($res);
    }
}
