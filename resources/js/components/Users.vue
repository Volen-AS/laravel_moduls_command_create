<template>
    <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    Laravel
                </div>

                <div class="links">
                    <a href="https://laravel.com/docs">Documentation</a>
                    <a href="https://laracasts.com">Laracasts</a>
                    <a href="https://laravel-news.com">Articles</a>
                    <a href="https://nova.laravel.com">Nova</a>
                    <a href="https://forge.laravel.com">Forge</a>
                    <a href="https://github.com/laravel/laravel">GitHub</a>
                </div>
            </div>
            <ul>
                <li v-for="user in users">{{user.email}}</li>
            </ul>
        </div>
</template>


<script>
import axios from 'axios';
import r from '../route';
    export default {
        data () {
            return {
                users : []
            }
        },
        computed : {
            cUsers : function () {
                return this.users;
            }
        },
        methods: {
            getUsers () {
                axios.get(r("users.index"))
                .then((response) => {
                    this.users = response.data.users
                });
                ;

            }
        },
        mounted() {
            
            this.getUsers();
        }
    }
</script>
