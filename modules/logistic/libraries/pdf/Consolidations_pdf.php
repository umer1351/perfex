<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Consolidations_pdf extends App_pdf
{
    protected $consolidation;

    public function __construct($consolidation)
    {
        $consolidation                = hooks()->apply_filters('request_html_pdf_data', $consolidation);
        $GLOBALS['consolidation_pdf'] = $consolidation;

        parent::__construct();

        $this->consolidation = $consolidation;

        $this->SetTitle(_l('lg_consolidated'));
        # Don't remove these lines - important for the PDF layout
        $this->consolidation = $this->fix_editor_html($this->consolidation);
    }

    public function prepare()
    {
        $this->set_view_vars('consolidation', $this->consolidation);

        return $this->build();
    }

    protected function type()
    {
        return 'consolidation';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/logistic/views/consolidated/consolidationpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}