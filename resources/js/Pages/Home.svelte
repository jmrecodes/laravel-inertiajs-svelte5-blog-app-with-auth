<!--
  🎓 EDUCATIONAL HOME PAGE - STREAMLINED DESIGN
  ============================================

  This is a modern, clean landing page demonstrating:
  • Svelte 5 runes syntax ($props, $state, $derived, $effect)
  • Modern responsive design with Tailwind CSS
  • Beautiful typography and visual hierarchy
  • Accessible, semantic HTML structure
  • Educational code patterns and best practices
-->

<script>
    /**
     * SVELTE 5 IMPORTS AND DEPENDENCIES
     * =================================
     *
     * Import necessary Svelte 5 features and external dependencies.
     * Svelte 5 introduces runes ($props, $state, $effect) for better
     * reactivity and simpler mental models.
     */

    import { router } from "@inertiajs/svelte"; // Inertia.js router for client-side navigation
    import { Link } from "@inertiajs/svelte"; // Inertia.js Link component for SPA-like navigation

    // Utility functions (we'll create these)
    import { formatDate } from "../Utils/helpers.js";

    // Footer component for consistent site-wide footer
    import Footer from "/resources/js/Components/Footer.svelte";

    /**
     * COMPONENT PROPS - SVELTE 5 RUNES SYNTAX
     * =======================================
     *
     * Using the new $props() rune for receiving data from Laravel via Inertia.js.
     * This provides better reactivity and type safety compared to export let.
     */

    // Props from Laravel controller via Inertia.js
    let {
        welcome = {
            title: "Learn Modern Web Development",
            subtitle: "Master Svelte 5, Inertia.js & Laravel",
            description:
                "A comprehensive educational platform with hands-on tutorials, real-world examples, and modern development practices.",
        },
        features = [
            "Reactive UI with Svelte 5",
            "Seamless Navigation with Inertia.js",
            "CRUD Operations with Eloquent ORM",
            "Modern Styling with Tailwind CSS",
            "Authentication with Laravel Sanctum",
            "Comprehensive Educational Comments",
        ],
        recentPosts = [],
        auth = {}, // auth object from Laravel
    } = $props();

    /**
     * COMPONENT STATE - SVELTE 5 $STATE RUNE
     * ======================================
     *
     * Using $state() for reactive local component state.
     * These values can change during the component's lifecycle.
     */
    let isLoading = $state(false);
    let showFeatures = $state(true);

    /**
     * REACTIVE COMPUTATIONS - SVELTE 5 $DERIVED RUNE
     * ==============================================
     *
     * Using $derived() for computed values that automatically update
     * when their dependencies change.
     */
    let recentPostsCount = $derived(recentPosts.length);

    /**
     * SIDE EFFECTS - SVELTE 5 $EFFECT RUNE
     * ====================================
     *
     * Using $effect() for side effects that run when dependencies change.
     * This replaces reactive statements ($:) from Svelte 4.
     */
    $effect(() => {
        // Update page title dynamically
        if (typeof window !== "undefined" && welcome.title) {
            document.title = `${welcome.title} | jmrecodes Educational Blog`;
        }
    });

    /**
     * EVENT HANDLERS
     * =============
     *
     * Functions that handle user interactions like clicks, form submissions, etc.
     * These demonstrate proper event handling patterns in Svelte.
     */

    /**
     * Handle CTA button clicks with loading state
     */
    function handleGetStarted() {
        isLoading = true;

        // Simulate brief loading (in real app, this might be navigation)
        setTimeout(() => {
            // Determine the destination based on production status and auth state.
            // This simplified logic helps the bundler correctly perform tree-shaking.
            const destination = !auth.user ? "/posts" : "/dashboard";

            router.visit(destination);
        }, 500);
    }

    /**
     * Handle newsletter subscription (example)
     */
    function handleNewsletterSignup(email) {
        console.log("📧 Newsletter signup:", email);
        // In a real app, you'd send this to your backend
    }
</script>

