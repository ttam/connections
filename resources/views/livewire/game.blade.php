<div class="max-w-2xl mx-auto p-4 select-none"
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

    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold font-serif">Connections</h1>
        <p class="text-gray-600 mt-1">Create four groups of four!</p>
    </div>

    <div class="mb-8" :class="{ 'animate-shake': shake }">

        <div class="flex flex-col gap-2 mb-2">
            @foreach($solvedCategoryIds as $categoryId)
                @php
                    // Retrieve the category and its words from the pre-loaded puzzle data
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

    <div class="flex items-center justify-center gap-2 mb-6 h-4">
        <span class="text-gray-600 text-sm">Mistakes remaining:</span>
        <div class="flex gap-2">
            @for($i = 0; $i < $mistakesRemaining; $i++)
                <div class="w-3 h-3 rounded-full bg-gray-600"></div>
            @endfor
        </div>
    </div>

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

    @if($gameStatus === 'won')
        <div class="text-center mt-8 text-2xl font-bold text-green-600 animate-bounce">Perfect!</div>
    @elseif($gameStatus === 'lost')
        <div class="text-center mt-8 text-2xl font-bold text-red-600">Next time!</div>
    @endif
</div>
