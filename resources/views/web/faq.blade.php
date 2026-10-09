@extends('web.layouts.app')

@section('title', 'Frequently Asked Questions (FAQ) — IndianMSME')
@section('meta_description', 'Find answers to common questions about IndianMSME.org, our platform, services, buying, selling, orders, and policies.')

@push('styles')
<style>
  /* FAQ Hero Banner - Ultra-Premium Design System */
  .faq-hero-banner {
    background: radial-gradient(circle at 82% 50%, rgba(13, 110, 253, 0.18) 0%, rgba(0, 166, 81, 0.05) 45%, transparent 75%),
                linear-gradient(135deg, #02112b 0%, #051a42 50%, #02112b 100%) !important;
    position: relative !important;
    overflow: hidden !important;
    font-family: 'Poppins', sans-serif !important;
    padding: 24px 0 !important;
    min-height: 230px !important;
    display: flex !important;
    align-items: center !important;
  }

  /* Subtle ambient lighting effect behind 3D Graphic */
  .faq-hero-banner::after {
    content: '' !important;
    position: absolute !important;
    top: 50% !important;
    right: 8% !important;
    transform: translateY(-50%) !important;
    width: 320px !important;
    height: 220px !important;
    background: radial-gradient(ellipse at center, rgba(37, 99, 235, 0.25) 0%, transparent 70%) !important;
    pointer-events: none !important;
  }

  .faq-hero-flex {
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 32px !important;
    position: relative !important;
    z-index: 2 !important;
  }

  .faq-hero-content {
    max-width: 600px !important;
  }

  .faq-hero-title {
    font-size: 2.35rem !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    letter-spacing: -0.4px !important;
    line-height: 1.2 !important;
    margin: 0 0 10px 0 !important;
    font-family: 'Poppins', sans-serif !important;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
  }

  /* Green Accent Bar under title */
  .faq-accent-line {
    width: 48px !important;
    height: 4px !important;
    background: linear-gradient(90deg, #00a651 0%, #10b981 100%) !important;
    border-radius: 4px !important;
    margin-top: 0 !important;
    margin-bottom: 16px !important;
    box-shadow: 0 0 12px rgba(0, 166, 81, 0.4) !important;
  }

  .faq-hero-subtitle {
    font-size: 0.95rem !important;
    color: rgba(226, 232, 240, 0.9) !important;
    font-weight: 400 !important;
    line-height: 1.65 !important;
    margin: 0 !important;
    font-family: 'Poppins', sans-serif !important;
  }

  .faq-hero-image-wrap {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    flex-shrink: 0 !important;
  }

  .faq-hero-image-wrap img {
    height: 240px !important;
    max-height: 250px !important;
    width: auto !important;
    object-fit: contain !important;
    display: block !important;
    filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.35)) !important;
    transition: transform 0.3s ease !important;
  }

  .faq-hero-image-wrap img:hover {
    transform: translateY(-4px) scale(1.02) !important;
  }

  @media (max-width: 991px) {
    .faq-hero-banner {
      padding: 28px 0 !important;
    }
    .faq-hero-flex {
      flex-direction: column !important;
      text-align: center !important;
      gap: 22px !important;
    }
    .faq-hero-content {
      max-width: 100% !important;
    }
    .faq-hero-title {
      font-size: 1.85rem !important;
    }
    .faq-accent-line {
      margin-left: auto !important;
      margin-right: auto !important;
    }
    .faq-hero-image-wrap {
      justify-content: center !important;
    }
    .faq-hero-image-wrap img {
      height: 170px !important;
    }
  }

  /* Topic Sidebar Buttons - Exact Match */
  .topic-btn {
    width: 100% !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 10px 14px !important;
    border-radius: 8px !important;
    font-size: 0.85rem !important;
    font-weight: 500 !important;
    color: #475569 !important;
    background: transparent !important;
    border: none !important;
    transition: all 0.2s ease !important;
    text-align: left !important;
    cursor: pointer !important;
    font-family: 'Poppins', sans-serif !important;
  }
  .topic-btn:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
  }
  .topic-btn.active {
    background: #e8f7ee !important;
    color: #047857 !important;
    font-weight: 700 !important;
  }
  .topic-btn.active .topic-icon {
    color: #00a651 !important;
  }

  /* Accordion Styling - Exact Replica of Reference Image */
  .faq-accordion-item {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    margin-bottom: 8px !important;
    overflow: hidden !important;
    transition: all 0.2s ease !important;
  }
  .faq-accordion-item:hover {
    border-color: #cbd5e1 !important;
  }
  .faq-accordion-item.open {
    background: #f2faf5 !important;
    border-color: #d1fae5 !important;
  }

  .faq-accordion-header {
    width: 100% !important;
    padding: 13px 18px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 14px !important;
    background: transparent !important;
    border: none !important;
    text-align: left !important;
    font-size: 0.875rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    cursor: pointer !important;
    transition: color 0.2s ease !important;
    font-family: 'Poppins', sans-serif !important;
  }
  .faq-accordion-header:hover {
    color: #00a651 !important;
  }
  .faq-accordion-item.open .faq-accordion-header {
    color: #00a651 !important;
    background: transparent !important;
  }

  .faq-accordion-icon {
    transition: transform 0.25s ease !important;
    color: #64748b !important;
    flex-shrink: 0 !important;
  }
  .faq-accordion-item.open .faq-accordion-icon {
    transform: rotate(180deg) !important;
    color: #00a651 !important;
  }

  .faq-accordion-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), padding 0.35s ease;
    background: transparent;
    color: #334155;
    font-size: 0.835rem;
    line-height: 1.6;
    padding: 0 18px;
    font-family: 'Poppins', sans-serif;
  }
  .faq-accordion-item.open .faq-accordion-body {
    max-height: 500px;
    padding: 4px 18px 18px 18px;
    border-top: none;
  }

  /* Support Card Button - Match Image */
  .btn-contact-support {
    background: #00a651 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 0.875rem !important;
    padding: 11px 20px !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px !important;
    width: 100% !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 6px rgba(0, 166, 81, 0.2) !important;
    font-family: 'Poppins', sans-serif !important;
  }
  .btn-contact-support:hover {
    background: #008f47 !important;
    box-shadow: 0 4px 12px rgba(0, 166, 81, 0.3) !important;
  }

  /* FAQ Bottom Features Trust Banner - Pixel Perfect Replica */
  .faq-features-banner {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 22px 28px !important;
    margin-top: 28px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
  }

  .faq-features-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 0 !important;
    align-items: center !important;
  }

  .faq-feature-item {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 6px 20px !important;
    border-right: 1px solid #e2e8f0 !important;
  }

  .faq-feature-item:first-child {
    padding-left: 4px !important;
  }

  .faq-feature-item:last-child {
    border-right: none !important;
    padding-right: 4px !important;
  }

  .faq-feature-icon {
    width: 52px !important;
    height: 52px !important;
    border-radius: 50% !important;
    background: #ebf3ff !important;
    color: #1d6bf3 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
  }

  .faq-feature-title {
    font-size: 0.925rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin-bottom: 3px !important;
    font-family: 'Poppins', sans-serif !important;
    line-height: 1.2 !important;
  }

  .faq-feature-desc {
    font-size: 0.775rem !important;
    color: #475569 !important;
    line-height: 1.4 !important;
    margin: 0 !important;
    font-family: 'Poppins', sans-serif !important;
  }

  @media (max-width: 1024px) {
    .faq-features-grid {
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 20px !important;
    }
    .faq-feature-item {
      border-right: none !important;
      padding: 0 !important;
    }
  }

  @media (max-width: 640px) {
    .faq-features-grid {
      grid-template-columns: 1fr !important;
    }
  }
