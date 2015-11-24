<?php

$dataholder = "file.txt";
$messages = unserialize(file_get_contents($dataholder));
header('Content-Type: application/json'); 


$id = false;
// Since the arguments aren't passed as normal HTML query parameters but instead in the address structure, 
// we need to parse the URL with RegEx to see what the client requested
if(preg_match('#threads/:(\d*)#', $_SERVER['REQUEST_URI'], $matches)){
    // Match was found, here's our ID
    $id = $matches[1];
}


if($_SERVER['REQUEST_METHOD'] == 'POST'){ 
    // POST request = Posting new or responding to thread
    if(!$id) {
        // POST & no ID = new message
        if(empty($messages)) // Size of array containing 0 elements is still 1, like an array containing 1 elemnt, so we need to make this exception here
            $id = 1;
        else 
            $id = sizeof($messages)+1;
        $newthread = true;
        $ok = true;
    } else if(!isset($messages[$id])) {
        // Client is trying to post a reply to a thread that doesn't exist.
        http_response_code(404);
        echo json_encode(array('error'=>'THREAD_ID_NOT_FOUND'), JSON_PRETTY_PRINT);
        $ok = false;
    } else {
        // POST & valid ID = response to a thread
        $newthread = false; // The saving mechanism is the same for both but the response is different for these two cases
        $ok = true;
    }
    
    if($ok) {
        $messages[$id]['id'] = $id;
        // Since client is posting raw json data instead of post parameters, we need to read the data from the input file handle
        $store_object = json_decode(file_get_contents('php://input'));
        if($store_object && isset($store_object->nickname) && isset($store_object->message)) { 
            // If json_decode was able to read the data expected, we can safely save it
            $store['id'] = sizeof($messages[$id]['messages'])+1;
            $store['nickname'] = $store_object->nickname;
            $store['messageBody'] = $store_object->message;
        
            $messages[$id]['messages'][$store['id']-1] = $store;
            file_put_contents($dataholder, serialize($messages));
        
            if($newthread)
                echo json_encode($messages[$id], JSON_PRETTY_PRINT);
            else
                echo json_encode($store, JSON_PRETTY_PRINT);
        } else {
            // The request was in some way malformed and the server side is unable to process it.
            http_response_code(400);
            echo json_encode(array('error'=>'BAD_REQUEST'), JSON_PRETTY_PRINT);
        }
    }
} else if($id>0){
    // GET request and id given = fetching a message
    if(isset($messages[$id])) // The message requested exists, return it to client
        echo json_encode($messages[$id], JSON_PRETTY_PRINT);
    else // The message id given doesn't exist in our message data
        echo json_encode(array('error'=>'THREAD_ID_NOT_FOUND'), JSON_PRETTY_PRINT);
} else {
    // GET request & no id = getting a list of all the messages
    foreach($messages as $post) {
        $list[] = array(
            'threadId' => $post['id'],
            'preview' => substr($post['messages'][sizeof($post['messages'])-1]['messageBody'],0,70),
        );
    }
    echo json_encode($list?:array(), JSON_PRETTY_PRINT );
}
