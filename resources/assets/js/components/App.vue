<template>
    <div class="container">

        <div class="row">

            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-primary">
                    <div class="panel-heading">Chat</div>
                    <div class="panel-body">
                        <div style="display:none" id="token">{{ currentUser.token }}</div>
                        <div style="display:none" id="id">{{ currentUser.id }}</div>
                        <div style="display:none" id="user">{{ currentUser.admin }}</div>

                        <div class="form-group">
                            <label>Messages:</label>
                            <chat :messages="messages"/>

                        </div>

                        <div class="form-group">
                            <new-msg v-on:send="addMsg"/>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-3 col-xs-6">
                <users-list :users="users"/>
                <online-users :online="online"/>
            </div>

        </div>
    </div>
</template>



<script>

    let token = null;
    let socket = null;
    let id = null;

    import Chat from './Chat.vue'
    import OnlineUsers from './OnlineUsers.vue'
    import UsersList from './UsersList.vue'
    import NewMsg from "./NewMsg";

    export default {

        components: {
            NewMsg,
            OnlineUsers,
            UsersList,
            Chat,
        },

        props: ['currentUser'],

        mounted() {

            console.log('Component mounted.');
            token = $('#token').text();
            id = $('#id').text();
            socket = new WebSocket(`ws://localhost:8080/?${token}`);

            socket.onopen = (event) => {
                console.log("Connection established!");
            };

            socket.onmessage = (event) => {

                let data = JSON.parse(event.data);
                console.log(data, user);
                switch (data.type) {
                    case('online'):
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is online',
                        });
                        break;
                    case('offline'):
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is offline',
                        });
                        break;
                    case('message'):
                        this.messages.push({
                            type: 'cloud',
                            name: data.name + ':',
                            text: data.text,
                        });
                        break;
                    case('userlist'):
                        this.online = [];
                        for (let user of data.names) {
                            this.online.push({
                                name: user,
                            });
                        }
                        break;
                    case('users'):
                        this.users = [];
                        for (let user of data.names) {
                            this.users.push({
                                name: user.name,
                                id: user.id,
                            });
                        }
                        break;
                    case('mute'):
                        if(data.name === this.name) {
                            alert('muted!');
                        }
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is muted',
                        });
                        break;
                    case('unmute'):
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is unmuted',
                        });
                        break;
                    case('ban'):
                        if(data.name === this.name) {
                            window.location.href = '/logout';
                        }
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is banned',
                        });
                        break;
                    case('unban'):
                        this.messages.push({
                            type: 'italics',
                            name: data.name,
                            text: 'is unbanned',
                        });
                        break;
                }
            };

            socket.onclose = (event) => {
                console.log('Chat close');
            };
        },

        data () {
            return {
                messages: [],
                online: [],
                users: [],
                user: $('#user').text(),
                name: $('.dropdown-toggle').text().trim(),
                newMessage: '',
            }
        },

        methods: {
            muteHandler (id) {
                socket.send(JSON.stringify({
                    type: 'mute',
                    id: id,
                }));
            },
            banHandler (id) {
                socket.send(JSON.stringify({
                    type: 'ban',
                    id: id,
                }));
            },
            addMsg (data){
                socket.send(JSON.stringify({
                    type: 'message',
                    id: id,
                    name: data.name,
                    text: data.text,
                }));
            }
        },
    }
</script>


<style>
</style>