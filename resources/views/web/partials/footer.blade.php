{{-- Website Master Footer Partial --}}

{{-- Top Newsletter Banner --}}
<div class="footer-newsletter-banner bg-[#07182c] border-b border-white/10 text-left relative overflow-hidden"
    style="background-color: #07182c !important; padding-top: 32px !important; padding-bottom: 32px !important;">
    {{-- Indian Monuments Background Image on Right --}}
    <img src="{{ asset('images/indian-monuments-bg.png') }}" alt="Indian Monuments Background"
        class="absolute right-0 bottom-0 h-full max-h-[125px] w-auto max-w-[480px] pointer-events-none opacity-85 object-contain hidden md:block z-0"
        style="object-position: right bottom !important;" />

    <div class="container mx-auto flex flex-col lg:flex-row items-center relative z-10">
        <div class="text-left flex-shrink-0">
            <h3 class="text-2xl sm:text-[26px] font-extrabold text-white tracking-tight mb-1"
                style="color: #ffffff !important; font-weight: 800; font-family: inherit;">
                Stay Connected With Indian MSME
            </h3>
            <p class="text-sm text-slate-300 font-normal footer-banner-subtext"
                style="color: #cbd5e1 !important; font-size: 14px !important;">
                Get new product launches, exclusive offers and inspiring stories from <span class="font-semibold"
                    style="color: #00a651 !important;">India's makers.</span>
            </p>
        </div>
        <div class="flex-1 flex justify-center items-center px-4 footer-subscribe-wrapper">
            <form action="#" method="POST" class="flex items-center shadow-md" onsubmit="event.preventDefault();"
                style="background-color: #ffffff !important; border-radius: 9px !important; width: 100% !important; max-width: 440px !important; overflow: hidden !important; height: 46px !important;">
                @csrf
                <input type="email" placeholder="Enter your email address" required
                    class="flex-1 min-w-0 text-slate-800 placeholder-slate-400 text-sm px-4 focus:outline-none font-normal border-0"
                    style="background-color: #ffffff !important; color: #1e293b !important; height: 100% !important;" />
                <button type="submit"
                    class="text-white text-sm font-bold px-7 h-full transition-colors shrink-0 border-0 flex items-center justify-center cursor-pointer"
                    style="background-color: #00a651 !important; color: #ffffff !important; font-weight: 700; border-radius: 0 9px 9px 0 !important; height: 100% !important;">
                    Subscribe
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Main Footer Body --}}
<footer class="footer bg-[#07182c] text-white pt-12 pb-8 text-left" style="background-color: #07182c !important;">
    <div class="container mx-auto px-4">

        {{-- Custom 6-Column Single Horizontal Row Grid --}}
        <div class="footer-six-col-grid pb-12">

            {{-- Column 1: Brand & Logo --}}
            <div class="footer-col brand-col">
                <a href="{{ url('/') }}" class="inline-block mb-3">
                    @php
                        $siteFooterLogo = \App\Models\SystemSetting::getVal('website_footer_logo', \App\Models\SystemSetting::getVal('website_header_logo'));
                        $siteFooterLogoUrl = $siteFooterLogo ? (filter_var($siteFooterLogo, FILTER_VALIDATE_URL) ? $siteFooterLogo : asset($siteFooterLogo)) : asset('admin-assets/images/IndianMSME.png');
                    @endphp
                    <div class="bg-white px-3 py-1.5 rounded-md inline-flex items-center justify-center shadow-sm">
                        <img src="{{ $siteFooterLogoUrl }}" alt="Indian MSME Logo"
                            class="h-12 max-h-14 w-auto object-contain" />
                    </div>
                </a>
                <p class="footer-brand-desc">
                    Empowering India's MSMEs by connecting makers to markets across the nation.
                </p>
                {{-- Social Icons --}}
                <div class="flex items-center gap-3 mt-4">
                    <a href="https://www.facebook.com/Indianmsme.org/" target="_blank" class="footer-social-icon"
                        title="Facebook">
                        <i class="hgi hgi-stroke hgi-facebook-01"></i>
                    </a>
                    <a href="https://www.instagram.com/indianmsme/" target="_blank" class="footer-social-icon"
                        title="Instagram">
                        <i class="hgi hgi-stroke hgi-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/@IndianMSME" target="_blank" class="footer-social-icon"
                        title="YouTube">
                        <i class="hgi hgi-stroke hgi-youtube"></i>
                    </a>
                    <a href="https://www.linkedin.com/company/indianmsme/" target="_blank" class="footer-social-icon"
                        title="LinkedIn">
                        <i class="hgi hgi-stroke hgi-linkedin-01"></i>
                    </a>
                    <a href="https://twitter.com/indianmsme" target="_blank" class="footer-social-icon" title="Twitter">
                        <i class="hgi hgi-stroke hgi-new-twitter"></i>
                    </a>
                </div>
            </div>

            {{-- Column 2: Shop --}}
            <div class="footer-col">
                <h5 class="footer-col-heading">
                    Shop
                    <span class="footer-heading-line"></span>
                </h5>
                <ul class="footer-links-list">
                    <li><a href="{{ route('shop') }}">All Categories</a></li>
                    <li><a href="{{ route('shop') }}">Best Sellers</a></li>
                    <li><a href="{{ route('shop') }}">New Arrivals</a></li>
                    <li><a href="{{ route('shop') }}">Daily Deals</a></li>
                    <li><a href="{{ route('shop') }}">Brands</a></li>
                </ul>
            </div>

            {{-- Column 3: Our Platform --}}
            <div class="footer-col">
                <h5 class="footer-col-heading">
                    Our Platform
                    <span class="footer-heading-line"></span>
                </h5>
                <ul class="footer-links-list">
                    <li><a href="http://ddc.{{ config('app.domain', 'localhost') }}/login">DDC</a></li>
                    <li><a href="http://adc.{{ config('app.domain', 'localhost') }}/login">ADC</a></li>
                    <li><a href="http://vendor.{{ config('app.domain', 'localhost') }}/login">DDB</a></li>
                    <li><a href="{{ route('shop') }}">Services</a></li>
                    <li><a href="#">Download App</a></li>
                </ul>
            </div>

            {{-- Column 4: Customer Service --}}
            <div class="footer-col">
                <h5 class="footer-col-heading">
                    Customer Service
                    <span class="footer-heading-line"></span>
                </h5>
                <ul class="footer-links-list">
                    <li><a href="{{ url('/contact') }}">Contact Us</a></li>
                    <li><a href="{{ url('/contact') }}">Track Order</a></li>
                    <li><a href="{{ url('/about') }}">Shipping Policy</a></li>
                    <li><a href="{{ url('/about') }}">Return Policy</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                </ul>
            </div>

            {{-- Column 5: Seller Corner --}}
            <div class="footer-col">
                <h5 class="footer-col-heading">
                    Seller Corner
                    <span class="footer-heading-line"></span>
                </h5>
                <ul class="footer-links-list">
                    <li><a href="{{ route('vendor-account') }}">Seller Registration</a></li>
                    <li><a href="http://vendor.{{ config('app.domain', 'localhost') }}/login">Seller Login</a></li>
                    <li><a href="{{ route('vendor-account') }}">Seller Guide</a></li>
                    <li><a href="{{ url('/contact') }}">Seller Support</a></li>
                </ul>
            </div>

            {{-- Column 6: Contact Us --}}
            <div class="footer-col contact-col">
                <h5 class="footer-col-heading">
                    Contact Us
                    <span class="footer-heading-line"></span>
                </h5>
                <ul class="footer-contact-list">
                    <li>
                        <i class="hgi hgi-stroke hgi-location-01 footer-icon"></i>
                        <span>B 29, 2nd Floor, Pushpanjali Enclave,<br />Pitampura New Delhi-110034</span>
                    </li>
                    <li>
                        <i class="hgi hgi-stroke hgi-call footer-icon"></i>
                        <a href="tel:+918800001259">+91 8800001259</a>, <a href="tel:+919990060509">+91 9990060509</a>
                    </li>
                    <li>
                        <i class="hgi hgi-stroke hgi-mail-01 footer-icon"></i>
                        <a href="mailto:help@indianmsme.org">help@indianmsme.org</a>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Bottom Copyright Bar --}}
        <div class="footer-bottom-bar">
            <p>© {{ date('Y') }} IndianMSME. All Rights Reserved.</p>
            <div class="footer-legal-links">
                <a href="#">Privacy Policy</a>
                <span class="legal-divider">|</span>
                <a href="#">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>

