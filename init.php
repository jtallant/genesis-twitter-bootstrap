<?php

require_once 'classes/Twitter_Navbar_Walker.php';

require_once 'classes/Genesis_Twitter_Bootstrap.php';

$_genesis_tb_config = array(
	'container_class'  => 'container',
	'load_assets'      => true,
	'remove_header'    => false,
	'main_nav'         => array(
		'filter'       => true,
		'classes'	   => '',
		'brand'        => '',
		'responsive'   => true
	)
);

new Genesis_Twitter_Bootstrap($_genesis_tb_config);