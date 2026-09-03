@extends('layouts.app')

@section('title', 'Session Ended - CINEPHILE')

@section('content')
<!-- Main Content Canvas -->
<main class="flex-grow flex items-center justify-center pt-[100px] pb-12 px-margin-mobile md:px-margin-desktop">
    <!-- Glassmorphic Card -->
    <div class="glass-panel w-full max-w-xl rounded-xl p-8 md:p-12 shadow-2xl relative overflow-hidden">
        <!-- Decorative Glow inside card -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-primary-container/10 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="relative z-10 flex flex-col gap-8 items-center">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-full bg-primary-container/20 flex items-center justify-center border border-primary-container/50">
                <span class="material-symbols-outlined text-primary-container text-4xl">check_circle</span>
            </div>

            <!-- Header -->
            <div class="text-center space-y-2">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Session Ended</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant">The host has ended the movie session.</p>
            </div>

            <!-- Message -->
            <div class="w-full bg-surface-container/50 rounded-lg p-4 border border-white/10">
                <p class="font-body-md text-body-md text-on-surface text-center">
                    Thank you for joining! We hope you enjoyed the watch party. Feel free to create a new room or join another session anytime.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 w-full">
                <a href="/rooms/create" class="flex-1 bg-primary-container text-on-primary-container font-label-md text-label-md py-3 rounded-lg shadow-[0_0_15px_rgba(229,9,20,0.4)] hover:shadow-[0_0_25px_rgba(229,9,20,0.6)] hover:bg-[#ff1a25] transition-all duration-300 active:scale-[0.98] flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined">home</span>
                    Create Room
                </a>
                <!-- <a href="/rooms/join" class="flex-1 bg-surface-container/50 text-on-surface font-label-md text-label-md py-3 rounded-lg shadow-[0_0_15px_rgba(229,9,20,0.2)] hover:shadow-[0_0_20px_rgba(229,9,20,0.3)] hover:bg-surface-container transition-all duration-300 active:scale-[0.98] flex justify-center items-center gap-2 border border-white/10">
                    <span class="material-symbols-outlined">add</span>
                    Join Room
                </a> -->
            </div>
        </div>
    </div>
</main>

@endsection