<style>
    @media (min-width: 1024px) {
        .footer-subscribe-wrapper {
            padding-right: 280px !important;
        }
    }

    .footer-six-col-grid {
        display: grid;
        grid-template-columns: 2.2fr 1.1fr 1.1fr 1.3fr 1.3fr 2.1fr;
        gap: 28px;
        width: 100%;
    }

    .footer-col {
        display: flex;
        flex-direction: column;
    }

    .footer-brand-desc {
        color: #cbd5e1 !important;
        font-size: 14.5px !important;
        line-height: 1.55 !important;
        margin: 0 0 16px 0 !important;
        max-width: 280px;
    }

    .footer-social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.45);
        background-color: transparent;
        color: #ffffff !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        font-size: 25px;
    }

    .footer-social-icon:hover {
        background-color: #00a651 !important;
        border-color: #00a651 !important;
        color: #ffffff !important;
    }

    .footer-col-heading {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 20px !important;
        margin: 0 0 12px 0 !important;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .footer-heading-line {
        display: block;
        height: 3px;
        width: 32px;
        background-color: #00a651 !important;
        border-radius: 2px;
        margin-top: 6px;
    }

    .footer-links-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .footer-links-list li a {
        color: #cbd5e1 !important;
        font-size: 16px !important;
        text-decoration: none !important;
        transition: color 0.2s ease;
        font-weight: 400 !important;
    }

    .footer-links-list li a:hover {
        color: #ffffff !important;
    }

    .footer-contact-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .footer-contact-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #cbd5e1 !important;
        font-size: 16px !important;
        line-height: 1.5 !important;
    }

    .footer-contact-list li a {
        color: #cbd5e1 !important;
        text-decoration: none !important;
        transition: color 0.2s ease;
    }

    .footer-contact-list li a:hover {
        color: #ffffff !important;
    }

    .footer-icon {
        color: #00a651 !important;
        font-size: 20px !important;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .footer-bottom-bar {
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding-top: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: #cbd5e1;
        font-size: 14.5px;
    }

    .footer-bottom-bar p {
        margin: 0 !important;
        color: #cbd5e1 !important;
        font-size: 14.5px !important;
    }

    .footer-legal-links {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .footer-legal-links a {
        color: #cbd5e1 !important;
        font-size: 14.5px !important;
        text-decoration: none !important;
        transition: color 0.2s ease;
    }

    .footer-legal-links a:hover {
        color: #ffffff !important;
    }

    .legal-divider {
        color: #475569 !important;
    }

    @media (max-width: 1024px) {
        .footer-subscribe-wrapper {
            padding-right: 0 !important;
            width: 100% !important;
            margin-top: 16px;
        }
        .footer-six-col-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px 20px;
        }
    }

    @media (max-width: 640px) {
        .footer-banner-subtext {
            white-space: normal !important;
            font-size: 13px !important;
            text-align: center;
        }
        .footer-newsletter-banner .text-left {
            text-align: center !important;
        }
        .footer-six-col-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px 16px;
        }
        .footer-col.brand-col {
            grid-column: span 2 / span 2;
            text-align: center;
            align-items: center;
        }
        .footer-col.brand-col .footer-brand-desc {
            max-width: 100%;
        }
        .footer-col-heading {
            font-size: 17px !important;
        }
        .footer-links-list li a, .footer-contact-list li {
            font-size: 14px !important;
        }
        .footer-bottom-bar {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
    }
</style>