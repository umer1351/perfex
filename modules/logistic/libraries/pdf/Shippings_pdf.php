<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/pdf/App_pdf.php');

class Shippings_pdf extends App_pdf
{
    protected $shipping;

    public function __construct($shipping)
    {
        $shipping                = hooks()->apply_filters('request_html_pdf_data', $shipping);
        $GLOBALS['shipping_pdf'] = $shipping;

        parent::__construct();

        $this->shipping = $shipping;

        $this->SetTitle(_l('lg_shippings'));
        # Don't remove these lines - important for the PDF layout
        $this->shipping = $this->fix_editor_html($this->shipping);
    }

    public function prepare()
    {
        $this->set_view_vars('shipping', $this->shipping);

        return $this->build();
    }

    protected function type()
    {
        return 'shipping';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_requestpdf.php';
        $actualPath = APP_MODULES_PATH . '/logistic/views/shipping/shippingpdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}