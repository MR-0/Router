<?php
/* INCLUDES */
include 'routers.php';

$router = new Router();

$router->get('/my-route/{variable}', function($req, $variable){
  // Do something
});
