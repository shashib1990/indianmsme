<!-- ========== HEADER START ========== -->
@php
    $adcUser = \App\Models\User::getLoggedInAdc();
    $cartCount = $cartCount ?? 0;
@endphp
<header class="header sticky top-0 z-40 bg-white shadow-sm w-full">
    <!-- Top Utility Bar (Desktop Only) -->
    <div class="header-top-bar py-2 text-xs font-sans hidden xl:block"
        style="background-color: #06172e !important; color: #ffffff !important; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-x-6">
                    <a href="tel:+918800001259" class="flex items-center gap-x-2 transition-colors min-h-[32px]"
                        style="color: #cbd5e1 !important;">
                        <i class="hgi hgi-stroke hgi-call text-sm" style="color: #cbd5e1 !important;"></i>
                        <span style="color: #ffffff !important;">+91 8800001259 / +91 9990060509</span>
                    </a>
                    <a href="mailto:help@indianmsme.org"
                        class="flex items-center gap-x-2 transition-colors min-h-[32px]"
                        style="color: #cbd5e1 !important;">
                        <i class="hgi hgi-stroke hgi-mail-01 text-sm" style="color: #cbd5e1 !important;"></i>
                        <span style="color: #ffffff !important;">help@indianmsme.org</span>
                    </a>
                </div>
                <div class="flex items-center gap-x-4" style="color: #ffffff !important;">
                    <a href="{{ url('/faq') }}" class="hover:text-amber-400 transition-colors font-semibold"
                        style="color: #ffffff !important; font-weight: 600;">FAQ</a>
                    <span
                        style="color: rgba(255,255,255,0.45); font-size: 16px; font-weight: bold; line-height: 1;">|</span>
                    <a href="{{ route('vendor-account') }}" class="hover:text-amber-400 transition-colors font-semibold"
                        style="color: #ffffff !important; font-weight: 600;">Seller Registration</a>
                    <span
                        style="color: rgba(255,255,255,0.45); font-size: 16px; font-weight: bold; line-height: 1;">|</span>
                    <div id="adc-dropdown-wrapper" class="relative cursor-pointer py-1">
                        <button id="adc-dropdown-trigger" type="button"
                            class="flex items-center gap-x-1 hover:text-amber-400 transition-colors font-semibold select-none bg-transparent border-0 p-0 focus:outline-none cursor-pointer"
                            style="color: #ffffff !important; font-weight: 600;">
                            <i class="hgi hgi-stroke hgi-user text-sm" style="color: #93c5fd;"></i>
                            <span>
                                @if($adcUser)
                                    {{ $adcUser->name }}
                                @else
                                    ADC
                                @endif
                            </span>
                            <i class="hgi hgi-stroke hgi-arrow-down-01 text-xs"></i>
                        </button>
                        <div id="adc-dropdown-menu"
                            class="adc-dropdown-popup absolute left-0 top-full mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 py-2 hidden z-50 text-gray-800"
                            style="background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15) !important; width: 170px !important; min-width: 170px !important; max-width: 170px !important;">
                            <div class="adc-dropdown-title px-3 pb-1.5 mb-1 text-[10px] font-bold uppercase tracking-wider text-left"
                                style="color: #64748b !important; border-bottom: 1px solid #f1f5f9 !important;">
                                ADC PANEL
                            </div>
                            @if($adcUser)
                                <a href="{{ route('adc.dashboard') }}"
                                    class="flex items-center justify-start gap-2 px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors text-left"
                                    style="color: #1e293b !important;">
                                    <i class="hgi hgi-stroke hgi-dashboard-01 text-sm"
                                        style="color: #0d6efd !important;"></i> Dashboard
                                </a>
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('adc-header-logout-form-partial').submit();"
                                    class="flex items-center justify-start gap-2 px-3 py-2 text-xs font-semibold hover:bg-red-50 transition-colors text-left"
                                    style="color: #dc2626 !important;">
                                    <i class="hgi hgi-stroke hgi-logout text-sm" style="color: #dc2626 !important;"></i>
                                    Logout
                                </a>
                                <form id="adc-header-logout-form-partial" action="{{ route('logout') }}" method="POST"
                                    class="hidden">@csrf</form>
                            @else
                                <a href="{{ route('login', ['tab' => 'login']) }}"
                                    class="adc-login-link flex items-center justify-start gap-2 px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors text-left"
                                    style="color: #1e293b !important;">
                                    <i class="hgi hgi-stroke hgi-login-01 text-sm" style="color: #16a34a !important;"></i>
                                    Login
                                </a>
                                <a href="{{ route('login', ['tab' => 'register']) }}"
                                    class="adc-register-link flex items-center justify-start gap-2 px-3 py-2 text-xs font-semibold hover:bg-slate-50 transition-colors text-left"
                                    style="color: #0d6efd !important;">
                                    <i class="hgi hgi-stroke hgi-user-add-01 text-sm"
                                        style="color: #0d6efd !important;"></i> Register
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #adc-dropdown-wrapper:hover #adc-dropdown-menu,
        #adc-dropdown-menu.show-dropdown {
            display: block !important;
        }
    </style>
    <script>
        (function () {
            function initAdcDropdown() {
                var wrapper = document.getElementById('adc-dropdown-wrapper');
                var trigger = document.getElementById('adc-dropdown-trigger');
                var menu = document.getElementById('adc-dropdown-menu');

                if (trigger && menu && wrapper) {
                    trigger.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        menu.classList.toggle('show-dropdown');
                    });

                    document.addEventListener('click', function (e) {
                        if (!wrapper.contains(e.target)) {
                            menu.classList.remove('show-dropdown');
                        }
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAdcDropdown);
            } else {
                initAdcDropdown();
            }
        })();
    </script>

    <!-- Main Header Bar -->
    <div class="header-main py-2.5 sm:py-3.5"
        style="background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0 !important;">
        <div class="container mx-auto px-3 sm:px-4">
            <div class="flex items-center justify-between gap-x-2 sm:gap-x-4 xl:gap-x-8">
                <button id="sidebar-menu-btn"
                    class="xl:hidden w-11 h-11 flex items-center justify-center rounded-lg hover:bg-gray-100 p-0 focus:outline-none cursor-pointer shrink-0"
                    aria-label="Open Mobile Menu" style="color: #071933 !important;">
                    <i class="hgi hgi-stroke hgi-menu-01 text-2xl"></i>
                </button>
                <a href="{{ url('/') }}" class="flex items-center gap-x-2 shrink-0 py-1">
                    @php
                        $siteHeaderLogo = \App\Models\SystemSetting::getVal('website_header_logo');
                        $siteHeaderLogoUrl = $siteHeaderLogo ? (filter_var($siteHeaderLogo, FILTER_VALIDATE_URL) ? $siteHeaderLogo : asset($siteHeaderLogo)) : null;
                    @endphp
                    @if($siteHeaderLogoUrl)
                        <img src="{{ $siteHeaderLogoUrl }}" alt="indian MSME" class="object-contain header-site-logo" />
                    @else
                        <div class="flex flex-col">
                            <div
                                class="flex items-baseline font-black tracking-tight leading-none text-xl sm:text-3xl xl:text-4xl">
                                <span style="color: #071933 !important; font-weight: 800;">indian</span>
                                <span style="color: #0066cc !important; font-weight: 900; margin-left: 2px;">MSME</span>
                                <span
                                    style="color: #f59e0b !important; font-size: 10px; font-weight: 700; vertical-align: top; margin-left: 1px;">™</span>
                            </div>
                            <span
                                style="color: #071933 !important; font-size: 7px; sm:font-size: 8.5px; xl:font-size: 9.5px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px;">
                                B2B E-COMMERCE PLATFORM
                            </span>
                        </div>
                    @endif
                </a>
                <div class="header-search-wrap flex-1 hidden xl:block relative z-30"
                    style="max-width: 900px; width: 100%;">
                    <form action="{{ route('shop') }}" method="GET" class="w-full">
                        <div class="search-input-container relative isolation-isolate rounded-lg overflow-hidden flex items-center transition-all"
                            style="border: 1px solid #d1d5db !important; background-color: #f8fafc !important;">
                            <input type="text" name="search"
                                class="header-search-input w-full px-4 py-2.5 text-sm focus:outline-none bg-transparent"
                                style="color: #1e293b !important;" placeholder="Search for products, brands and more..."
                                value="{{ request('search') }}" autocomplete="off" />
                            <button type="submit"
                                class="header-search-btn px-5 py-2.5 flex items-center justify-center transition-colors shrink-0 cursor-pointer min-h-[44px]"
                                style="background-color: #0d6efd !important; color: #ffffff !important; border: none !important;">
                                <i class="hgi hgi-stroke hgi-search-01 text-lg" style="color: #ffffff !important;"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="header-actions flex items-center gap-x-2 sm:gap-x-4 xl:gap-x-6 shrink-0">
                    <button id="pincode-trigger-btn"
                        class="flex items-center gap-x-1 sm:gap-x-2 text-left hover:opacity-85 transition-opacity cursor-pointer group focus:outline-none min-h-[40px] px-1 sm:px-0"
                        onclick="document.getElementById('pincode-modal-overlay').classList.remove('invisible'); document.getElementById('pincode-modal-overlay').classList.add('visible');">
                        <div class="pincode-icon-bg w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center transition-colors shrink-0"
                            style="background-color: #e0f2fe !important; color: #0d6efd !important;">
                            <i class="hgi hgi-stroke hgi-location-01 text-sm sm:text-lg"
                                style="color: #0d6efd !important;"></i>
                        </div>
                        <div class="flex flex-col text-xs leading-tight">
                            <span class="pincode-label-deliver text-slate-500"
                                style="color: #64748b !important; font-size: 11px;">Deliver to</span>
                            <span class="font-bold text-xs sm:text-sm flex items-center gap-x-0.5"
                                style="color: #0f172a !important; font-weight: 700;">
                                <span id="pincode-header-label"
                                    class="pincode-full-label-desktop">{{ session('user_pincode_name') ? session('user_pincode_name') : (session('user_pincode') ? session('user_pincode') : 'Select Pincode') }}</span>
                                <span id="pincode-header-label-mobile"
                                    class="pincode-number-mobile">{{ session('user_pincode') ? session('user_pincode') : 'Deliver to' }}</span>
                                <i class="hgi hgi-stroke hgi-arrow-down-01 text-[10px] sm:text-xs"
                                    style="color: #64748b !important;"></i>
                            </span>
                        </div>
                    </button>
                    <a href="{{ auth('adc')->check() ? route('adc.cart.index') : route('login') }}"
                        class="inline-flex items-center gap-x-1.5 sm:gap-x-2 text-left hover:opacity-85 transition-opacity cursor-pointer group min-h-[40px] justify-center px-1 sm:px-0"
                        style="text-decoration: none;">
                        <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl sm:text-2xl transition-colors shrink-0"
                            style="color: #0f172a !important;"></i>
                        <span id="my-cart-text" class="font-bold text-sm text-slate-900"
                            style="color: #0f172a !important; font-weight: 700;">My Cart</span>
                        <span class="js-cart-count shrink-0"
                            style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; border-radius: 50% !important; background-color: #00a651 !important; color: #ffffff !important; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; line-height: 1; aspect-ratio: 1 / 1;">
                            {{ $cartCount }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Dedicated Search Row (< 1200px) -->
    <div class="px-3 py-3 bg-white border-b border-gray-200 xl:hidden">
        <div class="container mx-auto">
            <form action="{{ route('shop') }}" method="GET" class="w-full">
                <div class="search-input-container relative isolation-isolate rounded-xl overflow-hidden flex items-center shadow-sm"
                    style="border: 1.5px solid #cbd5e1 !important; background-color: #f8fafc !important; height: 50px !important;">
                    <input type="text" name="search"
                        class="header-search-input w-full px-4 text-sm focus:outline-none bg-transparent"
                        style="color: #1e293b !important; font-size: 14px; height: 50px !important;"
                        placeholder="Search for products, brands and more..." value="{{ request('search') }}"
                        autocomplete="off" />
                    <button type="submit"
                        class="header-search-btn px-5 flex items-center justify-center shrink-0 cursor-pointer min-w-[54px]"
                        style="background-color: #0d6efd !important; color: #ffffff !important; border: none !important; height: 50px !important;">
                        <i class="hgi hgi-stroke hgi-search-01 text-xl" style="color: #ffffff !important;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Nav (Desktop Only) -->
    <div class="header-nav hidden xl:block"
        style="background-color: #071933 !important; color: #ffffff !important; border-top: 1px solid rgba(255,255,255,0.1); height: 58px !important;">
        <div class="container mx-auto px-4 h-full">
            <div class="flex items-center justify-between h-full">
                <div id="cat-dropdown-wrapper" class="relative h-full flex items-center">
                    <button id="dropdownButton" type="button"
                        class="shop-by-cat-btn px-6 text-sm font-semibold flex items-center gap-x-3 transition-colors cursor-pointer select-none"
                        style="background-color: #040e1f !important; color: #ffffff !important; border-right: 1px solid rgba(255,255,255,0.12) !important; height: 58px !important;">
                        <i class="hgi hgi-stroke hgi-menu-01 text-lg" style="color: #ffffff !important;"></i>
                        <span style="color: #ffffff !important;">Shop By Category</span>
                        <i class="hgi hgi-stroke hgi-arrow-down-01 text-xs ml-1" style="color: #ffffff !important;"></i>
                    </button>
                    <div id="dropdownMenu"
                        class="absolute left-0 top-full bg-white shadow-2xl rounded-b-2xl border border-slate-200 py-0 hidden z-50 text-slate-800 overflow-y-auto"
                        style="background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 20px 40px -10px rgba(7, 25, 51, 0.25) !important; width: 320px !important; min-width: 320px !important; max-height: 540px !important;">
                        <div class="px-4 py-3 flex items-center justify-between border-b border-slate-200"
                            style="background-color: #f8fafc !important; position: sticky !important; top: 0 !important; z-index: 30 !important; box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider"
                                style="color: #475569 !important;">
                                Main Categories
                            </span>
                            @php $activeCount = \App\Models\Category::active()->count(); @endphp
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                style="background-color: #e0f2fe !important; color: #0369a1 !important;">
                                {{ $activeCount }} Categories
                            </span>
                        </div>
                        <div class="py-1">
                            @foreach(\App\Models\Category::active()->get() as $hdrCat)
                                <a href="{{ route('category.show', $hdrCat->slug) }}"
                                    class="group flex items-center justify-between px-4 py-2.5 hover:bg-blue-50/80 transition-all duration-200 border-b border-slate-100/70 last:border-0"
                                    style="text-decoration: none;">
                                    <div class="flex items-center gap-x-3 min-w-0">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200/60 shrink-0 group-hover:border-blue-300 transition-colors">
                                            <img src="{{ $hdrCat->image_url }}" alt="{{ $hdrCat->name }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';" />
                                        </div>
                                        <span
                                            class="text-xs font-bold text-slate-800 truncate group-hover:text-blue-600 transition-colors"
                                            style="color: #1e293b !important;">
                                            {{ $hdrCat->name }}
                                        </span>
                                    </div>
                                    <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0"
                                        style="color: #0d6efd !important;"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <nav class="main-menu flex-1 pl-6 h-full flex items-center">
                    <ul class="flex items-center gap-x-6 text-sm font-medium h-full">
                        <li class="h-full flex items-center"><a href="{{ route('home') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ (request()->routeIs('home') || request()->is('/')) ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ (request()->routeIs('home') || request()->is('/')) ? '#f59e0b' : '#f8fafc' }} !important;">Home</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('about') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ request()->routeIs('about*') ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ request()->routeIs('about*') ? '#f59e0b' : '#f8fafc' }} !important;">About Us</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('shop') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ (request()->routeIs('shop*') || request()->routeIs('product*') || request()->routeIs('category*')) ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ (request()->routeIs('shop*') || request()->routeIs('product*') || request()->routeIs('category*')) ? '#f59e0b' : '#f8fafc' }} !important;">Shop</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('brands') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ (request()->routeIs('brands*') || request()->routeIs('brand*')) ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ (request()->routeIs('brands*') || request()->routeIs('brand*')) ? '#f59e0b' : '#f8fafc' }} !important;">Brands</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('vendors') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ (request()->routeIs('vendors*') || request()->routeIs('vendor*')) ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ (request()->routeIs('vendors*') || request()->routeIs('vendor*')) ? '#f59e0b' : '#f8fafc' }} !important;">Verified Sellers</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('contact') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ request()->routeIs('contact*') ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ request()->routeIs('contact*') ? '#f59e0b' : '#f8fafc' }} !important;">Contact Us</a></li>
                        <li class="h-full flex items-center"><a href="{{ route('faq') }}"
                                class="h-full flex items-center hover:text-white transition-colors {{ (request()->routeIs('faq*') || request()->is('faq')) ? 'text-amber-400 font-bold border-b-2 border-amber-400' : '' }}"
                                style="color: {{ (request()->routeIs('faq*') || request()->is('faq')) ? '#f59e0b' : '#f8fafc' }} !important;">FAQ</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <style>
        #cat-dropdown-wrapper:hover #dropdownMenu,
        #dropdownMenu.show-cat-dropdown {
            display: block !important;
        }

        #dropdownMenu::-webkit-scrollbar {
            width: 6px;
        }

        #dropdownMenu::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        #dropdownMenu::-webkit-scrollbar-thumb {
            background: #0d6efd;
            border-radius: 10px;
        }

        #dropdownMenu::-webkit-scrollbar-thumb:hover {
            background: #0b5ed7;
        }

        .header-site-logo {
            width: 115px !important;
            max-width: 125px !important;
            height: auto !important;
            max-height: 42px !important;
        }

        /* Mobile view default (< 640px) */
        #my-cart-text {
            display: none !important;
        }

        .pincode-label-deliver {
            display: none !important;
        }

        .pincode-full-label-desktop {
            display: none !important;
        }

        .pincode-number-mobile {
            display: inline !important;
        }

        /* Desktop view (>= 640px) */
        @media (min-width: 640px) {
            .header-site-logo {
                width: 170px !important;
                max-width: 220px !important;
                max-height: 60px !important;
            }

            #my-cart-text {
                display: inline-block !important;
            }

            .pincode-label-deliver {
                display: inline !important;
            }

            .pincode-full-label-desktop {
                display: inline !important;
            }

            .pincode-number-mobile {
                display: none !important;
            }
        }
    </style>
    <script>
        (function () {
            function initCatDropdown() {
                var wrapper = document.getElementById('cat-dropdown-wrapper');
                var btn = document.getElementById('dropdownButton');
                var menu = document.getElementById('dropdownMenu');
                if (btn && menu && wrapper) {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        menu.classList.toggle('show-cat-dropdown');
                    });
                    document.addEventListener('click', function (e) {
                        if (!wrapper.contains(e.target)) {
                            menu.classList.remove('show-cat-dropdown');
                        }
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCatDropdown);
            } else {
                initCatDropdown();
            }
        })();
    </script>
</header>
<!-- ========== HEADER END ========== -->