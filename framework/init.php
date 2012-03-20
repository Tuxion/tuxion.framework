<?php

//Require helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Initiate configuration class.
//tx('Config');


//tx('Sql');