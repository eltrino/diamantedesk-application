<?php

$tasksJson = file_get_contents("tmp/tasks.json");
$tasksObject = json_decode($tasksJson);

require 'tmp/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->contentType('application/json');
//$db = new PDO('sqlite:tmp/db.sqlite3');

$app->get('/tasks', function() use ($tasksJson){
    echo $tasksJson;
});

$app->get('/tasks/:id', function($id) use ($tasksObject){
    $task = null;
    foreach($tasksObject as $value){
        if($value->id == $id){
            $task = json_encode($value);
        }
    }
    echo $task;
});

$app->put('/tasks/:id', function($id) use ($app,$tasksObject){
    echo $app->request()->getBody();
});

$app->run();