<?php

$tasksJson = file_get_contents("tmp/tasks.json");
$tasksObject = json_decode($tasksJson);

require 'tmp/Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->contentType('application/json');
$db = new PDO('sqlite:tmp/db.sqlite3');

function insertTask($row, $db, $id = null){
    $sql =<<<EOD
INSERT INTO `tasks` (
    `subject`,
    `shortcode`,
    `priority`,
    `status`,
    `description`,
    `created_at`
) VALUES (:subject, :shortcode, :priority, :status, :description, :created_at);
EOD;
    if(isset($id)){
        $sql =<<<EOD
UPDATE `tasks` SET
    `subject`       = :subject,
    `shortcode`     = :shortcode,
    `priority`      = :priority,
    `status`        = :status,
    `description`   = :description,
    `created_at`    = :created_at
WHERE `id` = $id;
EOD;
    }
    if(!isset($row->created_at)){
        $row->created_at = date('j-M-Y');
    };
    if(!isset($row->shortcode)){
        $row->shortcode = 'XAS';
    };
    if(!isset($row->status)){
        $row->status = 'Assigned';
    };
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':subject', $row->subject);
    $stmt->bindParam(':shortcode', $row->shortcode);
    $stmt->bindParam(':priority', $row->priority);
    $stmt->bindParam(':status', $row->status);
    $stmt->bindParam(':description', $row->description);
    $stmt->bindParam(':created_at', $row->created_at);
    $stmt->execute();
}

$app->get('/tasks.json', function() use ($db){
    $stmt = $db->query("SELECT id, subject, priority, status, created_at FROM tasks;");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
});

$app->get('/tasks/:id.json', function($id) use ($db){
    $stmt = $db->query('SELECT * FROM tasks WHERE id = ? LIMIT 1;');
    $stmt->execute([intval($id)]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)[0]);
});

$app->put('/tasks/:id.json', function($id) use ($app, $db){
    $row = json_decode($app->request()->getBody());
    insertTask($row, $db, $id);
    echo json_encode($row);
});

$app->post('/tasks.json', function() use ($app, $db){
    $row = json_decode($app->request()->getBody());
    insertTask($row, $db);
    $id = $db->lastInsertId();
    $stmt = $db->query('SELECT * FROM tasks WHERE id = ? LIMIT 1;');
    $stmt->execute([intval($id)]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)[0]);
});



$app->get('/install', function () use ($db, $tasksObject, $db) {
    $create = <<<EOD
DROP TABLE `tasks`;
CREATE TABLE `tasks` (
    `id`            INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `subject`       TEXT,
    `shortcode`     TEXT,
    `priority`      TEXT,
    `status`        TEXT,
    `description`   TEXT,
    `created_at`    TEXT
);
EOD;

    $db->exec($create);

    foreach($tasksObject as $row){
        insertTask($row, $db);
    }


    echo 'instaled';
});

$app->run();