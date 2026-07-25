<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Packages_pdf extends App_pdf
{
    protected $package;

    public function __construct($package)
    {
        $package                = hooks()->apply_filters('request_html_pdf_data', $package);
        $GLOBALS['package_pdf'] = $package;

        parent::__construct();

        $this->package = $package;

        $this->SetTitle(_l('lg_packages'));
        # Don't remove these lines - important for the PDF layout
        $this->package = $this->fix_editor_html($this->package);
    }

    public function prepare()
    {
        $this->set_view_vars('package', $this->package);

        return $this->build();
    }

    protected function type()
    {
        return 'package';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/logistic/views/packages/packagepdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}