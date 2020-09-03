<template>
    <div>
        <div class="form-group">
            <router-link :to="{name: 'createBoard'}" class="btn btn-success">Create new board</router-link>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Boards list</div>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th></th>
                        <th>Email</th>
                        <th width="100">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="board, index in boards">
                        <td>{{ board.name }}</td>
                        <td>
                            <router-link :to="{name: 'editboard', params: {id: board.id}}" class="btn btn-xs btn-default">
                                Edit
                            </router-link>
                            <a href="#"
                               class="btn btn-xs btn-danger"
                               v-on:click="deleteEntry(board.id, index)">
                                Delete
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                boards: []
            }
        },
        mounted() {
            var app = this;
            axios.get('/api/boards')
                .then(function (resp) {
                    app.boards = resp.data;
                })
                .catch(function (resp) {
                    console.log(resp);
                    alert("Could not load boards");
                });
        },
        methods: {
            deleteEntry(id, index) {
                if (confirm("Do you really want to delete it?")) {
                    var app = this;
                    axios.delete('/api/boards/' + id)
                        .then(function (resp) {
                            app.boards.splice(index, 1);
                        })
                        .catch(function (resp) {
                            alert("Could not delete board");
                        });
                }
            }
        }
    }
</script>
