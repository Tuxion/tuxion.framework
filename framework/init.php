<?php

//Load the helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Initiate debugging class.
tx('Debug');

//Initiate configuration class.
tx('Config');

header('Content-type: text/html');

trace( tx('User')->hasPermission('example', 'eatpie') );
trace( tx('User')->hasPermission('example', 'killpeople') );
