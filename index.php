<?php
	require_once './inc/func.php'; 
	
	$variables = process_request(); 
	
	print theme_output($variables); 