<?php
	// router.php
	if (preg_match('/\.(?:png|jpg|jpeg|gif|css|ico|js|woff|ttf|txt)$/', $_SERVER["REQUEST_URI"])) {
		return false;    // serve the requested resource as-is.
	} else { 
		include("index.php");
	}