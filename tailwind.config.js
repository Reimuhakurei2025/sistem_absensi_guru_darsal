/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#2E7D32',
                    50:  '#E8F5E9',
                    100: '#C8E6C9',
                    200: '#A5D6A7',
                    300: '#81C784',
                    400: '#66BB6A',
                    500: '#4CAF50',
                    600: '#43A047',
                    700: '#2E7D32',
                    800: '#1B5E20',
                    900: '#0D3F12',
                },
                secondary: {
                    DEFAULT: '#66BB6A',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                'soft': '0 2px 8px rgba(46, 125, 50, 0.08)',
                'card': '0 4px 12px rgba(0, 0, 0, 0.05)',
            },
        },
    },
    plugins: [],
};
