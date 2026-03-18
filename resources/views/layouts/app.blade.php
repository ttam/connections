<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connections Clone</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        const method = (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) ? 'add' : 'remove';
        document.documentElement.classList[method]('dark');
    </script>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-50 antialiased min-h-screen relative" x-data="{
      theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),

      init() {
          this.theme = this.theme.toLowerCase();
          this.setTheme(this.theme);
      },

      toggleTheme() {
          this.theme = this.theme === 'dark' ? 'light' : 'dark';
          localStorage.setItem('theme', this.theme);

          this.setTheme(this.theme);
      },

      setTheme(theme) {
          const method = theme === 'dark' ? 'add' : 'remove';
          document.documentElement.classList[method]('dark');
      }
}">

<button @click="toggleTheme()"
        class="absolute top-4 right-4 p-2 rounded-full border border-gray-200 dark:border-gray-800 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors z-50 bg-white dark:bg-gray-950 shadow-sm"
        aria-label="Toggle Dark Mode">

    <svg x-show="theme === 'dark'" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
    </svg>

    <svg x-show="theme === 'light'" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
    </svg>
</button>

{{ $slot }}

</body>
</html>
