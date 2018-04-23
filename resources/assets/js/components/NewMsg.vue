<template>
    <div>
        <input class="form-control rounded-0" v-model.trim="newMessage" @keyup.enter="newMessageHandler" placeholder="type here" />
    </div>
</template>

<script>
    export default {
        name: "new-msg",
        data(){
            return{
                newMessage: '',
                name: $('.dropdown-toggle').text().trim(),
            }
        },
        methods: {
            newMessageHandler(){
                this.$emit('send', {
                    // type: 'message',
                    // id: id,
                    name: this.name,
                    text: this.newMessage,
                });
                this.newMessage = '';
            }
        }
    }
</script>

<style scoped>
</style>