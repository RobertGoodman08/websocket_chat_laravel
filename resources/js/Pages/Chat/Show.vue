<template>
    <div class="flex items-start">
        <div class="w-3/4 p-4 mr-4 bg-white border border-gray-200">
            <h3 class="text-gray-700 mb-4 text-lg">{{ chat.title ?? 'Your chat' }}</h3>
            <div ref="message_box" class="mb-4" v-if="messages">
                <div v-if="!isLastPage" class="text-center mb-2">
                    <a @click.prevent="getMessages"
                       class="inline-block bg-sky-600 text-white text-xs px-3 py-2 rounded-lg" href="#">Load more</a>
                </div>
                <div v-for="message in messages.slice().reverse()" :class="message.is_owner ? ' text-right': ''">
                    <div :class="['p-4 mb-4 border inline-block',
                    message.is_owner ? 'bg-green-50 border-green-100': 'bg-sky-50 border-sky-100']">
                        <p class="text-sm">{{ message.user_name }}</p>
                        <p class="mb-2">{{ message.body }}</p>
                        <p class="text-xs italic">{{ message.time }}</p>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-gray-700 mb-4 text-lg">Send message</h3>
                <div>
                    <div class="mb-4">
                        <input placeholder="message" class="rounded-full border border-gray-400" type="text"
                               v-model="body">
                    </div>
                    <div>
                        <a @click.prevent="store"
                           class="inline-block bg-indigo-600 text-white text-xs px-3 py-2 rounded-lg" href="#">Send</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-1/4 p-4 bg-white border border-gray-200">

            <h3 class="text-gray-700 mb-4 text-lg">Users</h3>
            <div v-if="users">
                <div v-for="user in users" class="flex items-center pb-4 mb-4 border-b border-gray-300">
                    <p class="mr-2">{{ user.id }}</p>
                    <p class="mr-4">{{ user.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Main from "@/Layouts/Main.vue"

export default {
    name: "Show",

    props: [
        'chat',
        'users',
        'messages',
        'isLastPage'
    ],

    created() {
        window.Echo.channel(`store-message.${this.chat.id}`)
            .listen('.store-message', res => {
                this.messages.unshift(res.message)
                if (this.$page.url === `/chats/${this.chat.id}`
                ) {
                    axios.patch('/message_statuses', {
                        message_id: res.message.id,
                        user_id: this.$page.props.auth.user.id
                    })
                    .catch( error => {

                    });
                }
            })
    },

    data() {
        return {
            body: '',
            page: 1,
        }
    },

    layout: Main,

    computed: {
        userIds() {
            return this.users.map(user => {
                return user.id
            }).filter(userId => {
                return userId !== this.$page.props.auth.user.id
            })
        },
    },


    methods: {
        store() {
            axios.post('/messages', {
                chat_id: this.chat.id,
                body: this.body,
                user_ids: this.userIds
            })
                .then(res => {
                    this.messages.unshift(res.data)
                    this.body = ''
                })
        },

        getMessages() {
            axios.get(`/chats/${this.chat.id}?page=${++this.page}`)
                .then(res => {
                    this.messages.push(...res.data.messages)
                    this.$page.props.isLastPage = res.data.is_last_page
                })
        },
    }

}
</script>

<style scoped>

</style>
