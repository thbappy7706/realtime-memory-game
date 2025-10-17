<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen">
    <div class="bg-white antialiased dark:[background:radial-gradient(ellipse_70%_55%_at_50%_50%,rgba(255,20,147,0.15),transparent_50%),radial-gradient(ellipse_160%_130%_at_10%_10%,rgba(0,255,255,0.12),transparent_60%),radial-gradient(ellipse_160%_130%_at_90%_90%,rgba(138,43,226,0.18),transparent_65%),radial-gradient(ellipse_110%_50%_at_80%_30%,rgba(255,215,0,0.08),transparent_40%),#000000] flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>



