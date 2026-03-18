@section('title', 'Clonections - Puzzle Archive')
@section('description', 'Browse and play past puzzles.')

<div class="max-w-4xl mx-auto p-4">
    <div class="text-center mb-6 relative w-full flex justify-center items-center min-h-[40px]">

        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold font-serif">Puzzle Archive</h1>
            <p class="text-gray-400 mt-2">Play past games</p>
        </div>

        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="absolute right-0">
            @csrf
            <button type="submit" class="cursor-pointer text-sm font-semibold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                Log Out
            </button>
        </form>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($puzzles as $puzzle)
            <a href="{{ route('game.play', $puzzle->id) }}" wire:navigate
               class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm hover:shadow-md hover:border-primary-500 dark:hover:border-primary-500 transition-all cursor-pointer group">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white group-hover:underline decoration-2 underline-offset-4 decoration-primary-500">
                    {{ $puzzle->title }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ $puzzle->play_date->format('M j, Y') }}
                </p>
                <div class="mt-4 text-sm font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1">
                    Play now
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $puzzles->links() }}
    </div>
</div>
