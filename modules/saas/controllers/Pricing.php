<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Pricing extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->use_footer     = false;
        $this->use_navigation = false;
        $this->use_submenu    = false;

        $this->app_modules->is_inactive('saas') ? access_denied() : '';

        if (IS_TENANT) {
            redirect(site_url());
        }
    }

    public function index()
    {
        $data = [];
        // if staff or admin logged in then redirect to dashborad page
        if (is_staff_logged_in()) {
            redirect(site_url('admin'));
        }

        // module is not enabled and Landing page option is not enable then redirect to login page
        if (('1' != get_option('superadmin_enabled')) && ('1' != get_option('tenants_landing'))) {
            set_alert('warning', _l('access_denied'));
            redirect(site_url());
        }

        $data['title']         = _l('landing');
        $data['announcements'] = '';

        $data['displaySettings'] = [
            'heroBanner'   => true,
            'heroFeature'  => true,
            'features'     => true,
            'includes'     => true,
            'testimonials' => true,
            'partners'     => true,
            'getApp'       => true,
            'footerMenus'  => true,
        ];

        $this->load->model('Superadmin_model', 'saas_model');
        $data['plans'] = $this->saas_model->get_saas_plan();

        $this->load->config('features_limitation_config');
        $data['limitations'] = config_item('limitations');

        $this->data($data);
        $this->view('landing/front');
        $this->layout(false);
    }

    public function layout($notInThemeViewFiles = false)
    {
        /*
         * Navigation and submenu
         * @deprecated 2.3.2
         * @var boolean
         */

        $this->data['use_navigation'] = true == $this->use_navigation;
        $this->data['use_submenu']    = true == $this->use_submenu;

        /*
         * @since  2.3.2 new variables
         * @var array
         */
        $this->data['navigationEnabled'] = true == $this->use_navigation;
        $this->data['subMenuEnabled']    = true == $this->use_submenu;

        /*
         * Theme head file
         * @var string
         */
        $this->template['head'] = $this->load->view(SUPERADMIN_MODULE.'/front_theme/head', $this->data, true);

        $GLOBALS['customers_head'] = $this->template['head'];

        /**
         * Load the template view.
         *
         * @var string
         */
        $module                       = CI::$APP->router->fetch_module();
        $this->data['current_module'] = $module;
        $viewPath                     = null !== $module || $notInThemeViewFiles ?
        $this->view :
        $this->createThemeViewPath($this->view);

        $this->template['view']    = $this->load->view($viewPath, $this->data, true);
        $GLOBALS['customers_view'] = $this->template['view'];

        /*
         * Load the theme compiled template
         */
        $this->load->view(SUPERADMIN_MODULE.'/front_theme/index', $this->template);
    }
}

/* End of file Pricing.php */
/* Location: ./modules/saas/controllers/Pricing.php */
