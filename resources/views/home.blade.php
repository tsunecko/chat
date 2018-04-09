@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Chat</div>

                    <div class="panel-body">

                        <form name="messages" id="messages" action="" method="">
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

                            <button type="button" class="sendmsg" id="sendmsg" value="">Send</button>
                        </form>
                        <br>

                        <div class="form-group">
                            <label for="chat">Chat</label>
                            <div id="chat"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous">
    </script>

    <script>
        let csrfToken = document.head.querySelector('meta[name="csrf-token"]').content
        let socket = new WebSocket('ws://localhost:8080?{{ $token }}');
        let chat = document.querySelector("#chat");

        let user = @json($username);

        window.onload = function () {

            // let postData = {
            //     'X-CSRF-TOKEN': csrfToken
            // }
            // console.log('---', postData)
            // $.post('/logout',postData )

            //listener - if button clicked - apply the function mute
            $("#online").on("click", ".mute", function () {
                let user = $(this).data('user');    //get data from button object
                console.log('--- mute', user)
                mute(user);                         //apply function to button
            });

            //listener - if button clicked - apply the function ban
            $("#online").on("click", ".ban", function () {
                let user = $(this).data('user');    //get data from button object
                console.log('--- ban', user)
                ban(user);                          //apply function to button
            });

            //listener - if button clicked - apply the function sendmsg
            $("#messages").on("click", ".sendmsg", function () {
                //let user = $(this).data('user');    //get data from button object
                console.log('--- msg', user)
                sendmsg();                          //apply function to button
            });

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
                            //console.log('test', user); //udalit id
                            $('#online').append($('<span id="onlinenames" class="btn btn-default">' + user +
                                ' <button type="button" class="glyphicon glyphicon-volume-off mute" data-user="' + user + '"></button> ' +
                                '<button type="button" class="glyphicon glyphicon-remove-circle ban" data-user="' + user + '"></button>' + ' </span>'));
                        }
                        break;

                    //print into chat user is online
                    case 'online_into_chat':
                        $('#chat').append(
                            '<span style="color:#A9A9A9; font-style: italic">' + data.islogin + ' is online</span><br>'
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
                        if(data.user === user) {

                        }
                        $('#chat').append(
                            '<span style="color:#A9A9A9; font-style: italic">' + data.user + ' is muted</span><br>'
                        );
                        break;

                    //ban
                    case 'ban':
                        //console.log('---', data.user, user)
                        if(data.user === user) {
                            window.location.href = '/logout';
                        }
                        $('#chat').append(
                            '<span style="color:#A9A9A9; font-style: italic">' + data.user + ' is baned</span><br>'
                        );
                        break;
                }
            };

            //errors
            socket.onerror = function (event) {
                chat.innerHTML = "Error: " + event.message;
            }

            //set message into json after press ANY button "send"
            // document.messages.onsubmit = function () {
            //     let message = {}
            //     socket.send(JSON.stringify(message));
            //     return false;
            // }
        }

        //get random color
        function randcolor() {
            let r = Math.floor(Math.random() * (256));
            let g = Math.floor(Math.random() * (256));
            let b = Math.floor(Math.random() * (256));
            let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
            return color;
        }

        //mute after press icon
        function mute(user) {
            let message = {
                type: 'mute',
                user: user,
            };
            socket.send(JSON.stringify(message));
            return false;
        }


        //ban after press icon
        function ban(user) {
            let message = {
                type: 'ban',
                user: user,
            };
            socket.send(JSON.stringify(message));
            //logout and redirect into login form
            //window.location.href = "{{ route('logout') }}";
            //document.getElementById('logout-form').submit();
            return false;
        }

        //set message into json after press button "send"
        function sendmsg() {
            let message = {
                type: 'message',
                user: user,
                text: this.msg.value,
            };
            socket.send(JSON.stringify(message));
            $('#msg').val('');      //clear textarea after sending message
            return false;
        }
    </script>
@endsection
