<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

<script type="text/javascript">
$( document ).ready(function() {
    $("#send").click(function(){
        var sendInfo = {
            "nickname": $("#name").val(),
            "messageBody": $("#message").val()
        };
        var url = "threads/";
        var thread = $("#thread").val()*1;
        if(thread>0) {
            url = url + ':' + thread;   
        }
        $.ajax({
            type: "POST",
            url: url,
            data: JSON.stringify(sendInfo),
            contentType: "application/json", // send as JSON
            success: function(data){
                $('*[data-thread-id="'+data['id']+'"]').append(
                    "<div data-sub-id='"+data['id']+"'>"+data['message']+"</div>"
                );
                console.log(data);
            },
            dataType: "xml/html/script/json" // expected format for response
        });
    });
    $.ajax({
        type: "get",
        url: "threads/",
        success: function(data){
            for(var i in data) {
                $("#forum").append(
                    "<div data-thread-id='"+data[i]['threadId']+"'>"+data[i]['preview']+"</div>"
                );
            }
        },
        dataType: "json"
    });
});
</script>

</head>

<body>

<div id="forum">

</div>

<form id="theForm">
    <input type="text" id="name" placeholder="Kirjoita nimesi" /><br>
    <textarea id="message" placeholder="Kirjoita viestisi"></textarea><br>
    <input type="text" id="thread" readonly value="" /><br>
    <button id="send" type="button" >Lähetä</button>
</form>





</body>
</html>