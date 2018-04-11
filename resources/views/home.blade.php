@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-heading">Chat</div>

                    <div class="panel-body">

                        <form name="messages" id="messages" action="/chat/message" method="post">
                            {{ csrf_field() }}

                            @if ( $type  === 'admin' )
                            <div class="form-group">
                                <label for="allusers">All users:</label>
                                <div id="allusers">
                                </div>
                            </div>
                            @endif

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

                            <button type="button" class="btn btn-primary sendmsg" id="sendmsg" value="">Send</button>
                        </form>
                        <br>

                        <div class="form-group">
                            <label for="chat">Chat</label>
                            <div id="chat">
                                {{--@foreach($messages as $message)
                                    <span style="font-weight: bold"> {{ $message->name }} </span> {{ $message->text }} <br>
                                    @endforeach--}}
                            </div>
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

        let token = @json($token);
        let socket = new WebSocket('ws://localhost:8080?{{ $token }}');

        let user = @json($username);

        window.onload = function () {


            //listener - if button clicked - apply the function mute
            $("#allusers").on("click", ".mute", function () {
                let name = $(this).data('name');
                let token = $(this).data('token');
                console.log('--- mute', token, name);
                mute(token, name);                         //apply function to button
            });

            //listener - if button clicked - apply the function ban
            $("#allusers").on("click", ".ban", function () {
                let name = $(this).data('name');
                let token = $(this).data('token');
                console.log('--- ban', token, name);
                ban(token, name);                          //apply function to button
            });

            //listener - if button clicked - apply the function sendmsg
            $("#messages").on("click", ".sendmsg", function () {
                let past = localStorage.getItem('sendDate');
                if( $("#msg").val().length >= 200) {
                    $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Your message is longer than 200 letters!<br>'));
                }
                else if ((new Date - past)/1000 <= 15) {
                    $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Left ' + (15 -(new Date - past)/1000).toFixed(2) + ' sec for sending next message<br>'));
                } else {
                    let sendDate = Date.now();
                    localStorage.setItem('sendDate', sendDate);
                    console.log('--- msg', user)
                    sendmsg();                          //apply function to button
                }
            });



            socket.onopen = function (event) {

                //send json into back when user online
                let message = {
                    "type": 'online_into_chat',
                    "islogin": user,
                }
                socket.send(JSON.stringify(message));
            };



            socket.onclose = function (event) {
                if (event.wasClean) {
                    $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Connection close'));
                } else {
                    $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Connection close for some reason!'));
                }
                $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('<br>error code: ' + event.code + '<br>reason: ' + event.reason));
            }



            socket.onmessage = function (event) {

                let data = JSON.parse(event.data);

                switch (data.type) {


                    case 'allusers':
                        if ($('#allusers')) {
                            $('#allusers').empty();       //clear area after every update
                        }
                        console.log('---', data.type);
                        let users = data.users.data;
                        console.log('---', users);
                        for ( let user of users) {
                            $('#allusers').append($('<span id="allusersnames" class="btn btn-default">' + user.name +
                                ' <button type="button" class="glyphicon glyphicon-volume-off btn-warning btn-xs mute" data-name="' + user.name + '" data-token="' + user.token +
                                '"></button> <button type="button" class="glyphicon glyphicon-remove-circle btn-danger btn-xs ban" data-name="' + user.name + '" data-token="' + user.token + '"></button></span>  '));
                        }
                        break;


                    case 'userlist':
                        console.log(data.list.names);
                        if ($('onlinenames')) {
                            $('#online').empty();       //clear area after every update
                        }
                        for (let user of data.list.names) {
                            $('#online').append($('<span>').addClass("btn btn-default btn-sm").attr("id", "onlinenames").text(user));
                        }
                        break;


                    case 'online_into_chat':
                        $('#chat').append(
                            $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.islogin + ' is online<br>'));
                        break;


                    case 'message':
                        $('#chat').append(
                            '<span style="color:' + randcolor() + '; font-weight: bold">' + data.user + '</span>: ' + data.text + '<br>'
                        );
                        break;


                    case 'offline_into_chat':
                        $('#chat').append(
                            $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.islogout + ' is offline<br>'));
                        break;


                    case 'mute':
                        if(data.token === token) {
                            $('#chat').append($('<span>').css('color','#8B0000').css('font-weight','700').html('You are muted!<br>'));
                            $('#sendmsg').attr('disabled','disabled');
                        }
                        $('#chat').append(
                            $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.name + ' is muted<br>'));
                        break;


                    case 'stillMuted':
                        $('#sendmsg').attr('disabled','disabled');
                        break;


                    case 'ban':
                        if(data.token === token) {
                            window.location.href = '/logout';
                        }
                        $('#chat').append(
                            $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.name + ' is baned<br>'));
                        break;
                }
            };


            socket.onerror = function (event) {
                $('#chat').append(
                    $('<span>').html('Error: ' + event.message + '<br>'));
            }

        }


        function randcolor() {
            let r = Math.floor(Math.random() * (246));
            let g = Math.floor(Math.random() * (246));
            let b = Math.floor(Math.random() * (246));
            let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
            return color;
        }

        //mute after press icon
        function mute(token, name) {
            let message = {
                type: 'mute',
                user: name,
                token: token,
            };
            socket.send(JSON.stringify(message));
            return false;
        }


        //ban after press icon
        function ban(token, name) {
            let message = {
                type: 'ban',
                user: name,
                token: token,
            };
            socket.send(JSON.stringify(message));
            return false;
        }

        //set message into json after press button "send"
        function sendmsg() {
            let message = {
                type: 'message',
                user: user,
                text: this.msg.value,
                token: token,
            };
            socket.send(JSON.stringify(message));
            $('#msg').val('');      //clear textarea after sending message
            return false;
        }
    </script>
@endsection
