<?php
$path = explode('?',$_SERVER["REQUEST_URI"]);
$path = explode('/',$path[0]);
array_splice($path, 0, array_search('front', $path) + 1);
$method = $_SERVER['REQUEST_METHOD'];


header('Content-Type: application/json; charset=utf-8');

$tasksJson = file_get_contents("tmp/tasks.json");
$tasksObject = json_decode($tasksJson);

if($path[0] == 'tasks') {
    if(count($path) == 1){
        switch ($method) {
            case 'GET' :
                echo $tasksJson;
                break;
        }
    } else {
        switch ($method) {
            case 'GET' :
                $task = null;
                foreach($tasksObject as $value){
                    if($value->id == $path[1]){
                        $task = json_encode($value);
                    }
                }
                echo $task;
                break;
        }
    }

}