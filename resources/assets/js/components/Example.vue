<template>
    <div class="container">

        <div class="row">
            <div class="col-md-8 col-md-offset-1">
                <div class="panel panel-primary">
                    <div class="panel-heading">Chat</div>
                        <div class="panel-body">

                            <div style="display:none" id="token">{{ currentUser.token }}</div>

                            <div class="form-group">
                                <label>Messages:</label>
                                <div v-for="(msg, i) in messages" :key="i">{{msg.name}} {{msg.text}}</div>
                            </div>

                            <div class="form-group">
                                <textarea class="form-control rounded-0" v-model="newMessage" @keyup.enter="newMessageHandler"></textarea>
                            </div>

                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-3 col-xs-6">
                <div class="panel panel-info" v-if="currentUser.admin === '1'">
                    <div class="panel-heading">All users:</div>
                        <div class="panel-body">

                            <div class="form-group">
                                <div v-for="(user, i) in users" :key="i">{{user.name}}
                                    <button type="button" class="glyphicon glyphicon-volume-off btn-warning btn-xs" v-on:click="muteHandler(user.name, user.id)"></button>
                                    <button type="button" class="glyphicon glyphicon-remove btn-xs btn-danger" v-on:click="banHandler(user.name, user.id)"></button>
                                </div>
                            </div>

                        </div>
                </div>

                <div class="panel panel-info">
                    <div class="panel-heading">Users online:</div>
                    <div class="panel-body">

                        <div class="form-group">
                            <div v-for="(user, id) in online" :key="id">{{user.name}}</div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</template>



<script>

    let token = null;
    let socket = null;

        export default {

            props: ['currentUser'],

            mounted() {

                console.log('Component mounted.');
                token = $('#token').text();
                socket = new WebSocket(`ws://localhost:8080/?${token}`);

                socket.onopen = (event) => {
                    console.log("Connection established!");
                };

                socket.onmessage = (event) => {

                    let data = JSON.parse(event.data);
                    console.log(data);
                    switch (data.type) {
                        case('online'):
                            this.messages.push({
                                name: data.name,
                                text: 'is online'
                            });
                            break;
                        case('offline'):
                            this.messages.push({
                                name: data.name,
                                text: 'is offline'
                            });
                            break;
                        case('message'):
                            this.messages.push({
                                name: data.name + ':',
                                text: data.text
                            });
                            break;
                        case('userlist'):
                            this.online = [];
                            for (let user of data.names) {
                                this.online.push({
                                    name: user
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
                            this.messages.push({
                                name: data.name,
                                text: 'is muted'
                            });
                            break;
                        case('ban'):
                            if(data.name === $('.dropdown-toggle').text()) {
                                window.location.href = '/logout';
                            }
                            this.messages.push({
                                name: data.name,
                                text: 'is banned'
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
                    name: $('.dropdown-toggle').text(),
                    newMessage: '',
                }
            },

            methods: {
                newMessageHandler () {
                    socket.send(JSON.stringify({
                        type: 'message',
                        text: this.newMessage
                    }));
                    this.newMessage = '';
                },
                muteHandler (name, id) {
                    console.log("---", name, id);
                    socket.send(JSON.stringify({
                        type: 'mute',
                        name: name,
                        id: id
                    }));
                },
                banHandler (name, id) {
                    console.log("---", name, id);
                    socket.send(JSON.stringify({
                        type: 'ban',
                        name: name,
                        id: id
                    }));
                },
            },
    }
</script>



<style>
</style>