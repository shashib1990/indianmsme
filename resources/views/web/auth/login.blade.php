@extends('web.layouts.app')

@section('title', 'Welcome to Abhyam District Center — ADC Portal | Indian MSME')

@section('content')
<style>
  /* ==========================================================
   ABHYAM DISTRICT CENTER LOGIN & REGISTER PAGE
   Exact clone of requested design mockup & background
   ========================================================== */

  html, body { overflow-x: hidden; max-width: 100%; }
  * { box-sizing: border-box; }

  .al {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    overflow-x: hidden;
    background: #fff;
  }

  .al .al-container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 32px;
  }

  /* ---- HERO SECTION WITH FULL BACKGROUND ---- */
  .al .al-hero {
    position: relative;
    background: #ffffff url('{{ asset("images/adc-login/abhyam-center-hero.png") }}') no-repeat 42% center / cover;
    min-height: 620px;
    display: flex;
    align-items: center;
    overflow: hidden;
  }

  /* Overlay: Pure white ONLY behind far-left text (0-22%), fading completely to 0 before the computer desk (34-48%) */
  .al .al-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, 
      #ffffff 0%, 
      #ffffff 22%, 
      rgba(255, 255, 255, 0.85) 32%, 
      rgba(255, 255, 255, 0.25) 42%, 
      rgba(255, 255, 255, 0) 52%);
    z-index: 1;
  }

  .al .al-hero-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
    padding: 36px 32px;
    display: grid;
    grid-template-columns: 38% 62%;
    align-items: center;
    gap: 24px;
  }

  /* Hero Left Content - Clean narrow column so computer desk is uncovered in the center */
  .al .al-hero-left {
    background: transparent;
    padding: 0;
    border: none;
    box-shadow: none;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    max-width: 400px;
  }

  .al .al-brand-logo {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
  }

  .al .al-brand-icon {
    width: 44px;
    height: 44px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1d4ed8;
  }

  .al .al-brand-text {
    display: flex;
    flex-direction: column;
  }

  .al .al-brand-title {
    font-size: 20px;
    font-weight: 900;
    color: #0b192c;
    letter-spacing: -0.5px;
    line-height: 1.1;
  }

  .al .al-brand-sub {
    font-size: 10px;
    font-weight: 700;
    color: #1d4ed8;
    letter-spacing: 1.5px;
    text-transform: uppercase;
  }

  .al .al-hero-title {
    font-size: 40px;
    font-weight: 800;
    color: #0b192c;
    line-height: 1.2;
    margin: 0 0 14px;
    letter-spacing: -0.8px;
  }

  .al .al-hero-subtitle {
    font-size: 14.5px;
    color: #475569;
    line-height: 1.6;
    margin: 0 0 32px;
    max-width: 460px;
  }

  /* Two Feature Badges */
  .al .al-hero-features {
    display: flex;
    align-items: center;
    gap: 32px;
  }

  .al .al-feature-badge {
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .al .al-badge-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1d4ed8;
    flex-shrink: 0;
  }

  .al .al-badge-content h6 {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 2px;
  }

  .al .al-badge-content p {
    font-size: 11.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
  }

  /* Hero Right Form Floating Card */
  .al .al-hero-right {
    display: flex;
    justify-content: flex-end;
  }

  .al .al-form-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12), 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    width: 100%;
    max-width: 520px;
    overflow: hidden;
  }

  /* Tabs Header */
  .al .al-tabs-bar {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
  }

  .al .al-tab-btn {
    flex: 1;
    padding: 16px 12px;
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
  }

  .al .al-tab-btn.active {
    color: #1d4ed8;
    border-bottom-color: #1d4ed8;
    background: #ffffff;
  }

  .al .al-tab-btn:hover:not(.active) {
    color: #1e293b;
  }

  /* Form Body */
  .al .al-card-body {
    padding: 28px 32px 32px;
  }

  .al .al-form-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }

  .al .al-form-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .al .al-form-header h4 {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .al .al-form-header p {
    font-size: 12px;
    color: #64748b;
    margin: 2px 0 0;
  }

  /* Form Elements */
  .al .al-form-group {
    margin-bottom: 14px;
  }

  .al .al-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
  }

  .al .al-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    font-size: 13px;
    color: #0f172a;
    background: #ffffff;
    transition: all 0.2s;
    outline: none;
  }

  .al .al-input:focus {
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
  }

  .al .al-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .al .al-btn-primary {
    width: 100%;
    background: #1d4ed8;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    transition: background 0.2s, transform 0.1s;
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3);
  }

  .al .al-btn-primary:hover {
    background: #1e40af;
  }

  .al .al-footer-link {
    text-align: center;
    margin-top: 16px;
    font-size: 12.5px;
    color: #64748b;
  }

  .al .al-footer-link button, .al .al-footer-link a {
    background: none;
    border: none;
    color: #1d4ed8;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
  }

  /* ---- FEATURE STRIP FLOATING CARD ---- */
  .al .al-feature-bar {
    padding: 32px 0 16px;
    background: transparent;
  }

  .al .al-feature-card-wrapper {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    padding: 22px 28px;
  }

  .al .al-feature-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
  }

  .al .al-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 6px 16px;
  }

  .al .al-feature-item:not(:last-child) {
    border-right: 1px solid #f1f5f9;
  }

  .al .al-feature-icon {
    width: 44px;
    height: 44px;
    background: #eff6ff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    flex-shrink: 0;
  }

  .al .al-feature-content h6 {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 3px;
    line-height: 1.3;
  }

  .al .al-feature-content p {
    font-size: 11.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
  }

  /* ---- MANAGE YOUR ADC BANNER CARD ---- */
  .al .al-manage-banner-section {
    padding: 24px 0 48px;
  }

  .al .al-manage-banner {
    background: #f0f7ff;
    border-radius: 24px;
    border: 1px solid #e0eeff;
    overflow: hidden;
    display: grid;
    grid-template-columns: 42% 58%;
    align-items: center;
    min-height: 370px;
  }

  .al .al-manage-left {
    padding: 44px 44px;
  }

  .al .al-manage-title {
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    margin: 0 0 14px;
    letter-spacing: -0.5px;
  }

  .al .al-manage-sub {
    font-size: 14px;
    color: #475569;
    line-height: 1.6;
    margin: 0 0 28px;
  }

  .al .al-manage-checklist {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 24px;
  }

  .al .al-manage-check-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
  }

  .al .al-manage-check-icon {
    width: 20px;
    height: 20px;
    background: #2563eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    flex-shrink: 0;
  }

  .al .al-manage-right {
    position: relative;
    height: 100%;
    min-height: 370px;
    background: url('{{ asset("images/adc-login/adc-woman-desk.png") }}') no-repeat right center / cover;
  }

  .al .al-manage-right-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, 
      #f0f7ff 0%, 
      #f0f7ff 12%, 
      rgba(240, 247, 255, 0.82) 28%, 
      rgba(240, 247, 255, 0.3) 48%, 
      rgba(240, 247, 255, 0) 68%);
  }

  /* ---- SECTION STYLES ---- */
  .al-section { padding: 56px 0 64px; }
  .al-section-white { background: #ffffff; }
  .al-section-light { background: #f8fafc; }
  .al-section-blue  { background: #f0f7ff; }

  .al .al-section-title {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px;
    text-align: center;
    letter-spacing: -0.4px;
  }

  .al .al-accent-line {
    width: 44px;
    height: 3.5px;
    background: #1d4ed8;
    border-radius: 2px;
    margin: 0 auto 36px;
  }

  /* 5 Cards Grid */
  .al .al-cards-5 {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
  }

  .al .al-card-5 {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 18px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .al .al-card-5:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
  }

  .al .al-card-num {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .al .al-num-blue   { background: #1d4ed8; }
  .al .al-num-green  { background: #16a34a; }
  .al .al-num-purple { background: #9333ea; }
  .al .al-num-orange { background: #ea580c; }
  .al .al-num-cyan   { background: #0891b2; }

  .al .al-card-5-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 8px 0 16px;
  }

  .al .al-icon-bg-blue   { background: #eff6ff; color: #1d4ed8; }
  .al .al-icon-bg-green  { background: #f0fdf4; color: #16a34a; }
  .al .al-icon-bg-purple { background: #faf5ff; color: #9333ea; }
  .al .al-icon-bg-orange { background: #fff7ed; color: #ea580c; }
  .al .al-icon-bg-cyan   { background: #ecfeff; color: #0891b2; }

  .al .al-card-5 h5 {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px;
    line-height: 1.3;
  }

  .al .al-card-5 p {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    line-height: 1.55;
  }

  /* 4 Steps Section */
  .al .al-steps-container {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
    align-items: center;
    gap: 12px;
  }

  .al .al-step-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #475569;
    padding: 0 2px;
  }

  .al .al-step-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px 18px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .al .al-step-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
  }

  .al .al-step-card h5 {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px;
    line-height: 1.3;
  }

  .al .al-step-card p {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    line-height: 1.55;
  }

  /* ---- RESPONSIVE MEDIA QUERIES (TABLET & MOBILE) ---- */
  @media (max-width: 1200px) {
    .al .al-container { max-width: 100%; padding: 0 24px; }
    .al .al-hero-inner { max-width: 100%; padding: 32px 24px; }
  }

  @media (max-width: 992px) {
    .al .al-hero-inner { grid-template-columns: 1fr; gap: 32px; }
    .al .al-hero-left { max-width: 100%; text-align: center; margin: 0 auto; }
    .al .al-hero-subtitle { margin-left: auto; margin-right: auto; }
    .al .al-hero-features { justify-content: center; flex-wrap: wrap; }
    .al .al-hero-right { justify-content: center; }
    .al .al-manage-banner { grid-template-columns: 1fr; }
    .al .al-manage-right { min-height: 280px; }
    .al .al-manage-right-overlay {
      background: linear-gradient(180deg, 
        #f0f7ff 0%, 
        rgba(240, 247, 255, 0.7) 30%, 
        rgba(240, 247, 255, 0) 65%);
    }
    .al .al-cards-5 { grid-template-columns: repeat(3, 1fr); }
    .al .al-steps-container { grid-template-columns: 1fr 1fr; gap: 16px; }
    .al .al-step-arrow { display: none; }
  }

  @media (max-width: 768px) {
    .al .al-container { padding: 0 16px; }
    .al .al-hero-inner { padding: 24px 16px; }
    .al .al-hero-title { font-size: 28px; }
    .al .al-brand-title { font-size: 18px; }
    .al .al-hero-features { flex-direction: column; align-items: center; gap: 16px; }
    .al .al-feature-card-wrapper { padding: 16px; }
    .al .al-feature-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .al .al-feature-item { padding: 8px 4px; border-right: none !important; border-bottom: 1px solid #f1f5f9; }
    .al .al-feature-item:last-child { border-bottom: none; }
    .al .al-manage-left { padding: 28px 20px; }
    .al .al-manage-title { font-size: 24px; }
    .al .al-manage-checklist { grid-template-columns: 1fr; gap: 12px; }
    .al .al-manage-check-item { font-size: 13.5px; }
    .al .al-manage-right { min-height: 220px; }
    .al .al-cards-5 { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .al .al-card-5 { padding: 28px 14px 20px; }
    .al .al-grid-2 { grid-template-columns: 1fr; }
    .al .al-card-body { padding: 20px 16px; }
    .al .al-section-title { font-size: 22px; }
  }

  @media (max-width: 480px) {
    .al .al-feature-grid { grid-template-columns: 1fr; }
    .al .al-cards-5 { grid-template-columns: 1fr; }
    .al .al-steps-container { grid-template-columns: 1fr; gap: 14px; }
    .al .al-step-card { padding: 28px 16px 20px; }
    .al .al-step-arrow { display: flex; transform: rotate(90deg); margin: -4px 0; }
  }

  .tab-content.hidden { display: none !important; }
</style>

<div class="al">

  {{-- ======================== HERO SECTION ======================== --}}
  <section class="al-hero">
    <div class="al-hero-overlay"></div>

    <div class="al-hero-inner">

      {{-- LEFT CONTENT --}}
      <div class="al-hero-left">
        <div class="al-brand-logo">
          <div class="al-brand-icon">
            <svg width="36" height="36" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h14a1 1 0 001-1V10"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-6a1 1 0 011-1h4a1 1 0 011 1v6"/>
              <circle cx="12" cy="11" r="1.5" fill="#1d4ed8"/>
              <circle cx="9.5" cy="13" r="1" fill="#1d4ed8"/>
              <circle cx="14.5" cy="13" r="1" fill="#1d4ed8"/>
            </svg>
          </div>
          <div class="al-brand-text">
            <span class="al-brand-title">ABHYAM</span>
            <span class="al-brand-sub">DISTRICT CENTER</span>
          </div>
        </div>

        <h1 class="al-hero-title">Welcome to<br>Abhyam District Center</h1>
        <p class="al-hero-subtitle">
          Login or create your ADC account to access your dashboard and manage your operations.
        </p>

        <div class="al-hero-features">
          <div class="al-feature-badge">
            <div class="al-badge-icon">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="al-badge-content">
              <h6>Secure Access</h6>
              <p>Safe and secure login for your account</p>
            </div>
          </div>

          <div class="al-feature-badge">
            <div class="al-badge-icon">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <div class="al-badge-content">
              <h6>Dedicated Support</h6>
              <p>We are here to help you at every step</p>
            </div>
          </div>
        </div>
      </div>

      {{-- RIGHT FLOATING FORM CARD --}}
      <div class="al-hero-right">
        <div class="al-form-card">
          
          {{-- TABS --}}
          <div class="al-tabs-bar">
            <button id="btn-tab-login" class="al-tab-btn active">Login</button>
            <button id="btn-tab-register" class="al-tab-btn">Register</button>
          </div>

          <div class="al-card-body">

            {{-- ==================== SIGN IN SECTION ==================== --}}
            <div id="section-login" class="tab-content">
              <div class="al-form-header">
                <div class="al-form-header-icon">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                  </svg>
                </div>
                <div>
                  <h4>Sign In to ADC Portal</h4>
                  <p>Access your canteen dashboard & management tools.</p>
                </div>
              </div>

              @if(session('error'))
                <script>
                  document.addEventListener('DOMContentLoaded', function () {
                    Toastify({ text: "{{ session('error') }}", duration: 5000, close: true, gravity: "top", position: "right", style: { background: "#ef4444" } }).showToast();
                  });
                </script>
              @endif
              @if(session('success'))
                <script>
                  document.addEventListener('DOMContentLoaded', function () {
                    Toastify({ text: "{{ session('success') }}", duration: 5000, close: true, gravity: "top", position: "right", style: { background: "#10b981" } }).showToast();
                  });
                </script>
              @endif

              <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="al-form-group">
                  <label class="al-label" for="login_email">ADC Code <span style="color:#ef4444">*</span></label>
                  <input type="text" id="login_email" name="email" value="{{ old('email') }}" required class="al-input" placeholder="Enter your ADC Code (e.g. ADC001)" />
                </div>

                <div class="al-form-group">
                  <label class="al-label" for="login_password">Password <span style="color:#ef4444">*</span></label>
                  <input type="password" id="login_password" name="password" required class="al-input" placeholder="Enter secure password" />
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                  <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;color:#64748b;">
                    <input type="checkbox" name="remember" style="accent-color:#1d4ed8;"> Keep me authenticated
                  </label>
                </div>

                <button type="submit" id="btn-submit-login" class="al-btn-primary">
                  Sign In to Account
                </button>

                <div class="al-footer-link">
                  Don't have an account? <button type="button" onclick="document.getElementById('btn-tab-register').click()">Register Now</button>
                </div>
              </form>
            </div>

            {{-- ==================== REGISTER SECTION ==================== --}}
            <div id="section-register" class="tab-content hidden">
              <div class="al-form-header">
                <div class="al-form-header-icon">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                </div>
                <div>
                  <h4>Register as ADC</h4>
                  <p>Create your ADC account to get started.</p>
                </div>
              </div>

              <form id="adc-reg-form" action="{{ route('register.post') }}" method="POST">
                @csrf

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="adc-reg-type">ADC Type <span style="color:#ef4444">*</span></label>
                    <select id="adc-reg-type" name="type" required class="w-full select-simple">
                      <option value="">Select ADC Type</option>
                      <option value="ASC" {{ old('type') == 'ASC' ? 'selected' : '' }}>ASC – Ardh Sainik Canteen</option>
                      <option value="MVS" {{ old('type') == 'MVS' ? 'selected' : '' }}>MVS – Multi Variety Store</option>
                      <option value="SCS" {{ old('type') == 'SCS' ? 'selected' : '' }}>SCS – State Canteen Store</option>
                    </select>
                  </div>

                  <div class="al-form-group" id="adc-reg-code-container" style="{{ in_array(old('type'), ['ASC', 'SCS']) ? '' : 'display: none;' }}">
                    <label id="store-reg-code-label" class="al-label" for="adc-reg-code">Code <span style="color:#ef4444">*</span></label>
                    <select id="adc-reg-code" name="code" class="w-full select-searchable">
                      <option value="">Select Code</option>
                    </select>
                  </div>
                </div>

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="reg_name">Full Name <span style="color:#ef4444">*</span></label>
                    <input type="text" id="reg_name" name="name" value="{{ old('name') }}" required class="al-input" placeholder="Full Name" />
                  </div>
                  <div class="al-form-group">
                    <label class="al-label" for="reg_mobile">Mobile Number <span style="color:#ef4444">*</span></label>
                    <input type="text" id="reg_mobile" name="mobile" value="{{ old('mobile') }}" required maxlength="10"
                      pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                      class="al-input" placeholder="Mobile Number" />
                  </div>
                </div>

                <div class="al-form-group">
                  <label class="al-label" for="reg_email">Email Address <span style="color:#ef4444">*</span></label>
                  <input type="email" id="reg_email" name="email" value="{{ old('email') }}" required class="al-input" placeholder="Email Address" />
                </div>

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="company_name">Center / Business Name <span style="color:#ef4444">*</span></label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required class="al-input" placeholder="Center / Business Name" />
                  </div>
                  <div class="al-form-group">
                    <label class="al-label" for="gst_number">GST No <span style="color:#ef4444">*</span></label>
                    <input type="text" id="gst_number" name="gst_number" value="{{ old('gst_number') }}" required minlength="15" maxlength="15"
                      oninput="this.value = this.value.toUpperCase().slice(0, 15)"
                      class="al-input uppercase" placeholder="15-digit GSTIN" />
                  </div>
                </div>

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="adc-reg-state">State <span style="color:#ef4444">*</span></label>
                    <select id="adc-reg-state" name="state_id" required class="w-full select-searchable">
                      <option value="">Select State</option>
                    </select>
                  </div>
                  <div class="al-form-group">
                    <label class="al-label" for="adc-reg-district">District <span style="color:#ef4444">*</span></label>
                    <select id="adc-reg-district" name="district_id" required class="w-full select-searchable">
                      <option value="">Select District</option>
                    </select>
                  </div>
                </div>

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="reg_pincode">Pincode <span style="color:#ef4444">*</span></label>
                    <input type="text" id="reg_pincode" name="pincode" value="{{ old('pincode') }}" required maxlength="6"
                      pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                      class="al-input" placeholder="Pincode" />
                  </div>
                  <div class="al-form-group">
                    <label class="al-label" for="adc-reg-advisor-code">Advisor Code <span style="color:#ef4444">*</span></label>
                    <select id="adc-reg-advisor-code" name="advisor_code" required class="w-full select-searchable">
                      <option value="">Loading Advisor Codes...</option>
                    </select>
                  </div>
                </div>

                <div class="al-grid-2">
                  <div class="al-form-group">
                    <label class="al-label" for="reg_password">Password <span style="color:#ef4444">*</span></label>
                    <input type="password" id="reg_password" name="password" required class="al-input" placeholder="Password" />
                    <input type="hidden" name="password_confirmation" id="reg_password_confirmation" />
                  </div>
                  <div class="al-form-group">
                    <label class="al-label" for="reg_confirm_password_dummy">Confirm Password <span style="color:#ef4444">*</span></label>
                    <input type="password" id="reg_confirm_password_dummy" oninput="$('#reg_password_confirmation').val(this.value)" required class="al-input" placeholder="Confirm Password" />
                  </div>
                </div>

                <div class="al-form-group">
                  <label class="al-label" for="adc-reg-address">Address <span style="color:#ef4444">*</span></label>
                  <textarea id="adc-reg-address" name="address" required rows="2" class="al-input" style="resize:vertical;" placeholder="Full Address">{{ old('address') }}</textarea>
                </div>

                <button type="submit" id="btn-submit-adc-reg" class="al-btn-primary">
                  Create ADC Account
                </button>

                <div class="al-footer-link">
                  Already have an account? <button type="button" onclick="document.getElementById('btn-tab-login').click()">Login</button>
                </div>
              </form>
            </div>

          </div>{{-- end card body --}}
        </div>
      </div>

    </div>
  </section>
  {{-- ======================== END HERO ======================== --}}

  {{-- ======================== FEATURE STRIP FLOATING CARD ======================== --}}
  <div class="al-feature-bar">
    <div class="al-container">
      <div class="al-feature-card-wrapper">
        <div class="al-feature-grid">
          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>Manage Your ADC</h6>
              <p>Complete control of your center</p>
            </div>
          </div>

          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>Orders Management</h6>
              <p>Receive and manage orders easily</p>
            </div>
          </div>

          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>Inventory Management</h6>
              <p>Manage stock and products</p>
            </div>
          </div>

          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>Wallet & Transactions</h6>
              <p>Track wallet balance and transactions</p>
            </div>
          </div>

          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>Reports & Insights</h6>
              <p>Get important reports and insights</p>
            </div>
          </div>

          <div class="al-feature-item">
            <div class="al-feature-icon">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 00-9 9v7a2 2 0 002 2h3a1 1 0 001-1v-4a1 1 0 00-1-1H5v-3a7 7 0 0114 0v3h-3a1 1 0 00-1 1v4a1 1 0 001 1h3a2 2 0 002-2v-7a9 9 0 00-9-9z"/></svg>
            </div>
            <div class="al-feature-content">
              <h6>24/7 Support</h6>
              <p>Dedicated support whenever you need</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- ======================== END FEATURE STRIP ======================== --}}

  {{-- ======================== MANAGE YOUR ADC BANNER ======================== --}}
  <div class="al-manage-banner-section">
    <div class="al-container">
      <div class="al-manage-banner">
        <div class="al-manage-left">
          <h2 class="al-manage-title">Manage Your ADC<br>Operations in One Place</h2>
          <p class="al-manage-sub">Abhyam District Center provides a centralized platform to manage your day-to-day operations efficiently.</p>

          <div class="al-manage-checklist">
            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Manage ADC Profile</span>
            </div>
            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Track Transactions</span>
            </div>

            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Manage Products & Inventory</span>
            </div>
            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Manage Wallet</span>
            </div>

            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Manage Orders</span>
            </div>
            <div class="al-manage-check-item">
              <div class="al-manage-check-icon">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Access Important Reports</span>
            </div>
          </div>
        </div>

        <div class="al-manage-right">
          <div class="al-manage-right-overlay"></div>
        </div>
      </div>
    </div>
  {{-- ======================== END MANAGE YOUR ADC BANNER ======================== --}}

  {{-- ======================== 5 CARDS SECTION ======================== --}}
  <section class="al-section al-section-light">
    <div class="al-container">
      <h2 class="al-section-title">Everything You Need to Manage Your ADC</h2>
      <div class="al-accent-line"></div>

      <div class="al-cards-5">
        <div class="al-card-5">
          <span class="al-card-num al-num-blue">01</span>
          <div class="al-card-5-icon al-icon-bg-blue">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          </div>
          <h5>ADC Dashboard</h5>
          <p>Access important information and activities <strong>from a single dashboard.</strong></p>
        </div>

        <div class="al-card-5">
          <span class="al-card-num al-num-green">02</span>
          <div class="al-card-5-icon al-icon-bg-green">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <h5>Inventory Management</h5>
          <p>Manage and monitor your available stock <strong>and inventory.</strong></p>
        </div>

        <div class="al-card-5">
          <span class="al-card-num al-num-purple">03</span>
          <div class="al-card-5-icon al-icon-bg-purple">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <h5>Order Management</h5>
          <p>View, manage and process orders efficiently.</p>
        </div>

        <div class="al-card-5">
          <span class="al-card-num al-num-orange">04</span>
          <div class="al-card-5-icon al-icon-bg-orange">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3"/></svg>
          </div>
          <h5>Wallet &amp; Transactions</h5>
          <p>Track wallet balance and transaction history easily.</p>
        </div>

        <div class="al-card-5">
          <span class="al-card-num al-num-cyan">05</span>
          <div class="al-card-5-icon al-icon-bg-cyan">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <h5>Business Management</h5>
          <p>Manage your ADC profile and operational information.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ======================== 4 STEPS SECTION ======================== --}}
  <section class="al-section al-section-blue">
    <div class="al-container">
      <h2 class="al-section-title">Get Started in 4 Simple Steps</h2>
      <div class="al-accent-line"></div>

      <div class="al-steps-container">
        {{-- STEP 1 --}}
        <div class="al-step-card">
          <span class="al-card-num al-num-blue">01</span>
          <div class="al-card-5-icon al-icon-bg-blue">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          </div>
          <h5>Create Your Account</h5>
          <p>Enter your basic details and create your ADC account.</p>
        </div>

        {{-- ARROW 1 --}}
        <div class="al-step-arrow">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        {{-- STEP 2 --}}
        <div class="al-step-card">
          <span class="al-card-num al-num-green">02</span>
          <div class="al-card-5-icon al-icon-bg-green">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <h5>Submit Details</h5>
          <p>Provide the required ADC and business information.</p>
        </div>

        {{-- ARROW 2 --}}
        <div class="al-step-arrow">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        {{-- STEP 3 --}}
        <div class="al-step-card">
          <span class="al-card-num al-num-purple">03</span>
          <div class="al-card-5-icon al-icon-bg-purple">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <h5>Verification</h5>
          <p>Your submitted details are reviewed for verification.</p>
        </div>

        {{-- ARROW 3 --}}
        <div class="al-step-arrow">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        {{-- STEP 4 --}}
        <div class="al-step-card">
          <span class="al-card-num al-num-orange">04</span>
          <div class="al-card-5-icon al-icon-bg-orange">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <h5>Start Managing Your ADC</h5>
          <p>Once approved, access your ADC dashboard and start managing.</p>
        </div>
      </div>
    </div>
  </section>

</div>

@endsection


@push('styles')
  <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
  <style>
    .al .select2-container--default .select2-selection--single {
      height: 40px !important;
      border: 1px solid #cbd5e1 !important;
      border-radius: 10px !important;
      padding-left: 6px !important;
      display: flex !important;
      align-items: center !important;
      background-color: #ffffff !important;
    }
    .al .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #1d4ed8 !important;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12) !important;
    }
    .al .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 38px !important; right: 8px !important;
    }
    .al .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #0f172a !important; font-size: 13px !important; font-weight: 500 !important;
    }
    .select2-dropdown {
      border: 1px solid #cbd5e1 !important;
      border-radius: 10px !important;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,.1) !important;
      overflow: hidden !important;
      z-index: 9999 !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #1d4ed8 !important; color: white !important;
    }
    .select2-container--default .select2-results__option {
      padding: 8px 12px !important; font-size: 13px !important;
    }
  </style>
@endpush

@push('scripts')
  <script>
    jQuery(document).ready(function ($) {

      var apiStoreCodes = "{{ route('api.running-store-code', ['type' => '__TYPE__']) }}";
      var apiAmaCodes = "{{ route('api.ama-codes') }}";
      var apiStates = "{{ route('api.locations.states') }}";
      var apiDistricts = "{{ route('api.locations.districts') }}";

      // ── TAB SWITCHING ──
      var select2Ready = false;

      function switchTab(tab) {
        if (tab === 'register') {
          $('#btn-tab-register').addClass('active');
          $('#btn-tab-login').removeClass('active');
          $('#section-register').removeClass('hidden');
          $('#section-login').addClass('hidden');
          if (!select2Ready) {
            select2Ready = true;
            initRegisterDropdowns();
          }
        } else {
          $('#btn-tab-login').addClass('active');
          $('#btn-tab-register').removeClass('active');
          $('#section-login').removeClass('hidden');
          $('#section-register').addClass('hidden');
        }
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
      }

      $('#btn-tab-login').on('click', function () { switchTab('login'); });
      $('#btn-tab-register').on('click', function () { switchTab('register'); });

      var activeTab = new URLSearchParams(window.location.search).get('tab') || ("{{ ((isset($errors) && $errors->any()) || old('type') || old('name') || old('mobile')) ? 'register' : 'login' }}");
      switchTab(activeTab);

      // ── INIT DROPDOWNS ──
      function initRegisterDropdowns() {
        var $form = $('#section-register');

        $form.find('select').each(function () {
          try { if ($.fn.niceSelect) $(this).niceSelect('destroy'); } catch (e) { }
          $(this).next('.nice-select').remove();
          $(this).css({ display: 'block', visibility: 'visible' });
        });

        $form.find('select.select-simple').select2({
          minimumResultsForSearch: Infinity,
          width: '100%'
        });

        $form.find('select.select-searchable').select2({
          allowClear: false,
          width: '100%'
        });

        loadStates();
        loadAMACodes();

        var oldType = "{{ old('type') }}";
        if (oldType) {
          loadStoreCodes(oldType, "{{ old('code') }}" || null);
        } else {
          loadStoreCodes('', null);
        }
      }

      // ── ADC TYPE → STORE CODES ──
      function loadStoreCodes(type, selectedCode) {
        var $cs = $('#adc-reg-code'), $sl = $('#store-reg-code-label');
        if (!type || type === 'MVS') {
          $('#adc-reg-code-container').hide();
          $cs.prop('required', false).empty().append(new Option('Not Required', '', true, true));
          if ($cs.data('select2')) {
            $cs.val('').trigger('change');
          }
          return;
        }

        $('#adc-reg-code-container').show();
        $cs.prop('required', true);
        $cs.empty().append(new Option('Loading…', '', true, true));
        if ($cs.data('select2')) {
          $cs.trigger('change');
        }
        $sl.html((type || 'Store') + ' Code <span style="color:#ef4444">*</span>');

        fetch(apiStoreCodes.replace('__TYPE__', type))
          .then(function (r) { return r.json(); })
          .then(function (data) {
            $cs.empty().append(new Option('— Select ' + type + ' Code —', ''));
            if (Array.isArray(data)) {
              data.forEach(function (item) {
                if (item.id && item.id.toUpperCase() !== 'OTHER') {
                  var s = selectedCode && item.id == selectedCode;
                  $cs.append(new Option(item.name || item.id, item.id, s, s));
                }
              });
            }
            if (selectedCode) { $cs.val(selectedCode); }
            if ($cs.data('select2')) { $cs.select2('destroy'); }
            $cs.select2({ allowClear: false, width: '100%' });
            $cs.trigger('change');
          })
          .catch(function () {
            $cs.empty().append(new Option('Error loading codes', '', true, true));
            if ($cs.data('select2')) { $cs.trigger('change'); }
          });
      }

      $('#adc-reg-type').on('change', function () {
        loadStoreCodes($(this).val(), "{{ old('code') }}" || null);
      });

      // ── STATES ──
      function loadStates() {
        var $ss = $('#adc-reg-state');
        fetch(apiStates)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            $ss.empty().append(new Option('— Select State —', '', true, true));
            data.forEach(function (st) { $ss.append(new Option(st.name, st.id)); });
            $ss.trigger('change');
            var old = "{{ old('state_id') }}";
            if (old) { $ss.val(old).trigger('change'); }
          })
          .catch(function () { $ss.empty().append(new Option('Error loading states', '', true, true)).trigger('change'); });
      }

      // ── DISTRICTS ──
      function loadDistricts(stateId, selectedDistrictId) {
        var $ds = $('#adc-reg-district');
        $ds.empty().append(new Option('Loading…', '', true, true)).trigger('change');
        if (!stateId) {
          $ds.empty().append(new Option('— Select State First —', '', true, true)).trigger('change');
          return;
        }
        fetch(apiDistricts + '?state_id=' + stateId)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            $ds.empty().append(new Option('— Select District —', '', !selectedDistrictId, !selectedDistrictId));
            data.forEach(function (d) {
              var s = selectedDistrictId && d.id == selectedDistrictId;
              $ds.append(new Option(d.name, d.id, s, s));
            });
            if ($ds.data('select2')) { $ds.select2('destroy'); }
            $ds.select2({ allowClear: false, width: '100%' });
            $ds.trigger('change');
          })
          .catch(function () { $ds.empty().append(new Option('Error loading districts', '', true, true)).trigger('change'); });
      }

      $('#adc-reg-state').on('change', function () {
        loadDistricts($(this).val(), "{{ old('district_id') }}" || null);
      });

      // ── AMA / ADVISOR CODES ──
      function loadAMACodes() {
        var $as = $('#adc-reg-advisor-code');
        fetch(apiAmaCodes)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            $as.empty();
            var itemsList = [];
            if (data.status && Array.isArray(data.data)) {
              itemsList = data.data;
            } else if (Array.isArray(data)) {
              itemsList = data;
            }

            if (itemsList.length > 0) {
              itemsList.forEach(function (item, idx) {
                var code = item.code || item.name;
                var label = item.code ? (item.code + (item.name ? ' - ' + item.name : '')) : item.name;
                var isSelected = (idx === 0);
                $as.append(new Option(label, code, isSelected, isSelected));
              });
              var topCode = itemsList[0].code || itemsList[0].name;
              $as.val(topCode).trigger('change');
            } else {
              $as.append(new Option('No Advisor Codes Found', '', true, true)).trigger('change');
            }
          })
          .catch(function () {
            $as.empty().append(new Option('Failed to load Advisor Codes', '', true, true)).trigger('change');
          });
      }

      // ── FORM SUBMISSION ──
      $('#section-login form').on('submit', function (e) {
        var $btn = $('#btn-submit-login');
        if ($btn.length) {
          $btn.prop('disabled', true).css('opacity', '0.75').css('pointer-events', 'none');
          $btn.html('<svg class="animate-spin" style="width:18px;height:18px;display:inline-block;margin-right:8px;" viewBox="0 0 24 24" fill="none"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8z"/></svg> Signing In...');
        }
      });

      $('#adc-reg-form').on('submit', function (e) {
        var isValid = true, firstErr = null;
        $('#reg_password_confirmation').val($('#reg_password').val());
        function raiseError(field, msg) {
          isValid = false;
          Toastify({ text: msg, duration: 4000, gravity: 'top', position: 'right', style: { background: '#ef4444' } }).showToast();
          if (!firstErr) firstErr = field;
        }
        var currentType = $('#adc-reg-type').val();
        if (['ASC', 'SCS'].includes(currentType) && !$('#adc-reg-code').val())
          raiseError($('#adc-reg-code'), 'Please select a ' + currentType + ' Code.');
        if (!/^\d{10}$/.test($('#reg_mobile').val().trim()))
          raiseError($('#reg_mobile'), 'Mobile number must be exactly 10 numeric digits.');
        if (!/^\d{6}$/.test($('#reg_pincode').val().trim()))
          raiseError($('#reg_pincode'), 'Pincode must be exactly 6 numeric digits.');
        if ($('#reg_password').val().length < 8)
          raiseError($('#reg_password'), 'Password must be at least 8 characters long.');
        if (!isValid) {
          e.preventDefault();
          if (firstErr) firstErr.focus();
          return false;
        } else {
          var $btn = $('#btn-submit-adc-reg');
          if ($btn.length) {
            $btn.prop('disabled', true).css('opacity', '0.75').css('pointer-events', 'none');
            $btn.html('<svg class="animate-spin" style="width:18px;height:18px;display:inline-block;margin-right:8px;" viewBox="0 0 24 24" fill="none"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path style="opacity:.75" fill="white" d="M4 12a8 8 0 018-8v8z"/></svg> Registering...');
          }
        }
      });

    });
  </script>
@endpush
