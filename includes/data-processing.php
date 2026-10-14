<?php
if (!defined('ABSPATH')) { exit; }
function adb_defaults() {
    return [
        'database_url'=>'', 'database2_url'=>'', 'api_key'=>'', 'api2_key'=>'',
        'stylesheet'=>'standard', 'sortering'=>'titel', 'sogplacering'=>'2',
        'visnavn'=>'0', 'debug'=>'0', 'featherlight'=>'ekstern',
        'resultatvisning'=>array_fill_keys(['billede','titel','periode','arkiv','materialetype','placering','registreringsdato'], '0'),
    ];
}
function adb_get_options() {
    $saved = get_option('adb_settings', []);
    $defaults = adb_defaults();
    $options = array_replace($defaults, is_array($saved) ? $saved : []);
    $options['resultatvisning'] = array_replace($defaults['resultatvisning'], is_array($options['resultatvisning']) ? $options['resultatvisning'] : []);
    return adb_sanitize_settings($options);
}
function adb_choice($value, $choices, $default) {
    return is_string($value) && in_array($value, $choices, true) ? $value : $default;
}
function adb_sanitize_settings($input) {
    $input = is_array($input) ? $input : [];
    $result = adb_defaults();
    foreach (['database_url','database2_url'] as $key) {
        $result[$key] = esc_url_raw(is_string($input[$key] ?? null) ? $input[$key] : '', ['http','https']);
    }
    foreach (['api_key','api2_key'] as $key) {
        $result[$key] = is_string($input[$key] ?? null) ? trim($input[$key]) : '';
    }
    foreach (['debug','visnavn'] as $key) { $result[$key] = ($input[$key] ?? '') === '1' ? '1' : '0'; }
    $result['stylesheet'] = adb_choice($input['stylesheet'] ?? '', ['standard','valby'], 'standard');
    $result['sortering'] = adb_choice($input['sortering'] ?? '', ['titel','periode','arkiv','materialetype','placering','registreringsdato'], 'titel');
    $result['sogplacering'] = adb_choice($input['sogplacering'] ?? '', ['1','2','3'], '2');
    $result['featherlight'] = adb_choice($input['featherlight'] ?? '', ['featherlight','ekstern'], 'ekstern');
    $fields = is_array($input['resultatvisning'] ?? null) ? $input['resultatvisning'] : [];
    foreach ($result['resultatvisning'] as $key=>$value) { $result['resultatvisning'][$key] = ($fields[$key] ?? '') === '1' ? '1' : '0'; }
    return $result;
}
function adb_input($source, $key, $default = '') {
    return isset($source[$key]) && is_string($source[$key]) ? sanitize_text_field(wp_unslash($source[$key])) : $default;
}
function adb_text($data, $key) {
    return is_scalar($data[$key] ?? null) ? (string)$data[$key] : '';
}
