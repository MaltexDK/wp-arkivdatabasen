<?php
if (!defined('ABSPATH')) { exit; }
function adb_security($arkiv = 1) {
    global $adb_options;
    $timestamp = gmdate('U');
    return http_build_query(['timestamp'=>$timestamp, 'signatur'=>md5($adb_options[$arkiv === 2 ? 'api2_key' : 'api_key'].$timestamp)], '', '&');
}
function adb_api_url($action, $params = [], $arkiv = 1) {
    global $adb_options;
    $url = $adb_options[$arkiv === 2 ? 'database2_url' : 'database_url'];
    if (!$url) { return ''; }
    parse_str(adb_security($arkiv), $security);
    return add_query_arg(array_map('rawurlencode', array_merge($params, $security, ['action'=>$action])), $url);
}
function adb_api_request($action, $params = [], $arkiv = 1) {
    $url = adb_api_url($action, $params, $arkiv);
    if (!$url) { return new WP_Error('adb_configuration', 'Arkivet er ikke konfigureret.'); }
    // Preserve the archive API's existing signature and supported legacy encoding.
    $response = wp_remote_get($url, ['timeout'=>$action === 'get_search' ? 120 : 15]);
    if (is_wp_error($response)) { return $response; }
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return new WP_Error('adb_http', 'Databasen svarer ikke. Prøv igen senere.');
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_UTF8) {
        $body = iconv('ISO-8859-1', 'UTF-8', $body);
        $data = $body === false ? null : json_decode($body, true);
    }
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return new WP_Error('adb_json', 'Arkivet returnerede et ugyldigt svar.');
    }
    return $data;
}
function adb_security_check($arkiv = 1) {
    $data = adb_api_request('get_check', [], $arkiv);
    if (is_wp_error($data) || !isset($data['error']) || !is_scalar($data['error'])) { return false; }
    if (empty($data['error']) && !isset($data['arkivnavn'])) { return false; }
    return $data;
}
function adb_security_checkurl($arkiv = 1) { return adb_api_url('get_check', [], $arkiv); }
function adb_page_url($params = []) {
    return add_query_arg(array_map('rawurlencode', $params), get_permalink() ?: home_url('/'));
}
function adb_error_html($data) {
    $message = is_wp_error($data) ? $data->get_error_message() : adb_text($data, 'errormessage');
    return '<p class="adb-error">'.esc_html($message ?: 'Arkivet ser ikke ud til at være korrekt opsat.').'</p>';
}
function adb_search_form($string, $method, $sort, $second, $secondary_name) {
    $html = '<form class="adb-search" name="searchform" action="'.esc_url(adb_page_url(['action'=>'get_search'])).'" method="post">';
    $html .= '<input type="hidden" name="sortering" value="'.esc_attr($sort).'">';
    $html .= '<p><label>Søg i arkivet <input type="text" name="string" size="40" required value="'.esc_attr($string).'"></label> <button type="submit">Søg</button></p>';
    foreach (['string'=>'Som sætning','and'=>'Alle ordene','or'=>'Mindst et af ordene','emneord'=>'Som emneord'] as $value=>$label) {
        $html .= '<label><input type="radio" name="method" value="'.esc_attr($value).'" '.checked($method, $value, false).'> '.esc_html($label).'</label><br>';
    }
    if ($secondary_name !== '') {
        $html .= '<label><input type="checkbox" name="usesecond" value="1" '.checked($second, true, false).'> Søg i alternativ samling: '.esc_html($secondary_name).'</label>';
    }
    return $html.'</form>';
}
function adb_results($rows, $sort, $second) {
    global $adb_options;
    $fields = ['titel'=>'Titel','periode'=>'Periode','arkiv'=>'Arkiv','materialetype'=>'Materialetype','placering'=>'Placering','registreringsdato'=>'Registreringsdato'];
    $html = '<p>Søgningen gav <strong>'.count($rows).'</strong> resultater.</p><table class="sortable adb-results" data-sort="'.esc_attr($sort).'"><thead><tr>';
    if ($adb_options['resultatvisning']['billede'] === '1') { $html .= '<th class="sorttable_nosort">Billede</th>'; }
    foreach ($fields as $key=>$label) {
        if ($key === 'titel' || $adb_options['resultatvisning'][$key] === '1') { $html .= '<th data-sort="'.esc_attr($key).'">'.esc_html($label).'</th>'; }
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $url = adb_page_url(['action'=>'get_details','id'=>adb_text($row,'id'),'arkivnummer'=>adb_text($row,'arkivnummer'),'sortering'=>$sort,'usesecond'=>$second ? '1' : '0']);
        $html .= '<tr>';
        if ($adb_options['resultatvisning']['billede'] === '1') {
            $thumbnail = adb_text($row,'thumbnail') ?: plugins_url('images/noimage.gif', ADB_FILE);
            $html .= '<td><a href="'.esc_url($url).'"><img class="adb_thumbnail" src="'.esc_url($thumbnail).'" alt="'.esc_attr(adb_text($row,'navn')).'"></a></td>';
        }
        foreach ($fields as $key=>$label) {
            if ($key !== 'titel' && $adb_options['resultatvisning'][$key] !== '1') { continue; }
            $value = adb_text($row, $key === 'registreringsdato' ? 'dato' : $key);
            if ($key === 'titel') { $value = '<a href="'.esc_url($url).'">'.esc_html(adb_text($row,'navn')).'</a>'; }
            elseif ($key === 'periode') {
                $start = substr(adb_text($row,'periode_start'), 6, 4);
                $end = substr(adb_text($row,'periode_slut'), 6, 4);
                $value = esc_html($start === $end ? $start : $start.' - '.$end);
            } else { $value = esc_html($value); }
            $html .= '<td>'.$value.'</td>';
        }
        $html .= '</tr>';
    }
    return $html.'</tbody></table>';
}
function adb_details($data, $sort, $second) {
    global $adb_options;
    $html = '<h3>'.esc_html(adb_text($data,'Titel')).'</h3>';
    if (adb_text($data,'Titel') !== adb_text($data,'Forklaring')) { $html .= wpautop(esc_html(adb_text($data,'Forklaring'))); }
    $html .= '<div class="adb_img_container">';
    foreach (is_array($data['Billeder'] ?? null) ? $data['Billeder'] : [] as $image) {
        if (!is_array($image)) { continue; }
        $html .= '<div class="adb_img_large"><a href="'.esc_url(adb_text($image,'url')).'"'.($adb_options['featherlight'] === 'featherlight' ? ' data-adb-lightbox' : '').'><img src="'.esc_url(adb_text($image,'thumpnail') ?: adb_text($image,'thumbnail')).'" alt="'.esc_attr(adb_text($data,'Titel')).'"></a></div>';
    }
    $html .= '</div><h4>Standardinformation</h4><table>';
    $values = ['Registreringsnummer'=>adb_text($data,'Registreringsnummer'),'Registreringsdato'=>adb_text($data,'Registreringsdato'),'Tidsperiode'=>adb_text($data,'periode_start').' - '.adb_text($data,'periode_slut'),'Placering'=>adb_text($data,'Placering')];
    foreach ($values as $key=>$value) { $html .= '<tr><td>'.esc_html($key).'</td><td>'.esc_html($value).'</td></tr>'; }
    $html .= '<tr><td>Arkiv</td><td><a href="'.esc_url(adb_text($data,'Hjemmeside')).'" target="_blank" rel="noopener noreferrer">'.esc_html(adb_text($data,'Arkiv')).'</a></td></tr></table>';
    foreach (['Filer'=>'filnavn','Links'=>'titel'] as $key=>$label) {
        if (empty($data[$key]) || !is_array($data[$key])) { continue; }
        $html .= '<h4>'.esc_html($key).'</h4><ul>';
        foreach ($data[$key] as $item) {
            if (is_array($item)) { $html .= '<li><a href="'.esc_url(adb_text($item,'url')).'" target="_blank" rel="noopener noreferrer">'.esc_html(adb_text($item,$label)).'</a></li>'; }
        }
        $html .= '</ul>';
    }
    $html .= '<h4>'.esc_html(adb_text($data,'Registreringstype')).'</h4><table>';
    foreach (is_array($data['Registreringstypeindhold'] ?? null) ? $data['Registreringstypeindhold'] : [] as $key=>$value) {
        if (is_scalar($value) && $value !== '') { $html .= '<tr><td>'.esc_html($key).'</td><td>'.esc_html((string)$value).'</td></tr>'; }
    }
    $html .= '</table>';
    foreach (is_array($data['Emneord'] ?? null) ? $data['Emneord'] : [] as $tag) {
        if (!is_string($tag) || $tag === '') { continue; }
        $html .= '<form action="'.esc_url(adb_page_url(['action'=>'get_search'])).'" method="post"><input type="hidden" name="string" value="'.esc_attr($tag).'"><input type="hidden" name="method" value="emneord"><input type="hidden" name="sortering" value="'.esc_attr($sort).'"><input type="hidden" name="usesecond" value="'.($second ? '1' : '0').'"><button type="submit">'.esc_html($tag).'</button></form>';
    }
    return $html.'<p><a href="'.esc_url(adb_page_url()).'">Tilbage til søgning</a></p>';
}
function adb_shortcode($atts = [], $content = null) {
    global $adb_options;
    $primary = adb_security_check();
    if (!$primary) { return '<p>Beklager, men der er ikke forbindelse til databasen (opsætningen er muligvis forkert).</p>'; }
    if (!empty($primary['error'])) { return adb_error_html($primary); }
    $secondary = $adb_options['database2_url'] ? adb_security_check(2) : false;
    $secondary_name = $secondary && empty($secondary['error']) ? adb_text($secondary,'arkivnavn') : '';
    $second = $secondary_name !== '' && adb_input($_POST, 'usesecond', adb_input($_GET,'usesecond')) === '1';
    $archive = $second ? 2 : 1;
    $sort = adb_choice(adb_input($_POST,'sortering', adb_input($_GET,'sortering', $adb_options['sortering'])), ['titel','periode','arkiv','materialetype','placering','registreringsdato'], 'titel');
    $method = adb_choice(adb_input($_POST,'method'), ['string','and','or','emneord'], 'string');
    $string = adb_input($_POST,'string');
    $action = adb_input($_GET,'action');
    $html = '<div class="adb-container">';
    if ($adb_options['visnavn'] === '1') { $html .= '<h2>Søg i '.esc_html(adb_text($second ? $secondary : $primary,'arkivnavn')).'</h2>'; }
    if ($second) { $html .= '<p><strong>Der søges i alternativ samling!</strong></p>'; }
    if ($action === 'get_details') {
        $id = adb_input($_GET,'id');
        if ($id === '') { return $html.'<p>Registreringen mangler et ID.</p></div>'; }
        $data = adb_api_request('get_details', ['id'=>$id,'arkivnummer'=>adb_input($_GET,'arkivnummer')], $archive);
        if (!is_wp_error($data) && empty($data['error']) && !isset($data['Titel'])) { $data = new WP_Error('adb_details', 'Arkivet returnerede en ugyldig registrering.'); }
        return $html.(is_wp_error($data) || !empty($data['error']) ? adb_error_html($data) : adb_details($data,$sort,$second)).'</div>';
    }
    $form = adb_search_form($string,$method,$sort,$second,$secondary_name);
    $search = $action === 'get_search';
    if (!$search || in_array($adb_options['sogplacering'], ['2','3'], true)) { $html .= $form; }
    if ($search) {
        $data = $string === '' ? new WP_Error('adb_empty','En søgning kan ikke være tom.') : adb_api_request('get_search', ['string'=>$string,'method'=>$method], $archive);
        if (is_wp_error($data) || !empty($data['error'])) { $html .= adb_error_html($data); }
        elseif (!$data) { $html .= '<p>Søgningen gav <strong>ingen</strong> resultater. Prøv at udvide din søgning!</p>'; }
        else {
            foreach ($data as $row) {
                if (!is_array($row) || !isset($row['id'], $row['navn'])) { return $html.adb_error_html(new WP_Error('adb_rows','Arkivet returnerede et ugyldigt søgeresultat.')).'</div>'; }
            }
            $html .= adb_results($data,$sort,$second);
        }
        if (in_array($adb_options['sogplacering'], ['1','3'], true)) { $html .= $form; }
    }
    return $html.'</div>';
}
add_shortcode('adb', 'adb_shortcode');
