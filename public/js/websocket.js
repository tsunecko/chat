window.onload = function() {

    let socket = new WebSocket('ws://localhost:8080');
    let status = document.querySelector("#status");
    let user = @json($username);

    //open connection
    socket.onopen = function(e) {
        status.innerHTML = "Connection established!  <br>";
    };

    //close connection
    socket.onclose = function(e) {
        if(e.wasClean){
            status.innerHTML = "Connection close";
        } else {
            status.innerHTML = "Connection close for some reason!";
        };
        status.innerHTML += "<br>error code: " + e.code + "<br>reason: " + e.reason;
    }

    //get data
    socket.onmessage = function(e){
        let message = JSON.parse(e.data);

        // TODO Rewrite randcolor to saving color for user

        status.innerHTML += '<span style="color:' + randcolor() + '"><b>' + message.user + '</b></span>: ' + message.msg + '<br>';
    };

    //errors
    socket.onerror = function(e){
        status.innerHTML = "Error: " + e.message;
    }

    document.forms["messages"].onsubmit = function() {
        let message = {
            user: user,
            msg: this.msg.value
        }
        socket.send(JSON.stringify(message));
        return false;
    }
}