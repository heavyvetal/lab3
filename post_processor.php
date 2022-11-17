<?php
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $conn = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    } catch (MongoDBDriverExceptionException $e) {
        echo 'Failed to connect to MongoDB, is the service intalled and running?<br><br>';
        echo $e->getMessage();
        exit();
    }

    $query = new MongoDB\Driver\Query([],[]);
    $raw_posts = $conn->executeQuery('forum.posts', $query);

    $posts = [];

    foreach ($raw_posts as $raw_post) {
        $post = [];
        $post['id'] = (string)$raw_post->{'_id'};
        $post['username'] = $raw_post->name;
        $post['content'] = $raw_post->content;
        $post['created_at'] = $raw_post->created_at;
        $posts[] = $post;
    }

    $bulk = new MongoDB\Driver\BulkWrite();

    // Saving
    if (isset($_POST['text'])) {
        $bulk->update(
            ['_id' => new \MongoDB\BSON\ObjectID($id)],
            ['$set' => [
                    'content' => $_POST['text'],
                ]
            ]
        );

        $conn->executeBulkWrite('forum.posts', $bulk);

        $response = 'The post was succefully saved';
    }

    // Removing
    if (isset($_POST['del'])) {
        $bulk->delete(
            ['_id' => new \MongoDB\BSON\ObjectID($id)]
        );

        $conn->executeBulkWrite('forum.posts', $bulk);

        $response = 'The post was succefully removed';
    }

    echo $response;
}