@extends('layouts.app')

@section('title', 'Create Movie Room - CINEPHILE')

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
                    <h1 class="font-headline-lg text-headline-lg text-on-surface">Create Your Cinema Room</h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant">Set the stage for your next watch party.</p>
                </div>
                <!-- Form -->
                <form class="flex flex-col gap-6" method="POST" action="/rooms">
                    @csrf

                    <div class="flex flex-col gap-2">
                        <label class="font-label-md text-label-md text-on-surface" for="name">Room Name</label>
                        <input
                            class="w-full bg-surface-container/50 border-0 border-b border-white/20 text-on-surface font-body-md text-body-md px-4 py-3 rounded-t-lg transition-all duration-300 input-glow placeholder:text-on-surface-variant/50 focus:ring-0"
                            id="name"
                            name="name"
                            placeholder="e.g., Friday Night Flicks"
                            required
                            type="text"
                            value="{{ old('name') }}" />

                        @error('name')
                        <div class="text-error font-body-md text-body-md">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-label-md text-label-md text-on-surface" for="video_url">Video URL</label>
                        <input
                            class="w-full bg-surface-container/50 border-0 border-b border-white/20 text-on-surface font-body-md text-body-md px-4 py-3 rounded-t-lg transition-all duration-300 input-glow placeholder:text-on-surface-variant/50 focus:ring-0"
                            id="video_url"
                            name="video_url"
                            placeholder="Paste a YouTube, Netflix, or Prime link"
                            required
                            type="url"
                            value="{{ old('video_url') }}" />

                        @error('video_url')
                        <div class="text-error font-body-md text-body-md">{{ $message }}</div>
                        @enderror
                    </div>


                    <!-- Submit Button -->
                    <button class="mt-4 w-full bg-primary-container text-on-primary-container font-label-md text-label-md py-4 rounded-lg shadow-[0_0_15px_rgba(229,9,20,0.4)] hover:shadow-[0_0_25px_rgba(229,9,20,0.6)] hover:bg-[#ff1a25] transition-all duration-300 active:scale-[0.98] flex justify-center items-center gap-2" type="submit">
                        <span class="material-symbols-outlined">movie</span>
                        Create Room
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection