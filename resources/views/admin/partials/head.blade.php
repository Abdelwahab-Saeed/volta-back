{{--
    Shared <head> assets for every admin page (dashboard layout and the auth screens).

    The admin keeps using the Tailwind Play CDN (no build step on deploy). This file is the design
    system: brand colours, the Cairo font (same as the storefront) and the component classes every
    page uses (.card, .btn-*, .input, .data-table, .badge-*...). Change the look here, not per page.
--}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Volta brand: navy from the logo wordmark, blue from the "V". `blue` is mapped to the brand blue
    // so any leftover blue-* utility stays on brand.
    const brand = {
        50: '#eef8fc', 100: '#d7eff9', 200: '#b3e0f2', 300: '#7ecbe8', 400: '#45b0da',
        500: '#2197cb', 600: '#0a7fb3', 700: '#0b6892', 800: '#0f5677', 900: '#124863', 950: '#0b2e42',
    };
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Cairo', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    navy: {
                        50: '#f3f5fa', 100: '#e4e8f2', 200: '#c9d0e4', 300: '#a1adcd', 400: '#7283ae',
                        500: '#526393', 600: '#3f4d78', 700: '#333f63', 800: '#283253', 900: '#1e2749', 950: '#141a33',
                    },
                    brand,
                    blue: brand,
                    teal: { 400: '#5cc2bf', 500: '#3fb0ad' },
                },
                boxShadow: {
                    card: '0 1px 2px rgba(16, 24, 40, 0.04), 0 1px 3px rgba(16, 24, 40, 0.04)',
                    pop: '0 12px 32px -12px rgba(20, 26, 51, 0.28)',
                },
            },
        },
    };
