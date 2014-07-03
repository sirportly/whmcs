<?PHP
define("CLIENTAREA",true);
require("init.php");
require_once('modules/addons/sirportly/sirportly_functions.php');
include_once "modules/addons/sirportly/markdown.php";
$ca = new WHMCS_ClientArea();
$ca->initPage();
$ca->setPageTitle( $whmcs->get_lang('supportticketssubmitticket') );
$settings = sirportly_settings();

if ( !sirportly_enabled() ) {
  header('Location: /');
}

$ca->setTemplate('/modules/addons/sirportly/templates/default/knowledgebase.tpl');
$content = curl('/api/v2/knowledge/page', array('kb' => $settings['kb'], 'path' => ltrim ($_SERVER['PATH_INFO'], '/') ));
$ca->assign('tree', doc_nav($kb) );
$ca->assign('title', $content['results']['page']['title'] );
$ca->assign('content', Markdown($content['results']['page']['content']) );
$ca->output();