<template>
    <div>
        <div class="panel panel-info">
            <!--v-if="user === '0'"-->
            <div class="panel-heading">Users online:</div>
            <div class="panel-body">

                <div class="form-group">
                    <div>{{user}}</div>
                    <div v-for="(u, id) in online" :key="id">{{u.name}}</div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "online",
        props: ['online','user'],

    }
</script>

<style scoped>
</style>