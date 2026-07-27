<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
    "use strict";

    const PERFEX_SAAS_MODULE_NAME = '<?= PERFEX_SAAS_MODULE_NAME ?>';
    const PERFEX_SAAS_FILTER_TAG = '<?= PERFEX_SAAS_FILTER_TAG; ?>';
    const PERFEX_SAAS_IS_TENANT = <?= perfex_saas_is_tenant() ? 'true' : 'false'; ?>;
</script>

<!-- Add NProgress to spice loading -->
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css' />

<!-- Module custom admin script -->
<script src="<?= module_dir_url(PERFEX_SAAS_MODULE_NAME, 'assets/js/admin.js'); ?>"></script>