<!--
  DYNAMIC HEAD CONTENT
  ===================

  In Svelte, we use <svelte:head> instead of a Head component.
  This is Svelte's built-in way to set page-specific meta tags,
  title, and other head elements. This is crucial for SEO.

  EDUCATIONAL NOTE:
  Unlike React/Vue versions of Inertia.js, Svelte doesn't have a Head component.
  Instead, we use Svelte's native <svelte:head> which works seamlessly with Inertia.js.

  These tags will be injected into the <head> section of our
  main Blade template dynamically.
-->
<svelte:head>
    <title>{welcome.title}</title>
    <meta name="description" content={welcome.description} />
    <meta
        name="keywords"
        content="Svelte 5, Inertia.js, Laravel, Web Development, Tutorial"
    />

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content={welcome.title} />
    <meta property="og:description" content={welcome.description} />
    <meta property="og:type" content="website" />
</svelte:head>

<!--
  MAIN COMPONENT TEMPLATE
  =======================

  The main component markup using modern Svelte 5 syntax.
  This demonstrates component structure, conditional rendering,
  and integration with Tailwind CSS for styling.
-->

<!-- Loading Overlay (conditional rendering) -->
{#if isLoading}
    <div
        class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-sm w-full mx-4">
            <div class="text-center">
                <div
                    class="w-8 h-8 border-3 border-accent-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"
                ></div>
                <p class="text-gray-600 font-medium">Taking you there...</p>
            </div>
        </div>
    </div>
{/if}

<!-- Main Page Content -->
<div
    class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-100"
>
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <!-- Background decoration -->
        <div
            class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-purple-600/5"
        ></div>
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-96 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl"
        ></div>

        <div
            class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24"
        >
            <div class="text-center max-w-4xl mx-auto">
                <!-- 🏷️ BADGE -->
                <div
                    class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium mb-8"
                >
                    <span
                        class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"
                    ></span>
                    Now with Svelte 5 & Tailwind v4
                </div>

                <!-- 📝 MAIN TITLE -->
                <h1
                    class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 bg-clip-text text-transparent mb-6 leading-tight"
                >
                    {welcome.title}
                </h1>

                <!-- 📄 SUBTITLE -->
                <p class="text-xl md:text-2xl text-gray-600 mb-6 font-medium">
                    {welcome.subtitle}
                </p>

                <!-- 📝 DESCRIPTION -->
                <p
                    class="text-lg text-gray-700 mb-12 max-w-2xl mx-auto leading-relaxed"
                >
                    {welcome.description}
                </p>

                <!-- 🎯 CTA BUTTONS -->
                <div
                    class="flex flex-col sm:flex-row gap-4 justify-center items-center"
                >
                    <button
                        class="group relative overflow-hidden bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:transform-none w-full sm:w-auto"
                        onclick={handleGetStarted}
                        disabled={isLoading}
                    >
                        <span
                            class="relative z-10 flex items-center justify-center"
                        >
                            {#if isLoading}
                                <div
                                    class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"
                                ></div>
                                Loading...
                            {:else}
                                {!auth.user
                                    ? "🚀 Explore Posts"
                                    : "🎯 Get Started"}
                            {/if}
                        </span>
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                        ></div>
                    </button>
                    <!-- Register link is only shown in development mode -->
                    {#if import.meta.env.DEV || import.meta.env.MODE === "development"}
                        <Link
                            href="/register"
                            class="group px-8 py-4 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold text-lg hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 w-full sm:w-auto text-center"
                        >
                            📚 Learn More
                        </Link>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    {#if showFeatures && features.length > 0}
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        What You'll Master
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Build modern web applications with industry-standard
                        tools and practices
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {#each features as feature, index}
                        <div
                            class="group relative bg-gradient-to-br from-white to-gray-50 rounded-2xl p-8 shadow-lg hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 border border-gray-100"
                        >
                            <!-- Feature Icon -->
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-200"
                            >
                                <span class="text-2xl">
                                    {#if feature.includes("Svelte")}
                                        🎯
                                    {:else if feature.includes("Inertia")}
                                        🚀
                                    {:else if feature.includes("CRUD")}
                                        📊
                                    {:else if feature.includes("Tailwind")}
                                        🎨
                                    {:else if feature.includes("Authentication")}
                                        🔐
                                    {:else}
                                        ✨
                                    {/if}
                                </span>
                            </div>

                            <!-- Feature Text -->
                            <h3
                                class="text-xl font-semibold text-gray-900 mb-3"
                            >
                                {feature}
                            </h3>
                            <p class="text-gray-600 leading-relaxed">
                                Hands-on experience with real-world applications
                                and best practices.
                            </p>

                            <!-- Hover accent -->
                            <div
                                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            ></div>
                        </div>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <!-- Recent Posts Section -->
    {#if recentPostsCount > 0}
        <section class="py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Latest Tutorials
                    </h2>
                    <p class="text-xl text-gray-600">
                        Fresh educational content and hands-on guides
                    </p>
                </div>

                <!-- Posts Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    {#each recentPosts.slice(0, 6) as post}
                        <article
                            class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden"
                        >
                            <!-- Post Image -->
                            {#if post.featured_image}
                                <div class="aspect-video overflow-hidden">
                                    <img
                                        src={post.featured_image}
                                        alt={post.title}
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    />
                                </div>
                            {:else}
                                <div
                                    class="aspect-video bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center"
                                >
                                    <span class="text-4xl">📝</span>
                                </div>
                            {/if}

                            <div class="p-6">
                                <!-- Post Title -->
                                <h3
                                    class="text-xl font-semibold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors duration-200"
                                >
                                    <Link href="/posts/{post.slug}">
                                        {post.title}
                                    </Link>
                                </h3>

                                <!-- Post Excerpt -->
                                {#if post.excerpt}
                                    <p
                                        class="text-gray-600 mb-4 leading-relaxed"
                                    >
                                        {post.excerpt}
                                    </p>
                                {/if}

                                <!-- Post Meta -->
                                <div
                                    class="flex items-center justify-between text-sm text-gray-500"
                                >
                                    <span class="font-medium"
                                        >{post.user?.name || "Anonymous"}</span
                                    >
                                    <span>{formatDate(post.published_at)}</span>
                                </div>
                            </div>
                        </article>
                    {/each}
                </div>

                <!-- View All Link -->
                <div class="text-center mt-12">
                    <Link
                        href="/posts"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all duration-200"
                    >
                        View All Posts ({recentPostsCount}) →
                    </Link>
                </div>
            </div>
        </section>
    {/if}

    <!-- User Status Section (conditional) -->
    {#if auth.user}
        <div class="bg-green-50 border-l-4 border-green-400 p-6 mx-4 my-8">
            <div class="flex items-center">
                <span class="text-2xl mr-3">👋</span>
                <div>
                    <p class="text-green-800 font-medium">
                        Welcome back, <strong>{auth.user.name}</strong>!
                        <Link
                            href="/dashboard"
                            class="underline hover:text-green-900 transition-colors"
                        >
                            Go to your dashboard →
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    {:else}
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6">
            <div class="flex items-center">
                <span class="text-2xl mr-3">🚀</span>
                <div>
                    <p class="text-blue-800">
                        <Link
                            href="/login"
                            class="underline hover:text-blue-900 transition-colors font-medium"
                        >
                            Sign in
                        </Link> or
                        <Link
                            href="/register"
                            class="underline hover:text-blue-900 transition-colors font-medium"
                        >
                            create an account
                        </Link> to start your learning journey!
                    </p>
                </div>
            </div>
        </div>
    {/if}
</div>

<!--
  SITE FOOTER - COMPREHENSIVE SITE NAVIGATION AND LEGAL COMPLIANCE
  ================================================================

  🎓 LEARNING CONCEPT: Site-wide Footer Implementation
  ==================================================

  The footer component provides:
  - Secondary navigation for all site sections
  - Legal compliance links (Terms, Privacy Policy)
  - Contact information and social links
  - Copyright notice with proper attribution
  - Professional appearance and user trust

  Educational Benefits:
  - Demonstrates component reuse across pages
  - Shows how to implement legal compliance
  - Provides consistent site navigation
  - Improves SEO through internal linking

  Beginner Note: Footers are essential for professional web applications!
-->
<Footer {auth} />

<!-- 🎨 CUSTOM STYLES -->
<style>
    /* Enhanced animations for reduced motion preferences */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
