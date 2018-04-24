<template>
    <div>
        <div v-for="(msg, i) in messages" :key="i">
            <div v-if="msg.type === 'italics'" :class="{italics:italics}">
                {{msg.name}} {{msg.text}}
            </div>
            <div v-else="msg.type === 'cloud'" :class="{cloud:cloud, youreMsg:youreMsg}">
                <span class="name" :style="{color: randomColor()}"> {{msg.name}} </span> {{msg.text}}
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
            }
        },
        methods: {
            randomColor(){
                let rand = () => {
                    return Math.floor(Math.random() * (246));
                };
                let r = rand(); let g = rand(); let b = rand();
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
</style>