</script>
<style type="text/tailwindcss">
    @layer base {
        html { -webkit-tap-highlight-color: transparent; }
        body { @apply font-sans text-slate-800 antialiased; }
        ::selection { @apply bg-brand-100 text-navy-900; }
        [x-cloak] { display: none !important; }
    }

    @layer components {
        /* ---- Surfaces ---- */
        /* min-w-0: a wide table inside must scroll within the card, not stretch the page (grid/flex items default to min-width: auto) */
        .card { @apply bg-white rounded-2xl border border-slate-200/80 shadow-card min-w-0; }
        .card-header { @apply flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-slate-100; }
        .card-title { @apply text-base font-extrabold text-navy-900; }
        .card-subtitle { @apply text-xs text-slate-500 mt-0.5; }
        .card-body { @apply p-5; }
        .card-footer { @apply px-5 py-4 border-t border-slate-100 bg-slate-50/60 rounded-b-2xl; }
        .section-title { @apply text-sm font-extrabold text-navy-900 flex items-center gap-2; }

        /* ---- Buttons ---- */
        .btn { @apply inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-sm font-bold whitespace-nowrap transition duration-150 select-none cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none; }
        .btn-primary { @apply btn bg-brand-600 text-white shadow-sm shadow-brand-600/20 hover:bg-brand-700 focus-visible:ring-brand-500; }
        .btn-dark { @apply btn bg-navy-900 text-white hover:bg-navy-800 focus-visible:ring-navy-700; }
        .btn-secondary { @apply btn bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 hover:border-slate-400 focus-visible:ring-slate-400; }
        .btn-ghost { @apply btn text-slate-600 hover:bg-slate-100 hover:text-navy-900 focus-visible:ring-slate-400; }
        .btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500; }
        .btn-soft-danger { @apply btn bg-red-50 text-red-700 hover:bg-red-100 focus-visible:ring-red-400; }
        .btn-lg { @apply h-12 px-6 text-base; }
        .btn-sm { @apply h-8 px-3 text-xs rounded-lg; }

        /* Square icon buttons for row actions. Always give them a title (shown as a tooltip). */
        .icon-btn { @apply inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-navy-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500; }
        .icon-btn-primary { @apply icon-btn text-brand-700 hover:bg-brand-50 hover:text-brand-800; }
        .icon-btn-danger { @apply icon-btn text-red-600 hover:bg-red-50 hover:text-red-700; }

        /* ---- Forms ---- */
        .label { @apply block text-sm font-bold text-slate-700 mb-1.5; }
        .required::after { content: ' *'; @apply text-red-500; }
        .input { @apply block w-full h-11 px-3.5 rounded-xl border border-slate-300 bg-white text-sm text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/15 disabled:bg-slate-50 disabled:text-slate-400; }
        textarea.input { @apply h-auto py-2.5 leading-relaxed; }
        select.input { @apply pl-9 bg-no-repeat; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: left 0.6rem center; background-size: 1.25em 1.25em; -webkit-appearance: none; appearance: none; }
        input[type=file].input { @apply h-auto p-1.5 text-slate-500 file:ml-3 file:h-8 file:px-3 file:rounded-lg file:border-0 file:bg-brand-50 file:text-brand-700 file:font-bold file:text-sm hover:file:bg-brand-100 file:cursor-pointer; }
        .input-error { @apply border-red-400 focus:border-red-500 focus:ring-red-500/15; }
        .hint { @apply text-xs text-slate-500 mt-1.5 leading-relaxed; }
        .field-error { @apply text-xs font-semibold text-red-600 mt-1.5; }
        .checkbox { @apply w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500; }

        /* On/off switch: <label class="switch"><input type="checkbox" class="peer sr-only" ...><span class="switch-track"></span><span>Text</span></label> */
        .switch { @apply inline-flex items-center gap-3 cursor-pointer select-none; }
        .switch-track { @apply relative w-11 h-6 shrink-0 rounded-full bg-slate-300 transition-colors peer-checked:bg-brand-600 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2 after:content-[''] after:absolute after:top-0.5 after:right-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:-translate-x-5; }

        /* Dashed drop zone around a file input */
        .dropzone { @apply flex flex-col items-center justify-center gap-2 w-full px-6 py-8 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 text-center cursor-pointer transition hover:border-brand-400 hover:bg-brand-50/40; }

        /* ---- Tables ---- */
        /* relative: keeps absolutely positioned bits (sr-only labels) inside the scroll box */
        .table-wrap { @apply relative overflow-x-auto; }
        .data-table { @apply w-full text-sm text-right; }
        .data-table thead th { @apply px-5 py-3 text-xs font-bold text-slate-500 bg-slate-50 border-b border-slate-200 whitespace-nowrap; }
        .data-table tbody td { @apply px-5 py-3.5 border-b border-slate-100 align-middle; }
        .data-table tbody tr { @apply transition-colors; }
        .data-table tbody tr:hover { @apply bg-slate-50/70; }
        .data-table tbody tr:last-child td { @apply border-b-0; }
        .thumb { @apply w-11 h-11 rounded-xl object-cover bg-slate-100 border border-slate-200/70 shrink-0; }
        .thumb-empty { @apply w-11 h-11 rounded-xl bg-slate-100 border border-dashed border-slate-300 text-slate-400 flex items-center justify-center shrink-0; }

        /* ---- Badges ---- */
        .badge { @apply inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap ring-1 ring-inset; }
        .badge-dot::before { content: ''; @apply w-1.5 h-1.5 rounded-full bg-current opacity-80; }
        .badge-success { @apply badge bg-emerald-50 text-emerald-700 ring-emerald-600/15; }
        .badge-warning { @apply badge bg-amber-50 text-amber-700 ring-amber-600/20; }
        .badge-danger { @apply badge bg-red-50 text-red-700 ring-red-600/15; }
        .badge-info { @apply badge bg-brand-50 text-brand-700 ring-brand-600/15; }
        .badge-navy { @apply badge bg-navy-50 text-navy-700 ring-navy-600/15; }
        .badge-violet { @apply badge bg-violet-50 text-violet-700 ring-violet-600/15; }
        .badge-neutral { @apply badge bg-slate-100 text-slate-600 ring-slate-500/15; }
        .chip { @apply inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold; }

        /* ---- Misc ---- */
        .kbd { @apply inline-flex items-center px-1.5 h-5 rounded border border-slate-300 bg-white text-[10px] font-bold text-slate-500 font-sans; }
        .link { @apply font-bold text-brand-700 hover:text-brand-800 hover:underline underline-offset-4; }
        .muted { @apply text-slate-500; }
        .money { @apply font-extrabold text-navy-900 tabular-nums whitespace-nowrap; }
        .money small { @apply text-[11px] font-bold text-slate-400 mr-0.5; }
    }

    /* Tooltip for elements with data-tip (icon buttons) */
    @layer utilities {
        [data-tip] { position: relative; }
        [data-tip]:hover::after, [data-tip]:focus-visible::after {
            content: attr(data-tip);
            @apply absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 rounded-md bg-navy-950 text-white text-[11px] font-bold whitespace-nowrap pointer-events-none z-50;
        }
    }
</style>
