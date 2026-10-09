@extends('web.layouts.app')

@section('title', 'Contact Us — IndianMSME')
@section('meta_description', 'Get in touch with IndianMSME support team. We are here to help and answer any questions you might have.')

@section('og_title', 'Contact Us — IndianMSME')
@section('og_description', 'Get in touch with IndianMSME support team. Reach out for customer support, business inquiries or feedback.')
@section('og_url', route('contact'))

@push('styles')
  <style>
    /* Hero Banner Dark Background & Seamless Background Image */
    .contact-hero-banner {
      background: #04162e !important;
      position: relative !important;
      overflow: hidden !important;
      min-height: 320px !important;
      display: flex !important;
      align-items: center !important;
    }

    /* Green Accent Bar under title matching FAQ */
    .contact-accent-line {
      width: 48px !important;
      height: 4px !important;
      background: linear-gradient(90deg, #00a651 0%, #10b981 100%) !important;
      border-radius: 4px !important;
      margin-top: 2px !important;
      margin-bottom: 14px !important;
      box-shadow: 0 0 12px rgba(0, 166, 81, 0.4) !important;
    }

    .contact-hero-bg-img {
      position: absolute !important;
      top: 0 !important;
      right: 0 !important;
      bottom: 0 !important;
      width: 48% !important;
      height: 100% !important;
      pointer-events: none !important;
    }

    .contact-hero-bg-img img {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
      object-position: center 25% !important;
    }

    .contact-hero-overlay {
      position: absolute !important;
      inset: 0 !important;
      background: linear-gradient(to right, #04162e 0%, rgba(4, 22, 46, 0.75) 20%, rgba(4, 22, 46, 0.05) 60%, transparent 100%) !important;
    }

    @media (max-width: 992px) {
      .contact-hero-bg-img {
        width: 100% !important;
        opacity: 0.25 !important;
      }

      .contact-hero-overlay {
        background: linear-gradient(to right, #04162e 0%, rgba(4, 22, 46, 0.9) 100%) !important;
      }
    }

    /* 4 Top Cards Grid - Single Row Layout */
    .contact-info-grid {
      display: grid !important;
      grid-template-columns: repeat(4, 1fr) !important;
      gap: 20px !important;
      margin-bottom: 28px !important;
      width: 100% !important;
    }

    @media (max-width: 1024px) {
      .contact-info-grid {
        grid-template-columns: repeat(2, 1fr) !important;
      }
    }

    @media (max-width: 640px) {
      .contact-info-grid {
        grid-template-columns: 1fr !important;
      }
    }

    .contact-info-card {
      background: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 14px !important;
      padding: 20px 22px !important;
      display: flex !important;
      align-items: flex-start !important;
      gap: 16px !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
      transition: all 0.25s ease !important;
    }

    .contact-info-card:hover {
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06) !important;
      transform: translateY(-2px) !important;
    }

    .icon-badge {
      width: 52px !important;
      height: 52px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.14) !important;
    }

    .icon-badge-green {
      background: #00a651 !important;
      color: #ffffff !important;
    }

    .icon-badge-blue {
      background: #1877f2 !important;
      color: #ffffff !important;
    }

    .icon-badge-orange {
      background: #f97316 !important;
      color: #ffffff !important;
    }

    .icon-badge-purple {
      background: #7c3aed !important;
      color: #ffffff !important;
    }

    /* 2-Column Main Section (Form + Map) */
    .contact-main-grid {
      display: grid !important;
      grid-template-columns: 1fr 1fr !important;
      gap: 24px !important;
      margin-bottom: 28px !important;
      width: 100% !important;
      align-items: stretch !important;
    }

    @media (max-width: 992px) {
      .contact-main-grid {
        grid-template-columns: 1fr !important;
      }
    }

    .contact-box {
      background: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 16px !important;
      padding: 28px !important;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03) !important;
      height: 100% !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: space-between !important;
    }

    /* Form 3 Inputs Row */
    .contact-form-row {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 14px !important;
    }

    @media (max-width: 640px) {
      .contact-form-row {
        grid-template-columns: 1fr !important;
      }
    }

    .contact-input-field {
      width: 100% !important;
      padding: 10px 14px !important;
      background: #ffffff !important;
      border: 1px solid #cbd5e1 !important;
      border-radius: 8px !important;
      font-size: 0.85rem !important;
      color: #0f172a !important;
      outline: none !important;
      transition: all 0.2s ease !important;
    }

    .contact-input-field:focus {
      border-color: #00a651 !important;
      background: #ffffff !important;
      box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.12) !important;
    }

    .accent-line {
      width: 28px !important;
      height: 3px !important;
      background: #00a651 !important;
      border-radius: 4px !important;
      margin-top: 4px !important;
      margin-bottom: 18px !important;
    }

    /* Bottom Support Features Banner */
    .contact-features-banner {
      background: #edf7f3 !important;
      border: 1px solid #dcece4 !important;
      border-radius: 16px !important;
      padding: 24px 12px !important;
      display: grid !important;
      grid-template-columns: repeat(4, 1fr) !important;
      gap: 0 !important;
      width: 100% !important;
    }

    .contact-feature-item {
      display: flex !important;
      align-items: flex-start !important;
      gap: 16px !important;
      padding: 0 20px !important;
      border-right: 1px solid #d2e4da !important;
    }

    .contact-feature-item:last-child {
      border-right: none !important;
    }

    .feature-icon-badge {
      width: 52px !important;
      height: 52px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
    }

    .feature-icon-green {
      background: #dcfce7 !important;
      color: #00a651 !important;
    }

    .feature-icon-blue {
      background: #e0f2fe !important;
      color: #0284c7 !important;
    }

    .feature-icon-orange {
      background: #ffedd5 !important;
      color: #ea580c !important;
    }

    .feature-icon-purple {
      background: #f3e8ff !important;
      color: #7c3aed !important;
    }

    @media (max-width: 1024px) {
      .contact-features-banner {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 20px !important;
      }

      .contact-feature-item {
        border-right: none !important;
        padding: 0 !important;
      }
    }

    @media (max-width: 640px) {
      .contact-features-banner {
        grid-template-columns: 1fr !important;
      }
    }

    .btn-submit-green {
      background: #00a651 !important;
      color: #ffffff !important;
      font-weight: 700 !important;
      font-size: 0.85rem !important;
      padding: 11px 26px !important;
      border-radius: 8px !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 8px !important;
      border: none !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
    }

    .btn-submit-green:hover {
      background: #008f47 !important;
      box-shadow: 0 4px 12px rgba(0, 166, 81, 0.25) !important;
    }
  </style>
