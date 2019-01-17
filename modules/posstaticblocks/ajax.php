<?php
require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');
include(dirname(__FILE__).'/posstaticblocks.php');

 $pos = new posstaticblocks();
  if(!isset( $_POST['module_id']) || $_POST['module_id'] == 'undefined')  die(json_encode(array()));
 $name_module = $_POST['module_id'];
 $module = Module::getInstanceByName($name_module);
 $id_module = $module->id;
 $hooks = $pos->getHooksByModuleId($id_module);
 $currentHook = $pos->getPosCurrentHook($name_module);
 $hookArrays = array();
 foreach($hooks as $key => $hook) {
 	$selected = 0;
 	if($currentHook['hook_module'] == $hook) $selected = 1;
	$hookArrays[$key] = array('id_hook'=>$hook, 'name' => $hook, 'selected' => $selected);
 }
//echo '<pre>'; print_r($hookArrays); die;
$json = json_encode($hookArrays); 
die(json_encode($json));

?>
