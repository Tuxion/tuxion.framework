<?php

//Set the timezone.
date_default_timezone_set('Europe/Amsterdam');

//Load the helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Load the configuration class.
tx('Config');

//Load the debug class.
tx('Debug');

//Do everything you need to do to output the data.
tx('Response')->output();

//Log the end of the page-load.
tx('Log')->message('root', '--page-load completed--');
