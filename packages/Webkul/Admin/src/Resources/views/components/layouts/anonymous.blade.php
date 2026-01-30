<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>

<head>
    <title>{{ $title ?? '' }}</title>

    <meta charset="UTF-8">

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        http-equiv="content-language"
        content="{{ app()->getLocale() }}"
    >

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="base-url"
        content="{{ url()->to('/') }}"
    >
    <meta 
        name="generator" 
        content="{{ core()->getConfigData('whitelabel.branding.general.meta_generator') ?: config('app.name') }}"
    >

    @stack('meta')

    @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap"
        rel="stylesheet"
    />

    @if ($favicon = core()->getConfigData('general.design.admin_logo.favicon'))
        <link
            type="image/x-icon"
            href="{{ Storage::url($favicon) }}"
            rel="shortcut icon"
            sizes="16x16"
        />
    @endif

    @stack('styles')

    <style>
        {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
    </style>

    {!! view_render_event('bagisto.admin.layout.head') !!}
</head>

<body>
    {!! view_render_event('bagisto.admin.layout.body.before') !!}

    <div id="app">
        <!-- Flash Message Blade Component -->
        <x-admin::flash-group />

        {!! view_render_event('bagisto.admin.layout.content.before') !!}

        <!-- Page Content Blade Component -->
        {{ $slot }}

        {!! view_render_event('bagisto.admin.layout.content.after') !!}
    </div>

    {!! view_render_event('bagisto.admin.layout.body.after') !!}

    @stack('scripts')

    {!! view_render_event('bagisto.admin.layout.vue-app-mount.before') !!}

    <script>
        /**
         * Load event, the purpose of using the event is to mount the application
         * after all of our `Vue` components which is present in blade file have
         * been registered in the app. No matter what `app.mount()` should be
         * called in the last.
         */
        function mountVueApp() {
            if (typeof window.app !== 'undefined' && window.app) {
                try {
                    const appElement = document.getElementById('app');
                    if (appElement) {
                        if (appElement.__vue_app__) {
                            console.warn('Vue app already mounted');
                            return;
                        }
                        app.mount("#app");
                        console.log('Vue app mounted successfully');
                    } else {
                        console.error('App element (#app) not found');
                    }
                } catch (error) {
                    console.error('Vue app mount error:', error);
                }
            } else {
                console.error('Vue app is not defined. JavaScript may not have loaded correctly.');
                // Retry after a short delay
                setTimeout(function() {
                    if (typeof window.app !== 'undefined' && window.app) {
                        mountVueApp();
                    } else {
                        console.error('Vue app still not available after retry. Please check browser console for JavaScript errors.');
                    }
                }, 500);
            }
        }

        // Debug: Check if JavaScript is loading
        console.log('Admin layout script loaded. DOM readyState:', document.readyState);
        console.log('Checking for window.app...', typeof window.app);

        // Try to mount immediately if DOM is ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(mountVueApp, 100);
        } else {
            window.addEventListener("load", mountVueApp);
        }

        // Fallback: try mounting after DOMContentLoaded
        document.addEventListener("DOMContentLoaded", function() {
            console.log('DOMContentLoaded fired');
            setTimeout(mountVueApp, 200);
        });
    </script>

    {!! view_render_event('bagisto.admin.layout.vue-app-mount.after') !!}

    <script type="text/javascript">
        {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
    </script>
</body>

</html>