</style>
@endpush

@section('content')

{{-- ── 1. SLEEK & EXACT MATCH HERO BANNER SECTION ──────────────────────── --}}
<div class="faq-hero-banner text-white">
  <div class="container mx-auto px-4 sm:px-6">
    <div class="faq-hero-flex">
      
      {{-- Left Side: Crisp Title & Description --}}
      <div class="faq-hero-content">
        <h1 class="faq-hero-title">
          Frequently Asked Questions
        </h1>
        <div class="faq-accent-line"></div>
        <p class="faq-hero-subtitle">
          Find answers to common questions about IndianMSME.org,<br class="hidden sm:inline">
          our platform, services, and policies.
        </p>
      </div>

      {{-- Right Side: Perfectly Aligned 3D Illustration Image --}}
      <div class="faq-hero-image-wrap">
        <img src="{{ asset('images/faq-hero-3d.png') }}" 
             alt="Frequently Asked Questions Illustration">
      </div>

    </div>
  </div>
</div>

{{-- ── 2. COMPACT MAIN CONTENT SECTION ───────────────────────────────────── --}}
<section class="py-5 bg-slate-50/70" style="font-family: 'Poppins', sans-serif;">
  <div class="container mx-auto px-4 sm:px-6">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
      
      {{-- ── LEFT SIDEBAR: Browse Topics (3 Cols) ────────────────────────── --}}
      <div class="lg:col-span-3">
        <div class="bg-white rounded-xl border border-slate-200/80 p-3 shadow-sm">
          <h6 class="font-extrabold text-slate-900 text-xs mb-2 px-1 uppercase tracking-wider">Browse Topics</h6>
          
          <div class="flex flex-col gap-0.5">
            <button type="button" class="topic-btn active" onclick="filterFaq('all', this)">
              <i class="hgi hgi-stroke hgi-help-circle topic-icon text-sm"></i>
              <span>All Questions</span>
            </button>

            @foreach($topics as $topic)
              <button type="button" class="topic-btn" onclick="filterFaq('{{ $topic->slug }}', this)">
                <i class="hgi hgi-stroke {{ $topic->icon ?: 'hgi-information-circle' }} topic-icon text-sm"></i>
                <span>{{ $topic->name }}</span>
              </button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ── MIDDLE COLUMN: Accordion Questions List (6 Cols) ──────────────── --}}
      <div class="lg:col-span-6">
        
        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-3 px-1">
          <h4 id="faq-section-heading" class="font-extrabold text-slate-900 text-base sm:text-lg">
            All Questions
          </h4>
          <span class="text-xs text-slate-500">
            Can&apos;t find your answer? 
            <a href="{{ route('contact') }}" class="text-blue-600 font-bold hover:underline ml-1">Contact Us</a>
          </span>
        </div>

        {{-- Accordion Container --}}
        <div id="faq-accordion-container">
          @php $globalIndex = 0; @endphp
          @forelse($topics as $topic)
            @foreach($topic->faqs as $faq)
              @php $globalIndex++; @endphp
              <div class="faq-accordion-item {{ $globalIndex === 1 ? 'open' : '' }}" data-category="{{ $topic->slug }}">
                <button type="button" class="faq-accordion-header" onclick="toggleAccordion(this)">
                  <span>{{ $faq->question }}</span>
                  <i class="hgi hgi-stroke hgi-arrow-down-01 text-sm faq-accordion-icon"></i>
                </button>
                <div class="faq-accordion-body">
                  {!! nl2br(e($faq->answer)) !!}
                </div>
              </div>
            @endforeach
          @empty
            <div class="p-4 text-center text-slate-500 bg-white rounded-lg border border-slate-200">
              No FAQs available at the moment.
            </div>
          @endforelse
        </div>

      </div>

      {{-- ── RIGHT SIDEBAR: Still Need Help? Card (3 Cols) ────────────────── --}}
      <div class="lg:col-span-3">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
          
          <h5 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 3px; font-family: 'Poppins', sans-serif;">Still Need Help?</h5>
          <p style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin-bottom: 8px; font-family: 'Poppins', sans-serif;">We&apos;re here for you!</p>
          
          <p style="font-size: 0.8rem; color: #64748b; line-height: 1.5; margin-bottom: 18px; font-family: 'Poppins', sans-serif;">
            Our support team is ready to assist you with any questions or concerns.
          </p>

          <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 18px 0 20px 0;">

          {{-- Contact Info 1: Phone --}}
          <div class="flex items-start gap-4 mb-4">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #1d6bf3; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
            </div>
            <div>
              <div style="font-size: 0.925rem; font-weight: 800; color: #0f172a; font-family: 'Poppins', sans-serif;">
                <a href="tel:+918800001259" class="hover:text-emerald-600 transition-colors">+91 8800001259</a>, 
                <a href="tel:+919990060509" class="hover:text-emerald-600 transition-colors">+91 9990060509</a>
              </div>
              <span style="font-size: 0.775rem; color: #64748b; font-family: 'Poppins', sans-serif;" class="block mt-0.5 leading-tight">
                (Mon - Sat: 10:00 AM - 6:00 PM)
              </span>
            </div>
          </div>

          {{-- Contact Info 2: Email --}}
          <div class="flex items-start gap-4 mb-6">
            <div style="width: 38px; height: 38px; border-radius: 8px; border: 2px solid #1d6bf3; color: #1d6bf3; background: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </svg>
            </div>
            <div>
              <a href="mailto:help@indianmsme.org" style="font-size: 0.925rem; font-weight: 800; color: #0f172a; font-family: 'Poppins', sans-serif;" class="hover:underline block">
                help@indianmsme.org
              </a>
              <span style="font-size: 0.775rem; color: #64748b; font-family: 'Poppins', sans-serif;" class="block mt-0.5 leading-tight">
                We&apos;ll respond as soon as possible.
              </span>
            </div>
          </div>

          {{-- Contact Support Button --}}
          <a href="{{ route('contact') }}" class="btn-contact-support">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
              <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
            </svg>
            <span>Contact Support</span>
          </a>

        </div>
      </div>

    </div>

    {{-- ── 3. BOTTOM TRUST FEATURES BANNER (4 COLUMNS) ──────────────────── --}}
    <div class="faq-features-banner">
      <div class="faq-features-grid">

        {{-- Feature 1: Secure & Safe --}}
        <div class="faq-feature-item">
          <div class="faq-feature-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="m9 12 2 2 4-4"></path>
            </svg>
          </div>
          <div>
            <h6 class="faq-feature-title">Secure &amp; Safe</h6>
            <p class="faq-feature-desc">Your data and transactions are 100% secure with us.</p>
          </div>
        </div>

        {{-- Feature 2: Trusted Platform --}}
        <div class="faq-feature-item">
          <div class="faq-feature-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <circle cx="12" cy="8" r="6"></circle>
              <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
              <path d="m9 8 2 2 4-4"></path>
            </svg>
          </div>
          <div>
            <h6 class="faq-feature-title">Trusted Platform</h6>
            <p class="faq-feature-desc">Empowering thousands of businesses across India.</p>
          </div>
        </div>

        {{-- Feature 3: Easy Returns --}}
        <div class="faq-feature-item">
          <div class="faq-feature-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
              <path d="M3 3v5h5"></path>
              <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
              <path d="M16 16h5v5"></path>
            </svg>
          </div>
          <div>
            <h6 class="faq-feature-title">Easy Returns</h6>
            <p class="faq-feature-desc">Hassle-free returns and refund policy.</p>
          </div>
        </div>

        {{-- Feature 4: 24/7 Support --}}
        <div class="faq-feature-item">
          <div class="faq-feature-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
              <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
              <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
            </svg>
          </div>
          <div>
            <h6 class="faq-feature-title">24/7 Support</h6>
            <p class="faq-feature-desc">Our team is available anytime to help you.</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>

