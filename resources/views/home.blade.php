@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Chat</div>

                    <div class="panel-body">

                        <form name="messages" action="" method="">
                            {{ csrf_field() }}

                            <div class="form-group">
                                <label for="online">Online now:</label>
                                <div id="online">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="msg">Enter message, {{ $username }}</label>
                                <textarea class="form-control rounded-0" id="msg" rows="3" placeholder="Your message"
                                          name="msg"></textarea>
                            </div>

                            <input type="submit" value="Send"/>
                        </form>
                        <br>

                        <div class="form-group">
                            <label for="chat">Chat</label>
                            <div id="chat"></div>
                        </div>

                    </div>

                    <script>
                        window.onload = function () {

                            let socket = new WebSocket('ws://localhost:8080?{{ $token }}');
                            let chat = document.querySelector("#chat");

                            let user = @json($username);

                            //open connection
                            socket.onopen = function (event) {

                                //send json into back when user online
                                let message = {
                                    "type": 'online_into_chat',
                                    "islogin": user,
                                }
                                socket.send(JSON.stringify(message));
                            };


                            //close connection
                            socket.onclose = function (event) {

                                if (event.wasClean) {
                                    chat.innerHTML = "Connection close";
                                } else {
                                    chat.innerHTML = "Connection close for some reason!";
                                }
                                chat.innerHTML += "<br>error code: " + event.code + "<br>reason: " + event.reason;
                            }


                            //get data from back
                            socket.onmessage = function (event) {

                                let data = JSON.parse(event.data);

                                switch (data.type) {

                                    //send current online users to all users
                                    case 'userlist':
                                        if ('<span id="onlinenames">') {
                                            $('#online').empty();
                                        }
                                        for (let user of data.list.names) {
                                            $('#online').append($('<span id="onlinenames" class="btn btn-default">' + user +
                                                ' <span id="mute" onclick="mute(' + user + ')" class="glyphicon glyphicon-volume-off"></span> ' +
                                                '<span id="ban" class="glyphicon glyphicon-remove-circle"></span>' + ' </span>'));

                                        }
                                        break;

                                    //print into chat user is online
                                    case 'online_into_chat':
                                        $('#chat').append(
                                            '<span style="color:#008080; font-style: italic">' + data.islogin + ' is online</span><br>'
                                        );
                                        break;

                                    //print message into chat
                                    case 'message':
                                        $('#chat').append(
                                            '<span style="color:' + randcolor() + '; font-weight: bold">' + data.user + '</span>: ' + data.text + '<br>'
                                        );
                                        break;

                                    //print into chat user is offline
                                    case 'offline_into_chat':
                                        $('#chat').append(
                                            '<span style="color:#A9A9A9; font-style: italic">' + data.islogout + ' is offline</span><br>'
                                        );
                                        break;

                                    //mute
                                    case 'mute':
                                        $('#chat').append(
                                            '<span style="color:#B8860B; font-style: italic">' + data.user + ' is muted</span><br>'
                                        );
                                        break;

                                    //ban
                                    case 'ban':
                                        $('#chat').append(
                                            '<span style="color:#800000; font-style: italic">' + data.user + ' is baned</span><br>'
                                        );
                                        break;
                                }
                            };


                            //errors
                            socket.onerror = function (event) {
                                chat.innerHTML = "Error: " + event.message;
                            }


                            //set message into json after press button "send"
                            document.forms["messages"].onsubmit = function () {
                                let message = {
                                    type: 'message',
                                    user: user,
                                    text: this.msg.value,
                                }
                                socket.send(JSON.stringify(message));
                                $('#msg').val('');      //clear textarea after sending message
                                return false;
                            }


                            //mute after press icon
                            function mute(user) {
                                console.log(user);
                                let message = {
                                    type: 'mute',
                                    user: user,
                                }
                                socket.send(JSON.stringify(message));
                                return false;
                            }


                            //ban after press icon
                            document.getElementById("ban").onclick = function () {
                                let message = {
                                    type: 'ban',
                                    user: user,
                                }
                                socket.send(JSON.stringify(message));
                                return false;
                            }
                        }

                        //get random color
                        function randcolor() {
                            let r = Math.floor(Math.random() * (256));
                            let g = Math.floor(Math.random() * (256));
                            let b = Math.floor(Math.random() * (256));
                            let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
                            return color;
                        }
                    </script>


                    {{--@push('scripts')
                    <script src="js/websocket.js"></script>
                        <script src="js/randcolor.js"></script>
                    @endpush
                    @stack('scripts')--}}

                </div>
            </div>
        </div>
    </div>
@endsection
