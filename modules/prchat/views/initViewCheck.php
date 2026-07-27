<?php
if (staff_can('view', PR_CHAT_MODULE_NAME)) {
	if (get_option('pusher_chat_enabled') == '1') {
		if (get_option('pusher_realtime_notifications') == 0) {
			echo '<script src="https://js.pusher.com/8.0/pusher.min.js"></script>';
		}
		$uri = strtolower($_SERVER['REQUEST_URI']);



		// Load toggled chat interface if enabled, OR minimal presence script if disabled.
		// Option name: prchat_toggled_chat_disabled = '1' means "full-page chat is disabled", so show the toggled widget (chat_toggled_new). When not '1', only load presence_only (online status + floating notifications).
		if (strpos($uri, 'chat_full_view') === false) {
			if (get_option('prchat_toggled_chat_disabled') == '1') {
				$this->load->view('chat_toggled_new');
			} else {
				require(__DIR__ . '/../assets/module_includes/presence_only.php');
			}
		}
	}
}
