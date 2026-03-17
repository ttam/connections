<div class="{{ $puzzle->show_live_results ? 'max-w-5xl md:grid md:grid-cols-2 md:gap-12 md:items-start' : 'max-w-2xl' }} mx-auto p-4 select-none relative"
     x-data="{
        shake: false,
        toast: '',
        showToast(message) {
            this.toast = message;
            setTimeout(() => this.toast = '', 3000);
        }
     }"
     @toast.window="showToast($event.detail.message)"
     @shake-tiles.window="shake = true; setTimeout(() => shake = false, 500)">

    <div x-show="toast" x-transition.opacity x-text="toast" style="display: none;"
         class="fixed top-10 left-1/2 transform -translate-x-1/2 bg-black text-white px-4 py-2 rounded shadow-lg z-50">
    </div>

    <div class="w-full max-w-2xl mx-auto flex flex-col items-center">

        <div class="text-center mb-6 relative w-full flex justify-center items-center min-h-[40px]">

            <a href="{{ route('archive') }}" wire:navigate class="absolute left-0 text-sm font-semibold text-gray-500 hover:text-black flex items-center gap-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Archive
            </a>

            <div>
                <h1 class="text-3xl font-bold font-serif">Connections</h1>
                <p class="text-gray-600 mt-1 font-medium">
                    {{ $puzzle->title }}
                    @if($puzzle->user)
                        <span class="text-gray-400 font-normal">by {{ $puzzle->user->name }}</span>
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="absolute right-0">
                @csrf
                <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-black transition-colors">
                    Log Out
                </button>
            </form>

        </div>

        <div class="mb-8 w-full" :class="{ 'animate-shake': shake }">

            <div class="flex flex-col gap-2 mb-2">
                @foreach($solvedCategoryIds as $categoryId)
                    @php
                        $category = $puzzle->categories->firstWhere('id', $categoryId);
                        $categoryWords = $category->words->pluck('text')->join(', ');
                    @endphp

                    <div class="w-full rounded-lg flex flex-col items-center justify-center text-center p-4 animate-fade-in
                        {{ match((int) $category->difficulty_level) {
                            1 => 'bg-yellow-300',
                            2 => 'bg-green-300',
                            3 => 'bg-blue-300',
                            4 => 'bg-purple-300',
                            default => 'bg-gray-300',
                        } }}">
                        <span class="font-bold uppercase text-lg tracking-widest">{{ $category->title }}</span>
                        <span class="text-sm uppercase tracking-wide mt-1">{{ $categoryWords }}</span>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-4 gap-2">
                @foreach($boardWords->whereNotIn('category_id', $solvedCategoryIds) as $word)
                    @php
                        $isSelected = in_array($word->id, $selectedWordIds);
                    @endphp

                    <button wire:click="toggleSelection('{{ $word->id }}')"
                            @disabled($gameStatus !== 'playing')
                            class="col-span-1 aspect-[4/3] rounded-lg flex items-center justify-center font-bold text-center p-2 uppercase transition-all duration-150 active:scale-95
                            {{ $isSelected ? 'bg-gray-600 text-white' : 'bg-gray-200 text-black hover:bg-gray-300' }}">
                        {{ $word->text }}
                    </button>
                @endforeach
            </div>

        </div>

        @if($puzzle->max_mistakes !== null)
            <div class="flex items-center justify-center gap-2 mb-6 h-4">
                <span class="text-gray-600 text-sm">Mistakes remaining:</span>
                <div class="flex gap-2">
                    @for($i = 0; $i < $mistakesRemaining; $i++)
                        <div class="w-3 h-3 rounded-full bg-gray-600"></div>
                    @endfor
                </div>
            </div>
        @endif

        <div class="flex justify-center gap-3">
            <button wire:click="shuffle" class="px-5 py-3 rounded-full border border-black font-semibold hover:bg-gray-100 transition active:scale-95">
                Shuffle
            </button>
            <button wire:click="deselectAll" class="px-5 py-3 rounded-full border border-black font-semibold hover:bg-gray-100 transition active:scale-95 disabled:opacity-50 disabled:border-gray-300 disabled:text-gray-400 disabled:hover:bg-transparent"
                @disabled(empty($selectedWordIds))>
                Deselect All
            </button>
            <button wire:click="submit"
                    class="px-5 py-3 rounded-full border font-semibold transition active:scale-95
                           {{ count($selectedWordIds) === 4 ? 'border-black bg-black text-white hover:bg-gray-800' : 'border-gray-300 text-gray-400 cursor-not-allowed' }}"
                @disabled(count($selectedWordIds) !== 4 || $gameStatus !== 'playing')>
                Submit
            </button>
        </div>

        @if($gameStatus !== 'playing')
            <div class="mt-8 flex flex-col items-center gap-4">
                <div class="text-2xl font-bold {{ $gameStatus === 'won' ? 'text-green-600 animate-bounce' : 'text-red-600' }}">
                    {{ $gameStatus === 'won' ? 'Perfect!' : 'Next time!' }}
                </div>

                <button x-data="{ shareString: @js($this->getShareText()) }"
                        @click="navigator.clipboard.writeText(shareString).then(() => { $dispatch('toast', { message: 'Copied to clipboard!' }) })"
                        class="px-8 py-3 rounded-full bg-black text-white font-bold hover:bg-gray-800 transition active:scale-95 flex items-center gap-2">
                    Share Result
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                    </svg>
                </button>
            </div>
        @endif

    </div>

    @if($puzzle->show_live_results)
        <div class="mt-12 md:mt-0 md:sticky md:top-10 flex flex-col items-center pt-8 border-t md:border-t-0 md:border-l md:pt-0 border-gray-200 md:min-h-[400px]">

            @if(count($guesses) > 0)
                <h3 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Your Guesses</h3>

                <div class="flex flex-col gap-1">
                    @foreach($guesses as $guessRow)
                        <div class="flex gap-1 animate-fade-in">
                            @foreach($guessRow as $difficultyLevel)
                                <div class="w-8 h-8 rounded-sm
                                    {{ match((int) $difficultyLevel) {
                                        1 => 'bg-yellow-300',
                                        2 => 'bg-green-300',
                                        3 => 'bg-blue-300',
                                        4 => 'bg-purple-300',
                                        default => 'bg-gray-300',
                                    } }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 mb-4">Awaiting Guesses</h3>
            @endif

        </div>
    @endif

</div>
