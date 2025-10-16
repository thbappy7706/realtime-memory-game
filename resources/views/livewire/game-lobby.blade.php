<div class="max-w4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold leading-tight text-gray-900 dark:text-white">
            Memory Game Lobby
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Play a game of memory with your friends.
        </p>

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mx-auto p-6 items-start">

{{--        Create Game Form--}}
        <div class="card">
            <header>
                <h2>Create Games</h2>
                <p>Enter your game name below to create</p>
            </header>
            <section>
                <form wire:submit.prevent="createGame" class="form grid gap-6">
                    <div class="grid gap-2">
                        <label for="demo-card-form-email">Game Name</label>
                        <input type="text" id="gameName" placeholder="Enter game name" class="input"
                               wire:model.live="gameName" @error('gameName') aria-invalid="true" @enderror>
                        @error('gameName')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn w-fit" wire:loading.attr="disabled" wire:target="createGame">
                                <span class="inline-flex items-center">
                                    <!-- Updated Spinner -->
                                    <svg wire:loading wire:target="createGame" xmlns="http://www.w3.org/2000/svg"
                                         width="20" height="20"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round"
                                         stroke-linejoin="round" class="animate-spin mr-2 text-current">
                                        <path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path
                                            d="m16.2 16.2 2.9 2.9"/><path
                                            d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path
                                            d="m4.9 4.9 2.9 2.9"/>
                                    </svg>

                                    <span wire:loading.remove wire:target="createGame">Create Game</span>
                                    <span wire:loading wire:target="createGame">Creating...</span>
                                </span>
                    </button>


                </form>


            </section>

        </div>

{{--        Join Game List--}}
        <div class="card">
            <header>
                <h2>Available Games</h2>
                @if($games->isEmpty())
                    <p class="text-sm text-gray-500">No games available at the moment</p>
                @else
                    <p>Select a game from the list below to join</p>

                @endif
            </header>
            <section>

                @foreach($games as $key => $game)
                    <div class="card mt-3" wire:key="game-{{ $game->id }}">
                        <header>
                            <div class="flex justify-between">
                                <h2>Game Name: {{ $game->name }}</h2>
                                <span class="text-sm">
                    {{ $game->players()->count() }}/{{ $game->max_players }} players
                </span>
                            </div>
                            @foreach ($game->players as $player)
                                <p>{{ $player->user->name }}</p>
                            @endforeach
                        </header>

                        <footer class="flex items-center">
                            <button
                                @if ($game->players()->count() == $game->max_players) disabled @endif
                            wire:click="joinGame({{ $game->id }})"
                                class="btn w-fit"
                                wire:loading.attr="disabled"
                                wire:target="joinGame({{ $game->id }})"
                            >
                <span class="inline-flex items-center">
                    <!-- Spinner -->
                    <svg
                        wire:loading
                        wire:target="joinGame({{ $game->id }})"
                        xmlns="http://www.w3.org/2000/svg"
                        width="20" height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="animate-spin mr-2 text-current"
                    >
                        <path d="M12 2v4"/>
                        <path d="m16.2 7.8 2.9-2.9"/>
                        <path d="M18 12h4"/>
                        <path d="m16.2 16.2 2.9 2.9"/>
                        <path d="M12 18v4"/>
                        <path d="m4.9 19.1 2.9-2.9"/>
                        <path d="M2 12h4"/>
                        <path d="m4.9 4.9 2.9 2.9"/>
                    </svg>

                    <span wire:loading.remove wire:target="joinGame({{ $game->id }})">
                        Join To The Game
                    </span>
                    <span wire:loading wire:target="joinGame({{ $game->id }})">
                        Joining...
                    </span>
                </span>
                            </button>
                        </footer>
                    </div>
                @endforeach


            </section>


        </div>
    </div>


</div>
