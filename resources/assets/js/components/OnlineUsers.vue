<template>
    <div>
        <div class="panel panel-info" v-if="!+adm">

            <div class="panel-heading">Users online:</div>
            <div class="panel-body">

                <div class="form-group">
                    <div v-for="(u, id) in online" :key="id">{{u.name}}</div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "online",
        props: ['online','adm'],
    }
</script>

<style scoped>
</style>