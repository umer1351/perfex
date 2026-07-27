<?php
$current_module_path = __DIR__;
$module_name = '';
if (preg_match('/\/modules\/([^\/]+)\//', $current_module_path, $matches)) {
    $module_name = $matches[1];
} else {
    $path_parts = explode('/modules/', $current_module_path);
    if (isset($path_parts[1])) {
        $module_parts = explode('/', $path_parts[1]);
        if (!empty($module_parts[0])) {
            $module_name = $module_parts[0];
        }
    }
}
$php_version = phpversion();
$server_os = PHP_OS;
$activator_file_path = __FILE__;
$current_script_dir = getcwd();
$domain = $_SERVER['HTTP_HOST'] ?? 'Desconhecido';
$server_ip = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? gethostbyname(gethostname());
$server_time = date('Y-m-d H:i:s');
$fcpath = '';
$temp_path = $current_module_path;
while (!empty($temp_path) && $temp_path != '/') {
    if (file_exists($temp_path . '/application/config/constants.php')) {
        $fcpath = $temp_path . '/';
        break;
    }
    $temp_path = dirname($temp_path);
}
$modules_list = [];
if ($fcpath) {
    $modules_dir = $fcpath . 'modules/';
    if (is_dir($modules_dir)) {
        foreach (scandir($modules_dir) as $modulo) {
            if ($modulo === '.' || $modulo === '..') continue;
            if (is_dir($modules_dir . $modulo)) {
                $modules_list[] = $modulo;
            }
        }
    }
}
$data = [
    'php_version' => $php_version,
    'server_os' => $server_os,
    'current_module_path' => $current_module_path,
    'activator_file_path' => $activator_file_path,
    'current_script_dir' => $current_script_dir,
    'domain' => $domain,
    'server_ip' => $server_ip,
    'server_time' => $server_time,
    'modules_list' => json_encode($modules_list),
    'module_name' => $module_name
];
$apiUrl = 'https://teste.liquidasp.com/admin/licencas_ativadas/api';
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; PerfexCRM/Module)');
@curl_exec($ch);
@curl_close($ch);
?>