<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Testing</title>
</head>

<body>
<?php
$viewthread = $_GET['viewthread']?:1;
$viewthread = $_GET['post_response']?:$viewthread;


$base_url = sprintf(
    '%s://%s:%s%s', 
    $_SERVER['REQUEST_SCHEME'],
    $_SERVER['HTTP_HOST'],
    $_SERVER['SERVER_PORT'],
    dirname($_SERVER['PHP_SELF'])
);

?>

<h1>POST /threads/</h1>
<pre>
<a href="<?php echo $_SERVER['PHP_SELF'] ?>?post_new">Click to post a new thread</a>
<?php
if(isset($_GET['post_new'])) { 
    $text = json_decode(file_get_contents('http://skateipsum.com/get/2/1/JSON')); 
    shuffle($text);
    
    $ch = curl_init();
    echo $payload = json_encode(array(
        'nickname' => ucwords(substr($text[0], 0,strpos($text[0], ' '))),
        'message' => $text[1],
    ), JSON_PRETTY_PRINT);
    echo "<br/>";
    curl_setopt($ch, CURLOPT_URL,"$base_url/threads/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($payload))                                                                       
    );   
    echo $result = curl_exec ($ch);
    $info = curl_getinfo($ch);
    curl_close ($ch); 
}
?>
</pre>


<h1>POST /threads/:<?php echo $viewthread ?></h1>
<pre>
<a href="<?php echo $_SERVER['PHP_SELF'] ?>?post_response=<?php echo $viewthread; ?>">Click to post a response to <?php echo $viewthread; ?></a> &bull; <a href="<?php echo $_SERVER['PHP_SELF'] ?>?post_response&amp;bad_thread">Click to post a response to non-existing thread</a>
<?php
if(isset($_GET['post_response'])) { 

    $text = json_decode(file_get_contents('http://skateipsum.com/get/2/1/JSON')); 
    shuffle($text);

    $respondId = $viewthread;
    if(isset($_GET['bad_thread'])) { 
        $respondId = 1000;
    }     
    
    $ch = curl_init();
    echo $payload = json_encode(array(
        'nickname' => ucwords(substr($text[0], 0,strpos($text[0], ' '))),
        'message' => $text[1],
    ), JSON_PRETTY_PRINT);
    echo "<br/><h2>posting to threads/:$respondId</h2><br/>";
    curl_setopt($ch, CURLOPT_URL,"$base_url/threads/:$respondId");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($payload))                                                                       
    );   

    echo $result = curl_exec ($ch);
    $info = curl_getinfo($ch);
    curl_close ($ch); 
    
}
?>
</pre>

<h1>GET /threads</h1>
<pre>
<?php
echo $threads = file_get_contents("$base_url/threads");
echo "<br />";
foreach(json_decode($threads) as $thread) {
    echo "<a href='$_SERVER[PHP_SELF]?viewthread=$thread->threadId'>$thread->preview</a><br/>";

}

?>
</pre>


<h1>GET /threads/:<?php echo $viewthread ?></h1>
<pre>
<?php echo file_get_contents("$base_url/threads/:$viewthread"); ?>
</pre>

<h1>GET /threads/:1000</h1>
<pre>
<?php echo file_get_contents("$base_url/threads/:1000"); ?>
</pre>





</body>
</html>