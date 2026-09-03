<!DOCTYPE html>

<html class="dark" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>@yield('title', 'CINEPHILE')</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    
    <!-- Tailwind Config -->
    <script id="tailwind-config">
        if (window.tailwind) {
            tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-surface-variant": "#e9bcb6",
                        "on-secondary-container": "#725f00",
                        "on-secondary": "#3a3000",
                        "surface-tint": "#ffb4aa",
                        "surface-dim": "#131313",
                        "surface-container-low": "#1c1b1b",
                        "on-secondary-fixed": "#221b00",
                        "on-error": "#690005",
                        "primary": "#ffb4aa",
                        "on-primary-fixed": "#410001",
                        "on-primary-fixed-variant": "#930007",
                        "surface-container-high": "#2a2a2a",
                        "secondary-container": "#ffdb3c",
                        "primary-fixed-dim": "#ffb4aa",
                        "outline": "#af8782",
                        "inverse-primary": "#c0000c",
                        "error": "#ffb4ab",
                        "on-tertiary-fixed": "#1b1b1c",
                        "primary-fixed": "#ffdad5",
                        "on-tertiary-fixed-variant": "#474746",
                        "on-primary-container": "#fff7f6",
                        "secondary-fixed": "#ffe16d",
                        "primary-container": "#e50914",
                        "surface": "#131313",
                        "on-tertiary": "#303030",
                        "inverse-on-surface": "#313030",
                        "tertiary": "#c8c6c5",
                        "surface-container": "#201f1f",
                        "on-surface": "#e5e2e1",
                        "secondary": "#fff9ef",
                        "outline-variant": "#5e3f3b",
                        "tertiary-fixed": "#e5e2e1",
                        "surface-container-highest": "#353534",
                        "tertiary-container": "#737272",
                        "inverse-surface": "#e5e2e1",
                        "on-background": "#e5e2e1",
                        "tertiary-fixed-dim": "#c8c6c5",
                        "on-error-container": "#ffdad6",
                        "error-container": "#93000a",
                        "surface-bright": "#3a3939",
                        "on-tertiary-container": "#fbf8f8",
                        "secondary-fixed-dim": "#e9c400",
                        "on-secondary-fixed-variant": "#544600",
                        "surface-container-lowest": "#0e0e0e",
                        "background": "#131313",
                        "surface-variant": "#353534",
                        "on-primary": "#690003"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "base": "8px",
                        "margin-mobile": "16px",
                        "margin-desktop": "48px",
                        "container-max": "1440px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-lg": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "label-sm": ["Inter"],
                        "display-lg": ["Montserrat"]
                    },
                    "fontSize": {
                        "body-lg": ["18px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }],
                        "label-md": ["14px", {
                            "lineHeight": "1.4",
                            "letterSpacing": "0.05em",
                            "fontWeight": "600"
                        }],
                        "body-md": ["16px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }],
                        "headline-lg": ["32px", {
                            "lineHeight": "1.2",
                            "fontWeight": "700"
                        }],
                        "display-lg-mobile": ["40px", {
                            "lineHeight": "1.2",
                            "fontWeight": "700"
                        }],
                        "headline-md": ["24px", {
                            "lineHeight": "1.3",
                            "fontWeight": "600"
                        }],
                        "label-sm": ["12px", {
                            "lineHeight": "1.4",
                            "fontWeight": "500"
                        }],
                        "display-lg": ["64px", {
                            "lineHeight": "1.1",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }]
                    }
                }
            }
            }
        }
    </script>

    <!-- Common Styles -->
    <style>
        body {
            background-color: #131313;
            background-image: radial-gradient(circle at 50% 50%, rgba(229, 9, 20, 0.05) 0%, transparent 60%);
        }

        .glass-panel {
            background-color: rgba(31, 31, 31, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-glow:focus {
            box-shadow: 0 0 10px rgba(229, 9, 20, 0.2) inset, 0 2px 0 0 #e50914;
            border-color: transparent;
        }

        .glow-button {
            transition: all 0.3s ease;
        }

        .glow-button:hover {
            box-shadow: 0 0 15px theme('colors.primary-container');
        }

        .video-gradient {
            background: linear-gradient(to top, #0A0A0A 0%, transparent 40%);
        }

        /* Custom scrollbar for chat */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
</head>

<body class="bg-background text-on-background min-h-screen flex flex-col font-body-md overflow-x-hidden">
    @include('components.navbar')

    @yield('content')
</body>

</html>
