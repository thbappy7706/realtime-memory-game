<div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300"
     x-data="{
    init() {
        console.log('Alpine memory game component initialized!');
        this.$nextTick(() => {
            console.log('Setting up Livewire listeners and Echo');
            this.setupLivewireListeners();
            this.setupEchoConnection();
        });
    },

    setupLivewireListeners() {
        if(this.$wire) {
            this.$wire.on('cards-flipped', () => {
                setTimeout(() => {
                    this.$wire.call('checkMatch');
                }, 2000);
            });

            this.$wire.on('flip-cards-back', () => {
                setTimeout(() => {
                    this.$wire.call('flipCardsBack');
                }, 2000);
            });
        }
    },

    setupEchoConnection() {
        if(window.Echo && this.$wire) {
            console.log('Setting up Echo connection');
            const gameId = {{$game->id}};

            window.Echo.channel(`game.${gameId}`)
            .listen('GameUpdated', (data) => {
                console.log('GameUpdated event received:', data);
                this.$wire.$refresh();
            })
        }
    }
}">

    <div class="max-w-7xl mx-auto p-4 lg:p-6">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{$game->name}}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Memory Card Game</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm font-medium">
                        Turn {{ $game->turn_count }}
                    </span>

                    @if ($game->status == 'new')
                        <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full text-sm font-semibold">
                            Waiting for players
                        </span>
                    @elseif($game->status == 'playing')
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-semibold">
                            Playing
                        </span>
                    @else
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold">
                            Finished
                        </span>
                    @endif
                </div>
            </div>

            <!-- Game Message -->
            @if ($gameMessage)
                <div class="mt-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        {{ $gameMessage }}
                    </div>
                </div>
            @endif
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            <!-- Main Game Area -->
            <div class="lg:col-span-3">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    @if ($game->status === 'new')
                        <!-- Waiting Room -->
                        <div class="text-center py-12">
                            <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Waiting for Players</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">
                                Need at least 2 players to start. Current players: {{ $game->players()->count() }}
                            </p>
                            @if ($game->players()->count() >= 2 && auth()->user()->id == $game->current_player_id)
                                <button
                                    wire:click="startGame"
                                    class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-bold py-3 px-8 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    Start Game
                                </button>
                            @endif
                        </div>
                    @elseif (count($cards) > 0)
                        <!-- Game Board -->
                        <div class="flex justify-center">
                            <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3 max-w-4xl">
                                @foreach ($cards as $card)
                                    <div
                                        wire:click="flipCard({{$card['id']}})"
                                        class="aspect-square flex cursor-pointer items-center justify-center border-2 rounded-xl transition-all duration-300 text-xl font-bold shadow-lg hover:shadow-xl transform hover:scale-105
                                                @if ($card['is_matched'])
                                                    border-green-500 bg-green-100 dark:bg-green-900 text-green-900 dark:text-green-100
                                                @elseif ($card['show_value'])
                                                    border-blue-500 bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100
                                                @else
                                                    border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600
                                                @endif
                                                @if ($isProcessing || $game->current_player_id !== auth()->id() || $game->status !== 'playing')
                                                    cursor-not-allowed opacity-50 hover:scale-100 hover:shadow-lg
                                                @endif
                                                "
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-90"
                                        x-transition:enter-end="opacity-100 scale-100">
                                        @if($card['show_value'])
                                            <span class="text-2xl font-bold">{{ $card['value'] }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 text-2xl">?</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Players List -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        Players ({{ $game->players->count() }})
                    </h3>
                    <div class="space-y-3">
                        @foreach ($game->players as $player)
                            <div class="flex items-center p-4 justify-between rounded-lg transition-colors duration-200
                                        @if ($game->current_player_id == $player->user_id && $game->status === 'playing')
                                            bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700
                                        @else
                                            bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600
                                        @endif">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                        {{$player->user->initials()}}
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-white block">{{$player->user->name}}</span>
                                        @if ($game->current_player_id == $player->user_id && $game->status === 'playing')
                                            <span class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full font-semibold mt-1 inline-block">
                                                Current Player
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold text-gray-900 dark:text-white block">Score: {{ $player->score }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">matches</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Game Info -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Game Info
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                            <span class="text-gray-600 dark:text-gray-400">Grid Size:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{$game->grid_size}}x{{$game->grid_size}}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                            <span class="text-gray-600 dark:text-gray-400">Total Cards:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{$game->grid_size * $game->grid_size}}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 dark:text-gray-400">Players:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{$game->players->count()}}/{{$game->max_players}}</span>
                        </div>
                    </div>
                </div>

                <!-- Back to Lobby -->
                <div class="text-center">
                    <a
                        href="{{route('games.lobby')}}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-lg transition-colors duration-200 font-semibold shadow-lg hover:shadow-xl">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Game Lobby
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
