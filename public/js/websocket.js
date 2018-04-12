window.onload = function () {
    let socket = new WebSocket(`ws://localhost:8080?${token}`);


    //listener - if button clicked - apply the function mute
    $("#allusers").on("click", ".mute", function () {
        let name = $(this).data('name');
        let id = $(this).data('id');
        console.log('1--- mute', name, id);
        mute(name, id);                         //apply function to button
    });

    //listener - if button clicked - apply the function ban
    $("#allusers").on("click", ".ban", function () {
        let name = $(this).data('name');
        let id = $(this).data('id');
        console.log('1--- ban', name, id);
        ban(name, id);                          //apply function to button
    });

    //listener - if button clicked - apply the function sendmsg
    $("#messages").on("click", ".sendmsg", function () {
        let past = localStorage.getItem('sendDate');
        //let id = $(this).data('id');
        if( $("#msg").val().length >= 200) {
            $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Your message is longer than 200 letters!<br>'));
        }
        else if ((new Date - past)/1000 <= 15) {
            $('#chat').append($('<span>').css('font-style','italic').css('color','#A9A9A9').html('Left ' + (15 -(new Date - past)/1000).toFixed(2) + ' sec for sending next message<br>'));
        } else {
            let sendDate = Date.now();
            localStorage.setItem('sendDate', sendDate);
            console.log('1--- msg')
            sendmsg();                          //apply function to button
        }
    });



    socket.onopen = function (event) {
        console.log('---');
    };



    socket.onclose = function (event) {
        console.error(event);
        console.log('-----', event);

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
                let users = data.users;
                console.log('---', users);
                for ( let user of users) {
                    $('#allusers').append($('<span id="allusersnames" class="btn btn-default">' + user.name +
                        ' <button type="button" class="glyphicon glyphicon-volume-off btn-warning btn-xs mute" data-name="' + user.name + '" data-id="' + user.id +
                        '"></button> <button type="button" class="glyphicon glyphicon-remove-circle btn-danger btn-xs ban" data-name="' + user.name + '" data-id="' + user.id + '"></button></span>  '));
                }
                break;


            case 'userlist':
                console.log('---', data.type);
                if ($('#onlinenames')) {
                    $('#online').empty();       //clear area after every update
                }
                for (let user of data.names) {
                    $('#online').append($('<span>').addClass("btn btn-default btn-sm").attr("id", "onlinenames").text(user));
                }
                break;


            case 'online_into_chat':
                $('#chat').append(
                    $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.name + ' is online<br>'));
                break;


            case 'message':
                console.log('3--- msg', data.user);
                $('#chat').append(
                    '<span style="color:' + randcolor() + '; font-weight: bold">' + data.user + '</span>: ' + data.text + '<br>'
                );
                break;


            case 'offline_into_chat':
                $('#chat').append(
                    $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.name + ' is offline<br>'));
                break;


            case 'mute':
                console.log('3--- mute', data.name, data.id);
                if(data.id === id) {
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
                console.log('3--- ban', data.name, data.id);
                if(data.id === id) {
                    window.location.href = '/logout';
                }
                $('#chat').append(
                    $('<span>').css('font-style','italic').css('color','#A9A9A9').html(data.name + ' is baned<br>'));
                break;
        }
    };


    socket.onerror = function (event) {
        console.error(event);
        $('#chat').append(
            $('<span>').html('Error: ' + event.message + '<br>'));
    }

    function randcolor() {
        let r = Math.floor(Math.random() * (246));
        let g = Math.floor(Math.random() * (246));
        let b = Math.floor(Math.random() * (246));
        let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
        return color;
    }

//mute after press icon
    function mute(name, id) {
        console.log('2--- mute', name, id);
        let message = {
            type: 'mute',
            user: name,
            id: id,
        };
        socket.send(JSON.stringify(message));
        return false;
    }


//ban after press icon
    function ban(name, id) {
        console.log('2--- ban', name, id);
        let message = {
            type: 'ban',
            user: name,
            id: id,
        };
        socket.send(JSON.stringify(message));
        return false;
    }

//set message into json after press button "send"
    function sendmsg() {
        console.log('2--- msg');
        let message = {
            type: 'message',
            text: this.msg.value,
        };
        socket.send(JSON.stringify(message));
        $('#msg').val('');      //clear textarea after sending message
        return false;
    }

}