@endpush

@section('content')

  {{-- ── 1. Hero Banner Section (Seamless Full Height Image Background) ───── --}}
  <div class="contact-hero-banner text-white py-12 lg:py-16">

    {{-- Right Side Seamless Image with Gradient Fade --}}
    <div class="contact-hero-bg-img">
      <img src="{{ asset('images/contact-agent.jpg') }}" alt="Customer Support Executive">
      <div class="contact-hero-overlay"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 relative z-10">
      <div class="max-w-xl py-2">
        <h1 class="font-extrabold text-white tracking-tight mb-0" style="font-size: 2.35rem !important; margin-bottom: 0 !important;">
          Contact Us
        </h1>
        <div class="contact-accent-line"></div>
        <p class="text-slate-300 leading-relaxed" style="font-size: 0.95rem !important;">
          We&apos;re here to help and answer any questions you might have. We look forward to hearing from you!
        </p>
      </div>
    </div>
  </div>

  <section class="py-8 bg-slate-50/70">
    <div class="container mx-auto px-4 sm:px-6">

      {{-- ── 2. Contact Info Cards (4 Columns in 1 Row) ────────────────── --}}
      <div class="contact-info-grid">

        {{-- Card 1: Our Address --}}
        <div class="contact-info-card">
          <div class="icon-badge icon-badge-green">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <div>
            <h6 class="font-extrabold mb-1.5" style="color: #071a41 !important; font-size: 16px !important; font-family: 'Poppins', sans-serif;">Our Address</h6>
            <p style="font-size: 13px !important; color: #334155 !important; line-height: 1.55 !important; margin: 0 !important;">
              <strong style="color: #071a41 !important; font-weight: 700;">IndianMSME.org</strong><br>
              B 29, 2nd Floor, Pushpanjali Enclave,<br>
              Pitampura, New Delhi - 110034, India
            </p>
          </div>
        </div>

        {{-- Card 2: Call Us --}}
        <div class="contact-info-card">
          <div class="icon-badge icon-badge-blue">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
              </path>
            </svg>
          </div>
          <div>
            <h6 class="font-extrabold mb-1.5" style="color: #071a41 !important; font-size: 16px !important; font-family: 'Poppins', sans-serif;">Call Us</h6>
            <p style="font-size: 13.5px !important; font-weight: 700; color: #0d6efd !important; margin-bottom: 4px !important; line-height: 1.45 !important;">
              <a href="tel:+918800001259" style="color: #0d6efd !important; text-decoration: none;" class="hover:underline">+91 8800001259</a>,<br>
              <a href="tel:+919990060509" style="color: #0d6efd !important; text-decoration: none;" class="hover:underline">+91 9990060509</a>
            </p>
            <p style="font-size: 12px !important; color: #64748b !important; line-height: 1.4 !important; margin: 0 !important;">
              Mon – Sat: 10:00 AM – 6:00 PM<br>
              <span style="color: #ef4444 !important; font-weight: 700;">Sunday CLOSED</span>
            </p>
          </div>
        </div>

        {{-- Card 3: Email Us --}}
        <div class="contact-info-card">
          <div class="icon-badge icon-badge-orange">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
          <div>
            <h6 class="font-extrabold mb-1.5" style="color: #071a41 !important; font-size: 16px !important; font-family: 'Poppins', sans-serif;">Email Us</h6>
            <p style="font-size: 13.5px !important; font-weight: 700; color: #00a651 !important; margin-bottom: 4px !important; line-height: 1.4 !important;">
              <a href="mailto:help@indianmsme.org" style="color: #00a651 !important; text-decoration: none;" class="hover:underline">help@indianmsme.org</a>
            </p>
            <p style="font-size: 12.5px !important; color: #64748b !important; line-height: 1.4 !important; margin: 0 !important;">
              We&apos;ll respond as soon as possible.
            </p>
          </div>
        </div>

        {{-- Card 4: Working Hours --}}
        <div class="contact-info-card">
          <div class="icon-badge icon-badge-purple">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div>
            <h6 class="font-extrabold mb-1.5" style="color: #071a41 !important; font-size: 16px !important; font-family: 'Poppins', sans-serif;">Working Hours</h6>
            <p style="font-size: 13px !important; color: #334155 !important; font-weight: 600; margin-bottom: 4px !important; line-height: 1.4 !important;">
              Monday – Saturday<br>
              10:00 AM – 6:00 PM
            </p>
            <p style="font-size: 12.5px !important; color: #ef4444 !important; font-weight: 700; margin: 0 !important;">Sunday CLOSED</p>
          </div>
        </div>

      </div>

      {{-- ── 3. Main Form & Map Grid (2 Columns Equal Width & Height) ───── --}}
      <div class="contact-main-grid">

        {{-- Left Side: Send Us a Message Form --}}
        <div class="contact-box">
          <div>
            <h5 class="font-extrabold text-gray-900 text-base sm:text-lg leading-tight">Send Us a Message</h5>
            <div class="accent-line"></div>

            <form id="contact-us-form" method="POST" action="{{ route('contact.submit') }}" class="flex flex-col gap-3.5">
              @csrf

              {{-- 3 Fields Row (Your Name *, Email Address *, Phone Number *) --}}
              <div class="contact-form-row">
                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">
                    Your Name <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="name" required placeholder="Enter your name" value="{{ old('name') }}"
                    class="contact-input-field">
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">
                    Email Address <span class="text-red-500">*</span>
                  </label>
                  <input type="email" name="email" required placeholder="Enter your email" value="{{ old('email') }}"
                    class="contact-input-field">
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-700 mb-1">
                    Phone Number <span class="text-red-500">*</span>
                  </label>
                  <input type="tel" name="phone" required maxlength="10" minlength="10" pattern="[0-9]{10}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    placeholder="Enter 10-digit mobile number" value="{{ old('phone') }}"
                    class="contact-input-field">
                </div>
              </div>

              {{-- Subject --}}
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  Subject <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subject" required placeholder="Enter subject" value="{{ old('subject') }}"
                  class="contact-input-field">
              </div>

              {{-- Message --}}
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                  Your Message <span class="text-red-500">*</span>
                </label>
                <textarea name="message" required rows="4" placeholder="Type your message here..."
                  class="contact-input-field resize-y">{{ old('message') }}</textarea>
              </div>

              {{-- Submit Button --}}
              <div class="pt-1">
                <button type="submit" class="btn-submit-green">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                    stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                  </svg>
                  Send Message
                </button>
              </div>

            </form>
          </div>
        </div>

        {{-- Right Side: Find Us On Map --}}
        <div class="contact-box">
          <div>
            <h5 class="font-extrabold text-gray-900 text-base sm:text-lg leading-tight">Find Us On Map</h5>
            <div class="accent-line"></div>
          </div>

          <div
            class="w-full flex-1 min-h-[340px] rounded-xl overflow-hidden border border-gray-200 relative shadow-inner">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3499.789724128562!2d77.1264849!3d28.6959556!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d03c4015696d5%3A0xb35a0928929e0abf!2sPushpanjali%20Enclave%2C%20Pitampura%2C%20Delhi%2C%20110034!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin"
              width="100%" height="100%" style="border:0; min-height: 340px; width: 100%; border-radius: 0.75rem;"
              allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        </div>

      </div>

      {{-- ── 4. Bottom Support Features Row (Explicit SVGs & Crisp Typography) ── --}}
      <div class="contact-features-banner">

        {{-- Feature 1: Customer Support --}}
        <div class="contact-feature-item">
          <div class="feature-icon-badge feature-icon-green">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
              <path
                d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z">
              </path>
            </svg>
          </div>
          <div>
            <h6 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Customer Support</h6>
            <p class="text-xs text-gray-600 leading-relaxed font-normal">
              Need help with orders or general inquiries? Our support team is ready to assist you.
            </p>
          </div>
        </div>

        {{-- Feature 2: Business Inquiries --}}
        <div class="contact-feature-item">
          <div class="feature-icon-badge feature-icon-blue">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <div>
            <h6 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Business Inquiries</h6>
            <p class="text-xs text-gray-600 leading-relaxed font-normal">
              For partnership, distribution or wholesale inquiries, reach out to us.
            </p>
          </div>
        </div>

        {{-- Feature 3: Feedback --}}
        <div class="contact-feature-item">
          <div class="feature-icon-badge feature-icon-orange">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path
                d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
              </path>
            </svg>
          </div>
          <div>
            <h6 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Feedback</h6>
            <p class="text-xs text-gray-600 leading-relaxed font-normal">
              We value your feedback to improve our services and platform.
            </p>
          </div>
        </div>

        {{-- Feature 4: Safe & Secure (UPDATED SHIELD CHECKMARK SVG) --}}
        <div class="contact-feature-item">
          <div class="feature-icon-badge feature-icon-purple">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
              stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="M9 12l2 2 4-4"></path>
            </svg>
          </div>
          <div>
            <h6 class="font-bold text-gray-900 text-sm sm:text-base mb-1">Safe &amp; Secure</h6>
            <p class="text-xs text-gray-600 leading-relaxed font-normal">
              Your information is safe with us. We respect your privacy.
            </p>
          </div>
        </div>

      </div>

    </div>
  </section>

@endsection