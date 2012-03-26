<?php

//Require helpers.
foreach(glob('helpers/*.php') as $helper) require_once($helper); unset($helper);

//Preload the Loader class. We need it for loading.
require_once('system/core/Loader.php');

//Initiate debugging class.
tx('Debug');

//Initiate configuration class.
tx('Config');

$test = tx('Sql')->query('SELECT * FROM #system_config');

while($row = $test->row()){
  trace($row);
}