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
                            <div v-for="(msg, i) in messages" :key="i">
                                <div v-if="msg.type === 'italics'" v-bind:class="{italics:italics}">
                                    {{msg.name}} {{msg.text}}
                                </div>
                                <div v-else-if="msg.type === 'cloud'" v-bind:class="{cloud:cloud, youreMsg:youreMsg}">
                                    <span class="name" v-bind:style="{color: randomColor()}">{{msg.name}}</span> {{msg.text}}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea class="form-control rounded-0" v-model="newMessage" @keyup.enter="newMessageHandler" placeholder="type here"></textarea>
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
                                <div class="mute" @click="muteHandler(user.id)">M</div>
                                <div class="ban" @click="banHandler(user.id)">B</div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel panel-info" v-if="currentUser.admin === '0'">
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
                        // if(data.name === this.name) {
                        //     window.location.href = '/logout';
                        // }
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
                name: $('.dropdown-toggle').text().trim(),
                newMessage: '',
                cloud: true,
                italics: true,
                youreMsg: true,
                aliensMsg: true,
            }
        },

        methods: {
            newMessageHandler () {
                socket.send(JSON.stringify({
                    type: 'message',
                    text: this.newMessage,
                }));
                this.newMessage = '';
            },
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
            randomColor(){
                let r = Math.floor(Math.random() * (246));
                let g = Math.floor(Math.random() * (246));
                let b = Math.floor(Math.random() * (246));
                let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
                return color;
            }
        },
    }
</script>



<style>
    div.italics{
        font-style: italic;
        color: #A9A9A9;
    }
    div.cloud{
        padding: 10px;
        margin: 5px 0px;
        border-radius: 10px;
        display: inline-block;
    }
    span.name{
        font-weight: bold;
    }
    div.mute{
        width: 20px;
        height: 20px;
        background: #FFA500;
        border-radius: 5px;
        display: inline-block;
        text-align: center;
        color: #FFFFFF;
        font-weight: bold;
        cursor: default;
    }
    div.ban{
        width: 20px;
        height: 20px;
        background: #FF4500;
        border-radius: 5px;
        display: inline-block;
        text-align: center;
        color: #FFFFFF;
        font-weight: bold;
        cursor: default;
    }
    div.youreMsg{
        background: #d9edf7;
    }
    div.aliensMsg{
        background: #428bca;
    }
</style>