<?php
$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_staticblock` (
            `id_posstaticblock` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `identify` varchar(128) NOT NULL,
            `hook_position` varchar(128) NOT NULL,
            `name_module` varchar(128) NOT NULL,
            `hook_module` varchar(128) NOT NULL,
            `posorder` int(10) unsigned NOT NULL,
            `insert_module` int(10) unsigned NOT NULL,
            `active` int(10) unsigned NOT NULL,
            `showhook` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_posstaticblock`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
		
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_staticblock_lang` (
            `id_posstaticblock` int(11) unsigned NOT NULL,
            `id_lang` int(11) unsigned NOT NULL,
			`title` varchar(128) NOT NULL,
            `description` longtext,
            PRIMARY KEY (`id_posstaticblock`,`id_lang`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
				

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pos_staticblock_shop` (
            `id_posstaticblock` int(11) unsigned NOT NULL,
            `id_shop` int(11) unsigned NOT NULL,
            PRIMARY KEY (`id_posstaticblock`,`id_shop`)
        ) ENGINE = ' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$doc = new DOMDocument();
$file = _PS_MODULE_DIR_.DS.'posstaticblocks'.DS.'sql'.DS.'staticblock.xml';
$doc->load( $file);

$blocks = $doc->getElementsByTagName("block");
foreach( $blocks as $block )
{	
	$ids = $block->getElementsByTagName( "id_posstaticblock" );
	$id = $ids->item(0)->nodeValue;
	$identifys = $block->getElementsByTagName( "identify" );
	$identify = $identifys->item(0)->nodeValue;
	$hook_positions = $block->getElementsByTagName( "hook_position" );
	$hook_position = $hook_positions->item(0)->nodeValue;
	$name_modules = $block->getElementsByTagName( "name_module" );
	$name_module = $name_modules->item(0)->nodeValue;
	$hook_modules = $block->getElementsByTagName( "hook_module" );
	$hook_module = $hook_modules->item(0)->nodeValue;
	$posorders = $block->getElementsByTagName( "posorder" );
	$posorder = $posorders->item(0)->nodeValue;
	$posorders = $block->getElementsByTagName( "posorder" );
	$posorder = $posorders->item(0)->nodeValue;
	$insert_modules = $block->getElementsByTagName( "insert_module" );
	$insert_module = $insert_modules->item(0)->nodeValue;
	$actives = $block->getElementsByTagName( "active" );
	$active = $actives->item(0)->nodeValue;
	$showhooks = $block->getElementsByTagName( "showhook" );
	$showhook = $showhooks->item(0)->nodeValue;
	$sql[] = "INSERT INTO `"._DB_PREFIX_."pos_staticblock` (`id_posstaticblock`, `identify`, `hook_position`, `name_module`, `hook_module`,`posorder`, `insert_module`, `active`, `showhook`) 
	values('".$id."','".$identify."','".$hook_position."','".$name_module."','".$hook_module."','".$posorder."','".$insert_module."','".$active."','".$showhook."')";
}

$blocklangs = $doc->getElementsByTagName("block_lang");
foreach( $blocklangs as $blocklang )
{	
	$ids = $blocklang->getElementsByTagName( "id_posstaticblock" );
	$id = $ids->item(0)->nodeValue;
	$id_langs = $blocklang->getElementsByTagName( "id_lang" );
	$id_lang = $id_langs->item(0)->nodeValue;
	$titles = $blocklang->getElementsByTagName( "title" );
	$title = $titles->item(0)->nodeValue;
	$descriptions = $blocklang->getElementsByTagName( "description" );
	$description = $descriptions->item(0)->nodeValue;
	$description = str_replace('path_default_postheme/',__PS_BASE_URI__,$description);
	$sql[] = "INSERT INTO`"._DB_PREFIX_."pos_staticblock_lang` (`id_posstaticblock`,`id_lang`,`title`,`description`) 
	values('".$id."','".$id_lang."','".$title."','".$description."')";
}



$blockshops = $doc->getElementsByTagName("block_shop");
foreach( $blockshops as $blockshop )
{	
	$ids = $blockshop->getElementsByTagName( "id_posstaticblock" );
	$id = $ids->item(0)->nodeValue;
        $id_shops = $blockshop->getElementsByTagName( "id_shop" );
	$id_shop = $id_shops->item(0)->nodeValue;
        $sql[] = "insert into `"._DB_PREFIX_."pos_staticblock_shop`(`id_posstaticblock`, `id_shop`) 
            VALUES('".$id."','".$id_shop."')";
}