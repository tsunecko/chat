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
                        <label for="exampleFormControlTextarea2">Enter message, {{ Auth::user()->name }}</label>
                        <textarea class="form-control rounded-0" id="exampleFormControlTextarea2" rows="3" placeholder="Your message" name="msg"></textarea>
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
