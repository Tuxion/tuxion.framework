<?php

//Load the helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper, $helpers);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Initiate debugging class.
tx('Debug');

//Initiate configuration class.
tx('Config');
