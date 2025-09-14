import preset from './vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                'arabic': ['Segoe UI', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
