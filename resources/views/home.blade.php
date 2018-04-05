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
                            {{--{{ $onlineUsers }}--}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="msg">Enter message, {{ $username }}</label>
                        <textarea class="form-control rounded-0" id="msg" rows="3" placeholder="Your message" name="msg"></textarea>
                    </div>

                    <input type="submit" value="Send"/>
                    </form><br>

                <div class="form-group">
                    <label for="status">Chat</label>
                    <div id="status"></div>
                </div>

                </div>

                <script>
                    window.onload = function() {

                        let socket = new WebSocket('ws://localhost:8080?{{ $token }}');
                        let status = document.querySelector("#status");

                        let user = @json($username);
                        let names = @json($onlineUsers).map(event => event.name);

                        let buttonMute = '<button type="button" class="btn btn-warning btn-xs">Mute</button> ';
                        let buttonBan = '<button type="button" class="btn btn-danger btn-xs">Ban</button> ';





                        //open connection
                        socket.onopen = function(event) {


                            let message = {
                                "islogin": user
                            }
                            socket.send(JSON.stringify(message));



                            status.innerHTML = "<span style=\"color:#A9A9A9; font-style: italic\"> Connection established! </span><br>";

                            //show online users with buttons
                            for(let name of names) {
                                document.getElementById('online').innerHTML = buttonMute + buttonBan + name + ', ';
                            }

                            // Save data to the current local store
                            //localStorage.setItem("token", "John");
                        };

                        //close connection
                        socket.onclose = function(event) {
                            if(event.wasClean){
                                status.innerHTML = "Connection close";
                            } else {
                                status.innerHTML = "Connection close for some reason!";
                            };
                            status.innerHTML += "<br>error code: " + event.code + "<br>reason: " + event.reason;
                        }

                        //get data
                        socket.onmessage = function(event){

                            let message = JSON.parse(event.data);
                            //print logic for different data from json
                            if (message.islogin) {
                                status.innerHTML += '<span style="color:#A9A9A9; font-style: italic">' + message.islogin + ' is online </span><br>';
                            } else {
                                status.innerHTML += '<span style="color:' + randcolor() + '"><b>' + message.user + '</b></span>: ' + message.msg + '<span style="color:#A9A9A9; font-style: italic"> ' + message.hour + ":" + message.min + '</span><br>';
                            }
                        };

                        //errors
                        socket.onerror = function(event){
                            status.innerHTML = "Error: " + event.message;
                        }

                        //set message into json after press button "send"
                        document.forms["messages"].onsubmit = function() {
                            let d = new Date();
                            let message = {
                                user: user,
                                msg: this.msg.value,
                                hour: d.getHours(),
                                min: d.getMinutes()
                            }
                            socket.send(JSON.stringify(message));
                            return false;
                        }
                    }

                    //get random color
                    function randcolor(){
                        let r=Math.floor(Math.random() * (256));
                        let g=Math.floor(Math.random() * (256));
                        let b=Math.floor(Math.random() * (256));
                        let c='#' + r.toString(16) + g.toString(16) + b.toString(16);
                        return c;
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
