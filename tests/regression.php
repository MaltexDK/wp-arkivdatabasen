<?php
$_SERVER['HTTP_HOST']='127.0.0.1:8080';
require getenv('WP_LOAD_PATH') ?: '/var/www/html/wp-load.php';
set_error_handler(function($level,$message,$file,$line) { if (error_reporting() & $level) { throw new ErrorException($message,0,$level,$file,$line); } });
$stored_settings = get_option('adb_settings');
$count=0;
function adb_assert($ok,$message) { global $count; if (!$ok) { throw new Exception($message); } $count++; }
global $adb_options,$adb_update_checker;
$adb_options=adb_defaults();
$adb_options['database_url']='https://archive.example.test/api?existing=1';
$adb_options['api_key']='fixture';
$mode='normal'; $asset=true;
add_filter('pre_http_request',function($pre,$args,$url) use (&$mode,&$asset) {
    $response=function($body,$code=200) { return ['headers'=>[],'body'=>$body,'response'=>['code'=>$code,'message'=>'OK'],'cookies'=>[]]; };
    if (wp_parse_url($url,PHP_URL_HOST)==='api.github.com') {
        if (strpos($url,'/releases/latest')!==false) { return $response(wp_json_encode(['tag_name'=>'v1.4.0','draft'=>false,'prerelease'=>false,'created_at'=>'2026-10-14T00:00:00Z','body'=>'Stable update','zipball_url'=>'https://example.test/source.zip','assets'=>$asset ? [['name'=>'arkivdatabasen.zip','browser_download_url'=>'https://github.com/MaltexDK/wp-arkivdatabasen/releases/download/v1.4.0/arkivdatabasen.zip','download_count'=>1]] : []])); }
        if (strpos($url,'/contents/arkivdatabasen.php')!==false) { return $response(wp_json_encode(['encoding'=>'base64','content'=>base64_encode("<?php\n/*\nPlugin Name: Arkivdatabasen\nVersion: 1.4.0\n*/")])); }
        return $response('[]');
    }
    if (wp_parse_url($url,PHP_URL_HOST)!=='archive.example.test') { return new WP_Error('test','External requests disabled'); }
    parse_str(wp_parse_url($url,PHP_URL_QUERY),$q);
    adb_assert(($q['existing'] ?? '')==='1','Existing query lost');
    adb_assert($q['signatur']===md5('fixture'.$q['timestamp']),'Signature changed');
    if ($q['action']==='get_check') { return $response('{"error":false,"arkivnavn":"Æble","message":"OK"}'); }
    if ($mode==='network') { return new WP_Error('timeout','Timeout'); }
    if ($mode==='http') { return $response('Unavailable',503); }
    if ($mode==='json') { return $response('{invalid'); }
    if ($mode==='scalar') { return $response('"invalid"'); }
    if ($mode==='empty') { return $response('[]'); }
    if ($q['action']==='get_details') { return $response(wp_json_encode(['Titel'=>'Æble <script>evil</script>','Billeder'=>[['url'=>'javascript:alert(1)','thumpnail'=>'https://example.test/a.jpg']],'Emneord'=>["O'Brien <img src=x>"]])); }
    $body=wp_json_encode([['id'=>1,'arkivnummer'=>'A&B','navn'=>'Æble <script>evil</script>']],JSON_UNESCAPED_UNICODE);
    return $response($mode==='latin1' ? iconv('UTF-8','ISO-8859-1',$body) : $body);
},10,3);
adb_assert(adb_security_check()['arkivnavn']==='Æble','UTF8 check');
$_GET=[]; $_POST=[];
adb_assert(strpos(do_shortcode('[adb]'),'name="string"')!==false,'Initial form');
$_GET=['action'=>'get_search']; $_POST=['string'=>'"><img src=x onerror="evil">','sortering'=>'bad','method'=>'bad'];
$html=do_shortcode('[adb]');
adb_assert(strpos($html,'<script>evil</script>')===false,'API escaping');
adb_assert(strpos($html,'value=""><img')===false,'Attribute escaping');
adb_assert(strpos($html,'Æble')!==false,'UTF8 search');
adb_assert(strpos($html,'arkivnummer=A%26B')!==false,'Query encoding');
$mode='latin1'; adb_assert(strpos(do_shortcode('[adb]'),'Æble')!==false,'Latin1 search');
foreach (['network','http','json','scalar','empty'] as $mode) { adb_assert(strpos(do_shortcode('[adb]'),'name="string"')!==false,'Error form '.$mode); }
$mode='normal'; $_GET=['action'=>'get_details','id'=>'1']; $_POST=[];
$html=do_shortcode('[adb]');
adb_assert(strpos($html,'javascript:')===false && strpos($html,'<script>')===false,'Details escaping');
$_GET=['action'=>['bad']]; $_POST=['string'=>['bad']]; adb_assert(is_string(do_shortcode('[adb]')),'Array input');
$clean=adb_sanitize_settings(['stylesheet'=>'../../bad','resultatvisning'=>'bad','api_key'=>' key ']);
adb_assert($clean['stylesheet']==='standard' && $clean['api_key']==='key','Settings validation');
$adb_options=adb_defaults(); adb_assert(strpos(do_shortcode('[adb]'),'Beklager')!==false,'Missing setup');
$update=$adb_update_checker->requestUpdate();
adb_assert($update && $update->version==='1.4.0','Release version');
adb_assert(strpos($update->download_url,'/releases/download/v1.4.0/arkivdatabasen.zip')!==false,'Release asset');
$asset=false; adb_assert($adb_update_checker->requestUpdate()===null,'Source fallback rejected');
adb_assert(get_option('adb_settings') === $stored_settings, 'Saved settings changed');
$transient = $adb_update_checker->injectUpdate((object)['response'=>[], 'no_update'=>[]]);
adb_assert(isset($transient->no_update[plugin_basename(ADB_FILE)]) || isset($transient->response[plugin_basename(ADB_FILE)]), 'WordPress auto-update entry missing');
echo "PASS: $count assertions\n";
