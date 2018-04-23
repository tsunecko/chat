<template>
    <div>
        <div class="panel panel-info" >
            <!--v-if="user === '1'"-->
            <div class="panel-heading">All users:</div>
            <div class="panel-body">

                <div class="form-group">
                    <div v-for="(u, i) in users" :key="i">{{u.name}}
                        <div class="state mute" @click="muteHandler(user.id)">M</div>
                        <div class="state ban" @click="banHandler(user.id)">B</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "user-list",
        props: ['users','user'],
    }
</script>

<style scoped>
    div.state{
        width: 20px;
        height: 20px;
        border-radius: 5px;
        display: inline-block;
        text-align: center;
        color: #FFFFFF;
        font-weight: bold;
        cursor: default;
    }
    div.mute{
        background: #FFA500;
    }
    div.ban{
        background: #FF4500;
    }
</style>