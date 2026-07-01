{{-- Apply saved theme before first paint to prevent flash of wrong color scheme --}}
<script>
    (function () {
        var storageKey = 'tyro-dashboard-theme';
        var stored = localStorage.getItem(storageKey);
        var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        var root = document.documentElement;

        root.classList.remove('light', 'dark');
        root.classList.add(theme);
    })();
</script>
<style>
    html {
        color-scheme: light dark;
        background-color: #f4f4f5;
    }

    html.dark {
        background-color: #09090b;
    }
</style>
