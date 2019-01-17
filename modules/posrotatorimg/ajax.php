<?php
require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');
	$params = $_POST; 
	//print_r($params); action = 1 : add , action = add: update.
	$id_product = $params['id_product'];
	$action = $params['action'];
	$id_shop = (int) str_replace('s-','',$params['shop_id']); 

	if($action==2) {
			$data1 = array();
			if($id_shop==0) {
			  $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
			  $sql .= ' where img.`rotator` =1';
			  $sql .= ' AND img.`id_product` ='.$id_product ;
			  if(Db::getInstance()->Execute($sql)){
				 $result = Db::getInstance()->getRow($sql);
				 if($result['rotator'] ==1) $data1['id_image'] = 	$result['id_image']; 
			  }
			  $json1 = json_encode($data1); 
			  die(json_encode($json1));
			}
			//echo $id_shop.'--'. $action.'---'.$id_product;
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
			$sql .= ' LEFT JOIN `'. _DB_PREFIX_ . 'image_shop` imgs';
			$sql .= ' ON img.id_image = imgs.id_image';
			$sql .= ' where imgs.`id_shop` ='.$id_shop ;
			$sql .= ' AND img.`id_product` ='.$id_product ;
			$sql .= ' AND imgs.`rotator` =1' ;
			//echo '<pre>'; print_r($sql); die;
		     if(Db::getInstance()->Execute($sql)){
				$result = Db::getInstance()->getRow($sql);
				 if($result['rotator'] ==1) { 
					$data1['id_image'] = $result['id_image'];  
				 }
			 }
 
		$json1 = json_encode($data1); 
		die(json_encode($json1));
	}
	
	$value = 0; 
	$data = array();
	$images= Image::getImages((int)Context::getContext()->language->id,$id_product);
	$id = $params['img_id']; //22
	//echo $id; echo '-----------';
	//echo $id_shop; echo "<br>";
	foreach($images as $image) {
		$id_image = $image['id_image'];
		
		if($id_image== $id) { $value=1; } else {$value=0;} 
		//echo $id_image.'--'.$value;
		
		if($id_shop == 0) {
			$query = "UPDATE " . _DB_PREFIX_ . "image SET rotator =$value WHERE id_image =$id_image";
		} else {
			$query = "UPDATE " . _DB_PREFIX_ . "image_shop SET rotator =$value WHERE id_image =$id_image and id_shop = $id_shop";
		
		}

		if(!Db::getInstance()->Execute($query)){
			return false;
		} else {	
			$data [$image['id_image']]=array('id'=>$id,'id_product'=>$id_product);
		}	
	}
	$data['action'] = $params['action'];
	$data['value'] = $value;
	$data['id_shop'] = $id_shop;
	$json = json_encode($data); 
	die(json_encode($json));

?>
