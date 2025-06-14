/**
 * VITE CONFIGURATION - MODERN BUILD TOOL SETUP FOR EDUCATIONAL APP
 * ================================================================
 * 
 * Vite is a fast and modern build tool that serves as the bridge between our
 * Laravel backend and Svelte frontend. It handles the process of compiling
 * our JavaScript and CSS assets for both development and production.
 * 
 * 🎓 BEGINNER LEARNING OBJECTIVES:
 * ================================
 * 1. **Build Tools**: Understanding the role of Vite in a modern web development workflow.
 * 2. **Hot Module Replacement (HMR)**: Experiencing instant UI updates during development.
 * 3. **Asset Bundling**: How JavaScript and CSS files are processed and optimized.
 * 4. **Plugin System**: Extending Vite's capabilities with specific framework integrations.
 * 5. **Development vs. Production**: Different configurations for different environments.
 * 
 * 🔍 WHAT YOU'LL LEARN:
 * =====================
 * - How to configure Vite for a Laravel and Svelte project.
 * - The purpose of the `laravel-vite-plugin` for backend integration.
 * - The role of `@sveltejs/vite-plugin-svelte` for Svelte 5 compilation.
 * - How `tailwindcss/vite` simplifies Tailwind CSS setup.
 * - Optimizing production builds with `rollupOptions` (code splitting).
 * - Setting up path aliases for cleaner imports.
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    /**
     * PLUGINS CONFIGURATION
     * ====================
     * 
     * Plugins are essential extensions that add specific functionalities to Vite.
     * In this setup, we configure three main plugins to integrate Laravel, Tailwind CSS,
     * and Svelte 5 seamlessly.
     * 
     * 🎓 LEARN: How plugins enhance a build tool's capabilities.
     */
    plugins: [
        /**
         * LARAVEL VITE PLUGIN
         * ==================
         * 
         * This plugin creates the crucial bridge between your Laravel backend and Vite frontend.
         * It tells Laravel where your frontend assets are located and handles asset refreshing.
         * 
         * 🔍 WHAT IT DOES:
         * - **Generates correct asset URLs**: Ensures Laravel correctly links to your Vite-processed assets.
         * - **Hot Module Replacement (HMR)**: Enables instant browser updates without full page reloads during development.
         * - **Manages asset manifest**: In production, it creates a manifest file for versioning and caching.
         *
         * `input`: An array specifying the main entry points (your primary JavaScript and CSS files).
         * `refresh`: Enables browser auto-reloading when certain files change (e.g., Blade templates).
         */
        laravel({
            input: [
                'resources/css/app.css', // Main CSS file (where Tailwind directives are imported)
                'resources/js/app.js'    // Main JavaScript file (where your Svelte app is bootstrapped)
            ],
            refresh: true, // Enables browser refresh on specific file changes (e.g., Blade files)
        }),

        /**
         * TAILWIND CSS V4 PLUGIN
         * ======================
         * 
         * This plugin integrates Tailwind CSS directly with Vite. Tailwind CSS v4 introduces a new architecture
         * that simplifies its setup with modern bundlers like Vite.
         * 
         * 🎓 LEARN: How utility-first CSS frameworks are integrated into a build pipeline.
         * 
         * EDUCATIONAL NOTE:
         * With Tailwind CSS v4, you typically no longer need a separate `postcss.config.js` file for basic setup.
         * It's integrated directly into Vite via this plugin, simplifying the build process and improving performance.
         */
        tailwindcss(),

        /**
         * SVELTE PLUGIN - SVELTE 5 SUPPORT
         * ================================
         * 
         * This is the official plugin that enables Vite to compile your Svelte components.
         * It's configured to support Svelte 5's new reactivity model (runes) and HMR.
         * 
         * 🎓 LEARN: How a frontend framework is compiled by a build tool.
         *
         * `compilerOptions`:
         * - `dev`: Enables development-specific features like better error messages and warnings.
         * - `hmr`: Activates Svelte 5's integrated Hot Module Replacement for instant component updates.
         * `emitCss`: In production, extracts compiled CSS into separate `.css` files for better caching.
         */
        svelte({
            compilerOptions: {
                dev: process.env.NODE_ENV === 'development', // Enable Svelte dev mode in development
                hmr: process.env.NODE_ENV === 'development', // Enable Svelte HMR in development
            },
            emitCss: process.env.NODE_ENV === 'production', // Extract CSS into separate files for production builds
        }),
    ],

    /**
     * DEVELOPMENT SERVER CONFIGURATION
     * ================================
     * 
     * These settings configure Vite's development server, which serves your frontend assets
     * and enables features like HMR during local development.
     * 
     * 🎓 LEARN: How a local development server speeds up your workflow.
     */
    server: {
        /**
         * Host Configuration
         * =================
         * 
         * Setting `host: true` makes the Vite dev server accessible from other devices
         * on your local network (e.g., for testing on a mobile phone).
         */
        host: true, // Allow access from network (e.g., mobile testing)

        /**
         * Hot Module Replacement (HMR)
         * =====================
         * 
         * HMR allows you to see changes in your code reflected in the browser instantly,
         * without a full page reload. This greatly speeds up development.
         * `host: 'localhost'` is often needed for HMR to work correctly in certain environments.
         */
        hmr: {
            host: 'localhost', // Specify HMR host to avoid connection issues
        },
    },

    /**
     * BUILD CONFIGURATION
     * ==================
     * 
     * These settings are applied when you run `npm run build` to create optimized,
     * production-ready bundles of your JavaScript and CSS assets.
     * 🎓 LEARN: How to optimize frontend assets for deployment.
     */
    build: {
        /**
         * Source Maps for Debugging
         * ========================
         * 
         * Source maps link your compiled code back to your original source code,
         * making debugging much easier in the browser's developer tools.
         * They are typically enabled in development but disabled in production for smaller file sizes.
         */
        sourcemap: process.env.NODE_ENV === 'development', // Generate source maps only in development

        /**
         * Rollup Options for Advanced Bundling
         * ===================================
         * 
         * Rollup is the underlying bundler used by Vite for production builds.
         * `rollupOptions` allows fine-tuning how your code is bundled and optimized.
         * 
         * `manualChunks`: A strategy for **code splitting** (or chunking).
         *   It breaks larger bundles into smaller, more manageable chunks.
         *   This improves caching and initial page load times.
         * 
         * 🎓 LEARN: How code splitting improves web performance.
         */
        rollupOptions: {
            output: {
                // Define separate chunks for vendor libraries like Svelte and Inertia.js
                manualChunks: {
                    svelte: ['svelte'], // Puts Svelte runtime into its own file (better caching)
                    inertia: ['@inertiajs/svelte'], // Puts Inertia.js client library into its own file
                },
            },
        },
    },

    /**
     * RESOLVE CONFIGURATION
     * ====================
     * 
     * This section configures how Vite resolves import paths in your JavaScript/Svelte code.
     * `alias` allows creating shortcuts for long or deeply nested import paths.
     * 🎓 LEARN: How path aliases simplify your import statements.
     */
    resolve: {
        /**
         * Path Aliases
         * ============
         * 
         * Aliases replace a long directory path with a shorter, more convenient name.
         * This makes your import statements cleaner and easier to manage.
         *
         * USAGE EXAMPLE:
         * Instead of `import MyComponent from '../../Components/MyComponent.svelte'`,
         * you can write `import MyComponent from '@components/MyComponent.svelte'`.
         */
        alias: {
            '@': '/resources/js',            // Alias '@' to the resources/js directory
            '@components': '/resources/js/Components', // Alias '@components' to the Components directory
            '@pages': '/resources/js/Pages',       // Alias '@pages' to the Pages directory
        },
    },
});

/*
 * 🎓 EDUCATIONAL SUMMARY - VITE CONFIGURATION
 * ===========================================
 * 
 * This `vite.config.js` file is crucial for setting up a modern, efficient,
 * and enjoyable development environment for your Laravel + Svelte application.
 * 
 * ✅ KEY LEARNINGS FROM THIS FILE:
 * - **Development Workflow**: How Vite handles hot reloading and asset serving.
 * - **Build Optimization**: Strategies for creating performant production bundles.
 * - **Framework Integration**: How plugins connect Laravel, Tailwind CSS, and Svelte.
 * - **Code Organization**: Using path aliases for cleaner import statements.
 * - **Performance Tuning**: Understanding concepts like code splitting and sourcemaps.
 * 
 * This configuration serves as a robust and well-documented foundation for any
 * modern web development project using Vite.
 */ 