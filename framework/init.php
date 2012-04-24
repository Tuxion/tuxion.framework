<?php

//Set the timezone.
date_default_timezone_set('Europe/Amsterdam');

//Load the helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Load the config class.
tx('Config');

//Enter a log entry now that we can.
tx('Log')->message('root', 'system initialize', 'Helpers and loader loaded.');

//Load the debug class.
tx('Debug');

//Let the controllers do the rest of the work.
tx('Router');

//Enter a log entry to indicate the end of the system.
tx('Log')->message('root', '--', '------');
