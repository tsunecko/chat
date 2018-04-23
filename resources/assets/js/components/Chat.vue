<template>
    <div>
        <div v-for="(msg, i) in messages" :key="i">
            <div v-if="msg.type === 'italics'" v-bind:class="{italics:italics}">
                {{msg.name}} {{msg.text}}
            </div>
            <div v-else="msg.type === 'cloud'" v-bind:class="{cloud:cloud, youreMsg:youreMsg}">
                <span class="name" v-bind:style="{color: randomColor()}">{{msg.name}}</span> {{msg.text}}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "chat",
        props: ['messages'],
        data() {
            return {
                cloud: true,
                italics: true,
                youreMsg: true,
                aliensMsg: true,
            }
        },
        methods: {
            randomColor(){
                let r = Math.floor(Math.random() * (246));
                let g = Math.floor(Math.random() * (246));
                let b = Math.floor(Math.random() * (246));
                let color = '#' + r.toString(16) + g.toString(16) + b.toString(16);
                return color;
            },
        }
    }

</script>

<style scoped>
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
    div.youreMsg{
        background: #d9edf7;
    }
    div.aliensMsg{
        background: #428bca;
    }
</style>