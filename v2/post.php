<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Viestiseinä</title>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

<script type="text/javascript">
$( document ).ready(function() {
    $("#send").click(function(){
        // user wants to post data to server
        var sendInfo = {
            "nickname": $("#name").val(),
            "message": $("#message").val()
        };
        var url = "cakephp-3-1-4/threads/";
        var thread = $("#thread").val()*1;
        if(thread>0) {
            sendInfo["responseid"] = $("#thread").val();
        }
        // Sending the request to server
        $.ajax({
            type: "POST",
            url: url,
            data: JSON.stringify(sendInfo),// Prepare the data for the server
            contentType: "application/json", // send as JSON
            success: function(data){
                // Server responded with success
                if(data['messages'].length<2) {
                    // Remove previously highlighted message
                    $("#forum div").removeClass('highlight');
                    row = $(
                        "<div data-sub-id='"+data['id']+"'>"+data['messages'][0]['messageBody'].substring(0,70)+"</div>"
                    ).addClass('highlight');
                    // Add a new row, highlighted
                    $("#forum").append(row);
                    $("#thread").val(data['id']);
                }
                // Refresh the thread view with the server data
                $("#threads").empty();
                for(var i in data['messages']) {
                    console.log(data['messages'][i]['messageBody']);
                    $("#threads").append(
                        "<div><span class='nick'>"+data['messages'][i]['nickname'] + '</span><span class="message">' + data['messages'][i]['messageBody'].replace(/(\r\n|\n|\r)/gm, '<br />')+"</span></div>"
                    );
                }
                // Minimize the postings list
                $('#forum').animate({width: '40%'});

            },
            dataType: "json" // expected format for response
        });
    });
    $.ajax({
        // Initial action when the page loads: load data from the server
        type: "get",
        url: "cakephp-3-1-4/threads/",
        success: function(data){
            if(!data.length) {
                $("#NoMessagesHint").hide();    
            } else {
                $("#NoMessagesHint").show();    
                for(var i in data) {
                    $("#forum").append(
                        "<div data-thread-id='"+data[i]['threadId']+"'>"+data[i]['preview']+"</div>"
                    );
                }
            }
        },
        dataType: "json"
    });
    $("#forum").on('click', 'div', function() {
        $("#forum div").removeClass('highlight');
        $(this).addClass('highlight');
        $.ajax({
            type: "get",
            url: "cakephp-3-1-4/threads/"+$(this).data("thread-id"),
            success: function(data){
                $("#theForm").show();    
                $("#threads").empty();
                $('#forum').animate({width: '40%'});
                $("#title").text('Vastaa viestiketjuun'); 
                
                for(var i in data['messages']) {
                    console.log(data['messages'][i]['messageBody']);
                    $("#threads").append(
                        "<div><span class='nick'>" + data['messages'][i]['nickname'] + '</span><span class="message">' + data['messages'][i]['messageBody'].replace(/(\r\n|\n|\r)/gm, '<br>')+"</span></div>"
                    );
                }
            },
            dataType: "json"
        });        
    });
    $("#newthread").on('click', function() {
        $("#threads").empty();
        $("#title").text('Kirjoita uusi viesti'); 
        $("#thread").val('');
        $("#theForm").show();  
        $('#forum').animate({width: '40%'});
        $("#NoMessagesHint").hide();        
    });
    $("#theForm").hide();

});
</script>
<style type="text/css">
body {
    background-color:rgb(134,96,125);
    background-image:url(fractal-illusions.jpg);
 	background-size:100%;
	background-attachment:fixed;   
    font-family:Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;    
}
main {
    background-color: rgba(255,255,255,0.9);
    margin:auto;
    max-width:80em;
    padding:2em;
    border-radius:2em;
    min-height:50em;
    box-shadow:#000 0px 10px 15px;
}
#forum div:before {
    content:'\2665 ';
    text-decoration:none;
    display:inline-block;
    margin:0 0.5em;
}
#forum div {
    cursor:pointer;
    text-decoration:underline;
    color:#CB85A7;
    line-height:1.8;
}
#threads .nick{
    color:#CB85A7;
    font-weight:bold;   
}
#threads .nick:after{
    content: ' kirjoitti:';
}
#threads .message{
    display:block;
    margin-bottom:1em;
}
#threads {
    width:50%;
}
#theForm {
    padding:1em;
    border-radius:1em;
    background-color:#fff;
    width:50%;
    box-shadow: #ccc 0px 5px 10px;
}
#theForm h2, #forum h2 {
    margin-top:0;
}
#theForm textarea {
    width:100%;
    height:10em;
}
#newthread, #send {
    float:right;
}
#forum {
    float:right;
    width:100%;
}
.highlight {
    background-color:rgba(255,0,190,0.10);
}

</style>
</head>

<body>
    <main>
        <div id="forum">
            <button id="newthread" type="button" >Aloita uusi viestiketju</button>
            <h2>Viestiketjut</h2>
            <p id="NoMessagesHint">Ketjussa ei ole viestejä. Aloita uusi keskustelu.</p>
        </div>
        <div id="threads"></div>
        <form id="theForm">
            <h2 id="title">Kirjoita uusi viesti</h2>
            <textarea id="message" placeholder="Kirjoita viestisi"></textarea><br>
            <input type="hidden" id="thread" readonly value="" /><br>
            <input type="text" id="name" placeholder="Kirjoita nimesi" />
            <button id="send" type="button" >Lähetä</button>
        </form>
    </main>
</body>
</html>