@push('scripts')
<script>
  // Filter FAQ Items by Category
  function filterFaq(category, btn) {
    // Update active tab styling
    document.querySelectorAll('.topic-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Update section title dynamically
    const labelSpan = btn.querySelector('span');
    document.getElementById('faq-section-heading').textContent = labelSpan ? labelSpan.textContent : 'Questions';

    // Show / hide accordion items
    const items = document.querySelectorAll('.faq-accordion-item');
    let firstMatch = true;

    items.forEach(item => {
      const itemCat = item.getAttribute('data-category');
      if (category === 'all' || itemCat === category) {
        item.style.display = 'block';
        if (firstMatch) {
          openAccordion(item);
          firstMatch = false;
        } else {
          closeAccordion(item);
        }
      } else {
        item.style.display = 'none';
        closeAccordion(item);
      }
    });
  }

  // Toggle Accordion Item
  function toggleAccordion(button) {
    const item = button.closest('.faq-accordion-item');
    if (item.classList.contains('open')) {
      closeAccordion(item);
    } else {
      document.querySelectorAll('.faq-accordion-item.open').forEach(openItem => {
        closeAccordion(openItem);
      });
      openAccordion(item);
    }
  }

  function openAccordion(item) {
    item.classList.add('open');
  }

  function closeAccordion(item) {
    item.classList.remove('open');
  }
</script>
@endpush

@endsection
