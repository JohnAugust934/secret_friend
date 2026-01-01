@props(['messages' => []])

<div x-data="{
        notifications: [],
        add(message, type = 'success') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => this.remove(id), 5000); // 5 segundos
        },
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }"
    @notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed top-4 right-4 z-[100] space-y-3 w-full max-w-xs pointer-events-none"
    {{-- CORREÇÃO: Usamos aspas simples ' no x-init para não conflitar com as aspas duplas " do JSON --}}
    x-init='
        @if(session("success")) add(@json(session("success")), "success"); @endif
        @if(session("error")) add(@json(session("error")), "error"); @endif
        @if(session("info")) add(@json(session("info")), "info"); @endif
        @if(session("warning")) add(@json(session("warning")), "warning"); @endif
    '>
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-90"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-90"
            class="pointer-events-auto relative w-full p-4 rounded-2xl shadow-2xl border backdrop-blur-xl flex items-start gap-3 transform transition-all hover:scale-[1.02] cursor-pointer"
            @click="remove(notification.id)"
            :class="{
                'bg-white/90 dark:bg-gray-800/90 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200': notification.type === 'success',
                'bg-white/90 dark:bg-gray-800/90 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200': notification.type === 'error',
                'bg-white/90 dark:bg-gray-800/90 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200': notification.type === 'info',
                'bg-white/90 dark:bg-gray-800/90 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200': notification.type === 'warning',
             }">
            <div class="shrink-0 pt-0.5">
                <template x-if="notification.type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'info'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'warning'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </template>
            </div>

            <div class="flex-1">
                <p class="text-sm font-bold" x-text="notification.type === 'success' ? 'Sucesso' : (notification.type === 'error' ? 'Erro' : (notification.type === 'warning' ? 'Atenção' : 'Info'))"></p>
                <p class="text-xs mt-0.5 leading-relaxed opacity-90 break-all" x-text="notification.message"></p>
            </div>

            <button class="text-current opacity-50 hover:opacity-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </template>
</div>