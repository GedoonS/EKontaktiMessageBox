<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class ThreadsController extends AppController {
    
    public function initialize() {
        // In this example I won't be using an actual database which in terms of CakePHP would be abstract ORM database
        // Instead I'll be using a simple file where a serialized array containing the messages will be stored. 
        $this->dataholder = "file.txt";
        if(!file_exists($this->dataholder))
            file_put_contents($this->dataholder, serialize(array()));
        $this->messages = unserialize(file_get_contents($this->dataholder));
    }
    
    public function beforeRender(Event $event) {
        // This will be called for each method before rendering
        // Since this is a RESTful API we won't be needing an actual layout, therefore views will not be rendered
        $this->autoLayout = false;
        $this->render(false);
        // Let client know we'll be sending json data
        $this->response->type('application/json');

    }
    public function index() {
        $list = array();
        // Index will be called be default if it's a GET request and doesn't contain a parameter. /threads
        foreach($this->messages as $post) {
            // Collect a list of messages from the objects message store and deliver an abbreviated preview of the latest message in the thread
            $list[] = array(
                'threadId' => $post['id'],
                'preview' => substr($post['messages'][sizeof($post['messages'])-1]['messageBody'],0,70),
            );
        }
        
        // Send the data encoded as JSON
        // JSON_PRETTY_PRINT isn't actually necessary here but I'm just using it for debugging purposes.
        $this->response->body(json_encode($list, JSON_PRETTY_PRINT));
        $this->response->statusCode(200);
    
        return $this->response;
    }
    public function view($id) {
        // This is called when client has requested /threads/N where N is a number of the thread
        // NOTE: The original specification stated this should be /threads/:N but that's not the native 
        // cakephp way so I skipped this for now. I'm fairly sure it can be done with a custom router, 
        // which I skipped now since this is extra curriculum anyway... The parameter w/ colon notation
        // seems to be a Ruby on Rails way of doing things but turns out it doesn't translate very
        // well to CakePHP. It isn't undoable but it needs extra layer of configuration.
        // The same issue came up with POST threads/:N...
        if(isset($this->messages[$id])) {
            $response = $this->messages[$id];
            $this->response->statusCode(200);            
        } else {
            // Client is requesting a thread that doesn't exist. Respond with a JSON object with error message and a status code
            $response = array('error'=>'THREAD_ID_NOT_FOUND');
            $this->response->statusCode(404);            
        }

        $this->response->body(json_encode($response, JSON_PRETTY_PRINT));
      
        return $this->response;
    }
    public function add() {
        // In an actual project this would be database interaction with the model. Since this isn't an actual project,
        // it's not a very good example but a bad mockup just to showcase how the API works...
        
        $ok=true; // If we receieve a bad request, this will be set to false
        
        // We receive the json encoded data from client and decode it as a jSON object
        $store_object = $this->request->input('json_decode');
        if(!isset($store_object->responseid)) {
            // Response ID is not set so this is not a reply to an existing thread. This was supposed to be passed in the URL.
            // There's a longer explanation to this earlier in the comments of view() method, but in short it's not the 
            // CakePHP way of passing data so it didn't quite work with the built-in restful api logic in this particular framework.
            $id = sizeof($this->messages)+1;
            if(empty($this->messages)) $id = 1;
            $newthread=true;
        } else {
            $id = $store_object->responseid;    

            if(!isset($this->messages[$id])) {
                // Client is attempting to post a response to a non-existing thread. 
                $this->response->body(json_encode(array('error'=>'THREAD_ID_NOT_FOUND')));
                $this->response->statusCode(404);            
                $ok=false;  
            } 
        }

        if($ok) {
            // It's okay to save the new message or response, so give it an ID and save it in the data storage file
            // In a complete MVC application these would be delegated to Model and the inputs would be filtered there to prevent any SQL injections and such.
            // In this case, the data is simply written in a file as is without any restrictions since none were given in the specification
            $this->messages[$id]['id'] = $id;
            @$store['id'] = sizeof($this->messages[$id]['messages'])+1;
            $store['nickname'] = $store_object->nickname;
            $store['messageBody'] = $store_object->message;
        
            $this->messages[$id]['messages'][$store['id']-1] = $store;
            file_put_contents(
                $this->dataholder,
                serialize($this->messages)
            );

            //  Respond to client with json data and a statuscode        
            $this->response->body(json_encode($this->messages[$id]));
            $this->response->statusCode(200);
        }
        return $this->response;
    }
}
