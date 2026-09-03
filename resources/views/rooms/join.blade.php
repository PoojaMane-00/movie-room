@extends('layouts.app')

@section('title', 'Join {{ $room->name }} - CINEPHILE')

@section('content')
<!-- Main Content Canvas -->
<main class="flex-grow flex items-center justify-center pt-[100px] pb-12 px-margin-mobile md:px-margin-desktop">
    <!-- Glassmorphic Card -->
    <div class="glass-panel w-full max-w-xl rounded-xl p-8 md:p-12 shadow-2xl relative overflow-hidden">
        <!-- Decorative Glow inside card -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-64 bg-primary-container/10 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="relative z-10 flex flex-col gap-8">
            <!-- Header -->
            <div class="text-center space-y-2">
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Join {{ $room->name }}</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant">Enter your display name to join this watch party.</p>
            </div>
            <!-- Form -->
            <form class="flex flex-col gap-6" method="POST" action="/rooms/{{ $room->uuid }}/join">
                @csrf

                <div class="flex flex-col gap-2">
                    <label class="font-label-md text-label-md text-on-surface" for="display_name">Display Name</label>
                    <input
                        class="w-full bg-surface-container/50 border-0 border-b border-white/20 text-on-surface font-body-md text-body-md px-4 py-3 rounded-t-lg transition-all duration-300 input-glow placeholder:text-on-surface-variant/50 focus:ring-0"
                        id="display_name"
                        name="display_name"
                        placeholder="e.g., John Doe"
                        required
                        type="text"
                        maxlength="50"
                        value="{{ old('display_name') }}" />

                    @error('display_name')
                    <div class="text-error font-body-md text-body-md">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button class="mt-4 w-full bg-primary-container text-on-primary-container font-label-md text-label-md py-4 rounded-lg shadow-[0_0_15px_rgba(229,9,20,0.4)] hover:shadow-[0_0_25px_rgba(229,9,20,0.6)] hover:bg-[#ff1a25] transition-all duration-300 active:scale-[0.98] flex justify-center items-center gap-2" type="submit">
                    <span class="material-symbols-outlined">login</span>
                    Join Room
                </button>
            </form>
        </div>
    </div>
</main>
@endsection