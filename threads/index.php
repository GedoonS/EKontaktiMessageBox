<?php
$dataholder = "file.txt";
$messages = unserialize(file_get_contents($dataholder));

//parse url
$id = false;
if(strpos($_SERVER['REQUEST_URI'], ':')!==false){
    // Specific ID was called
    $id = array_pop(explode('threads/:', $_SERVER['REQUEST_URI']));   
}

if(sizeof($_POST) ){// Posting new or responding to thread
    if(!$id) {
        $id = sizeof($messages)+1;
        $newthread=true;
    }
    $messages[$id]['id'] = $id;
    $store = $_POST;
    $store['id'] = sizeof($messages[$id]['messages'])+1;
    $messages[$id]['messages'][$store['id']-1] = $store;
    file_put_contents($dataholder, serialize($messages));
    if($newthread)
        echo json_encode($messages[$id]);
    else
        echo json_encode($store);
} else if($id>0){// fetching a message
    echo json_encode($messages[$id], JSON_PRETTY_PRINT);
} else {// getting a list
    foreach($messages as $post) {
        $list[] = array(
            'threadId' => $post['id'],
            'preview' => substr($post['messages'][0]['messageBody'],0,70),
        );
    }
    echo json_encode($list?:array(), JSON_PRETTY_PRINT );
}
