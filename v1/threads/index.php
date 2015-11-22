<?php

$dataholder = "file.txt";
$messages = unserialize(file_get_contents($dataholder));
header('Content-Type: application/json'); 

//parse url
$id = false;
if(strpos($_SERVER['REQUEST_URI'], ':')!==false){
    // Specific ID was called
    $id = array_pop(explode('threads/:', $_SERVER['REQUEST_URI']));   
}




if($_SERVER['REQUEST_METHOD'] == 'POST'){// Posting new or responding to thread
    if(!$id) {
        $id = sizeof($messages);
        if(empty($messages)) $id = 0;
        $id++;
        $newthread=true;
    } else if(!isset($messages[$id])) {
        echo json_encode(array('error'=>'THREAD_ID_NOT_FOUND'), JSON_PRETTY_PRINT);
        exit();   
    } 
    $messages[$id]['id'] = $id;
    $store_object = json_decode(file_get_contents('php://input'));

    $store['id'] = sizeof($messages[$id]['messages'])+1;
    $store['nickname'] = $store_object->nickname;
    $store['messageBody'] = $store_object->message;

    $messages[$id]['messages'][$store['id']-1] = $store;
    file_put_contents($dataholder, serialize($messages));
    if($newthread)
        echo json_encode($messages[$id], JSON_PRETTY_PRINT);
    else
        echo json_encode($store, JSON_PRETTY_PRINT);
} else if($id>0){// fetching a message
    if(isset($messages[$id]))
        echo json_encode($messages[$id], JSON_PRETTY_PRINT);
    else
        echo json_encode(array('error'=>'THREAD_ID_NOT_FOUND'), JSON_PRETTY_PRINT);
} else {// getting a list
    foreach($messages as $post) {
        $list[] = array(
            'threadId' => $post['id'],
            'preview' => substr($post['messages'][0]['messageBody'],0,70),
        );
    }
    echo json_encode($list?:array(), JSON_PRETTY_PRINT );
}
