@extends('layouts.app')

@section('content')

        <example v-bind:current-user='{!! Auth::user()->toJson() !!}'></example>


    {{--<div class="container">--}}
        {{--<div class="row">--}}
            {{--<div class="col-md-8 col-md-offset-2">--}}
                {{--<div class="panel panel-primary">--}}
                    {{--<div class="panel-heading">Chat</div>--}}

                    {{--<div class="panel-body">--}}

                        {{--<form name="messages" id="messages">--}}
                            {{--{{ csrf_field() }}--}}

                            {{--@if ( $type  === 'admin' )--}}
                            {{--<div class="form-group">--}}
                                {{--<label for="allusers">All users:</label>--}}
                                {{--<div id="allusers">--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--@endif--}}

                            {{--<div class="form-group">--}}
                                {{--<label for="online">Online now:</label>--}}
                                {{--<div id="online">--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="form-group">--}}
                                {{--<label for="chat">Chat</label>--}}
                                {{--<div id="chat">--}}
                                    {{--@foreach($messages as $message)--}}
                                        {{--<span style="font-weight: bold"> {{ $message->name }} </span> {{ $message->text }} <br>--}}
                                        {{--@endforeach--}}
                                {{--</div>--}}
                            {{--</div>--}}

                            {{--<div class="form-group">--}}
                                {{--<label for="msg">Enter gfhdfhtyjhdtymessage,</label>--}}
                                {{--<textarea class="form-control rounded-0" id="msg" rows="3" placeholder="Your message"--}}
                                          {{--name="msg"></textarea>--}}
                            {{--</div>--}}

                            {{--<button type="button" class="btn btn-primary sendmsg" id="sendmsg" value="">Send</button>--}}
                        {{--</form>--}}
                        {{--<br>--}}

                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}

    {{--<script--}}
            {{--src="https://code.jquery.com/jquery-3.3.1.min.js"--}}
            {{--integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="--}}
            {{--crossorigin="anonymous">--}}
    {{--</script>--}}

    {{--<script>--}}
        {{--let id = @json($id);--}}
        {{--let token = @json($token);--}}


{{--</script>--}}
{{--<script src="{{ asset('js/websocket.js') }}"></script>--}}
@endsection
