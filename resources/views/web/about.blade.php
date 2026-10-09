@extends('web.layouts.app')

@section('title', 'About Us — Indian MSME B2B E-Commerce Platform')

@section('content')
  <style>
    /* ==========================================================
         ABOUT US PAGE - PIXEL-PERFECT CLONE & DESIGN SYSTEM
         ========================================================== */
    .ab-page {
      font-family: 'Poppins', sans-serif;
      color: #0f172a;
      background-color: #ffffff;
      overflow-x: hidden;
    }

    /* Container */
    .ab-container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ==========================================================
       1. HERO BANNER SECTION (EXACT REPLICA OF MOCKUP)
       ========================================================== */
    .ab-hero {
      background: #051429;
      position: relative;
      padding: 95px 0 85px;
      color: #ffffff;
      overflow: hidden;
      margin-bottom: 30px;
    }

    /* Top Center White Slanted Logo Banner */
    .ab-hero-logo-banner-wrap {
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      z-index: 35;
      width: 440px;
      max-width: 92%;
    }

    .ab-hero-logo-banner {
      background: #ffffff;
      padding: 14px 40px 16px;
      clip-path: polygon(0 0, 100% 0, 84% 100%, 16% 100%);
      border-radius: 0 0 20px 20px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .ab-hero-logo-text {
      font-size: 32px;
      font-weight: 900;
      line-height: 1;
      letter-spacing: -0.5px;
      display: flex;
      align-items: baseline;
      justify-content: center;
      gap: 4px;
    }

    .ab-hero-logo-text .logo-indian {
      color: #111827;
      font-weight: 800;
    }

    .ab-hero-logo-text .logo-msme {
      color: #ea580c;
      font-weight: 900;
    }

    .ab-hero-logo-text .logo-msme-blue {
      color: #1d4ed8;
      font-weight: 900;
    }

    .ab-hero-logo-text .logo-msme-green {
      color: #16a34a;
      font-weight: 900;
    }

    .ab-hero-logo-text .logo-org {
      font-size: 14px;
      font-weight: 800;
      color: #15803d;
      margin-left: 1px;
    }

    .ab-hero-logo-sub {
      font-size: 10.5px;
      font-weight: 800;
      color: #0b1f3a;
      letter-spacing: 2.2px;
      text-transform: uppercase;
      margin-top: 4px;
    }

    /* Faint India map outline background SVG on left */
    .ab-hero-map-bg {
      position: absolute;
      top: 5%;
      left: -2%;
      width: 52%;
      height: 90%;
      pointer-events: none;
      z-index: 1;
      opacity: 0.18;
    }

    /* Hero Grid Layout */
    .ab-hero-grid {
      display: grid;
      grid-template-columns: 44% 56%;
      gap: 28px;
      align-items: center;
      position: relative;
      z-index: 10;
    }

    .ab-hero-left {
      padding-right: 12px;
    }

    .ab-hero-tag {
      font-size: 13px;
      font-weight: 800;
      color: #f59e0b;
      letter-spacing: 2px;
      text-transform: uppercase;
      margin-bottom: 16px;
      display: block;
    }

    .ab-hero-left h1 {
      font-size: 42px;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.2;
      margin: 0 0 20px;
      letter-spacing: -0.5px;
    }

    .ab-hero-left h1 span {
      color: #f59e0b;
    }

    .ab-hero-left p {
      font-size: 15px;
      color: #cbd5e1;
      line-height: 1.7;
      margin: 0;
      max-width: 490px;
    }

    /* Hero Right 3-Image Graphic Block with Slanted White Borders */
    .ab-hero-right {
      position: relative;
      width: 100%;
      min-height: 380px;
    }

    .ab-hero-collage-wrap-3 {
      display: grid;
      grid-template-columns: 1fr 1.05fr;
      grid-template-rows: 175px 195px;
      gap: 14px;
      position: relative;
    }

    .ab-collage-card-3 {
      position: relative;
      background: #ffffff;
      padding: 4px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 16px 36px rgba(0, 0, 0, 0.4);
    }

    .ab-collage-card-3 img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      border-radius: 14px;
      transition: transform 0.4s ease;
    }

    .ab-collage-card-3:hover img {
      transform: scale(1.05);
    }

    /* Slanted Diagonal Polygons matching exact mockup */
    .ab-collage-card-3.card-top-left-3 {
      grid-column: 1 / 2;
      grid-row: 1 / 2;
      clip-path: polygon(22% 0, 100% 0, 78% 100%, 0 100%);
    }

    .ab-collage-card-3.card-bottom-left-3 {
      grid-column: 1 / 2;
      grid-row: 2 / 3;
      clip-path: polygon(0 0, 100% 0, 82% 100%, 0 100%);
    }

    .ab-collage-card-3.card-right-full-3 {
      grid-column: 2 / 3;
      grid-row: 1 / 3;
      clip-path: polygon(18% 0, 100% 0, 100% 100%, 0% 100%);
    }

    /* Center Shield Badge (Vocal for Local) */
    .ab-center-badge {
      position: absolute;
      top: 50%;
      left: 46%;
      transform: translate(-50%, -50%);
      background: #ffffff;
      border-radius: 28px;
      padding: 22px 28px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
      text-align: center;
      z-index: 25;
      min-width: 170px;
      border: 4px solid #ffffff;
    }

    .ab-vocal-orange {
      font-size: 24px;
      font-weight: 900;
      color: #ea580c;
      line-height: 1.1;
      display: block;
    }

    .ab-vocal-for {
      font-size: 15px;
      font-weight: 700;
      color: #1e3a8a;
      display: block;
      margin: 2px 0;
    }

    .ab-vocal-green {
      font-size: 24px;
      font-weight: 900;
      color: #16a34a;
      line-height: 1.1;
      display: block;
    }

    .ab-vocal-hindi {
      font-size: 13.5px;
      font-weight: 800;
      color: #0f172a;
      margin-top: 6px;
      display: block;
      letter-spacing: 0.3px;
    } }

    /* ==========================================================
         2. WHO WE ARE SECTION
         ========================================================== */
    .ab-who-section {
      padding: 85px 0 55px;
      background: #ffffff;
    }

    .ab-who-grid {
      display: grid;
      grid-template-columns: 46% 54%;
      gap: 52px;
      align-items: center;
    }

    /* Left Image Wrap */
    .ab-who-img-wrap {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
      border: 1px solid #e2e8f0;
      background: #f8fafc;
    }

    .ab-who-img-wrap img {
      width: 100%;
      height: 380px;
      object-fit: cover;
      display: block;
    }

    /* Floating Pill Badge on Team Image */
    .ab-who-badge {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: #ffffff;
      border-radius: 50px;
      padding: 10px 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.14);
      display: flex;
      align-items: center;
      gap: 12px;
      border: 1px solid #f1f5f9;
      z-index: 10;
    }

    .ab-badge-icon {
      width: 38px;
      height: 38px;
      background: #e0edff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2563eb;
      font-size: 20px;
      flex-shrink: 0;
    }

    .ab-badge-text {
      font-size: 13px;
      font-weight: 800;
      color: #1e3a8a;
      line-height: 1.35;
    }

    /* Right Content */
    .ab-who-right .ab-sub-label {
      font-size: 13px;
      font-weight: 800;
      color: #2563eb;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 10px;
      display: block;
    }

    .ab-who-right h2 {
      font-size: 34px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.3;
      margin: 0 0 20px;
      letter-spacing: -0.3px;
    }

    .ab-who-right p {
      font-size: 15px;
      color: #475569;
      line-height: 1.75;
      margin-bottom: 16px;
    }

    .ab-who-right p:last-child {
      margin-bottom: 0;
    }

    /* ==========================================================
       3. MISSION, VISION, VALUES (3 CARDS GRID - HORIZONTAL ROW)
       ========================================================== */
    .ab-mvv-section {
      padding: 45px 0 95px;
      background: #ffffff;
    }

    .ab-mvv-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .ab-mvv-card {
      border-radius: 20px !important;
      padding: 24px 24px !important;
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      gap: 20px !important;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .ab-mvv-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 32px rgba(0, 0, 0, 0.08);
    }

    /* Card 1: Mission (Blue) */
    .ab-mvv-card.card-mission {
      background: #edf4fe !important;
      border: 1px solid #dbeafe !important;
    }

    .ab-mvv-card.card-mission .ab-mvv-icon {
      background: #0033a0 !important;
      color: #ffffff !important;
    }

    .ab-mvv-card.card-mission h3 {
      color: #0033a0 !important;
    }

    /* Card 2: Vision (Orange) */
    .ab-mvv-card.card-vision {
      background: #fdf3e9 !important;
      border: 1px solid #ffedd5 !important;
    }

    .ab-mvv-card.card-vision .ab-mvv-icon {
      background: #e65100 !important;
      color: #ffffff !important;
    }

    .ab-mvv-card.card-vision h3 {
      color: #e65100 !important;
    }

    /* Card 3: Values (Green) */
    .ab-mvv-card.card-values {
      background: #f0f9f1 !important;
      border: 1px solid #dcfce7 !important;
    }

    .ab-mvv-card.card-values .ab-mvv-icon {
      background: #0d8a2e !important;
      color: #ffffff !important;
    }

    .ab-mvv-card.card-values h3 {
      color: #0d8a2e !important;
    }

    .ab-mvv-icon {
      width: 64px !important;
      height: 64px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 28px !important;
      margin-bottom: 0 !important;
      flex-shrink: 0 !important;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
    }

    .ab-mvv-content {
      flex: 1 !important;
    }

    .ab-mvv-content h3 {
      font-size: 19px !important;
      font-weight: 800 !important;
      margin: 0 0 4px 0 !important;
    }

    .ab-mvv-content p {
      font-size: 13.5px !important;
      color: #334155 !important;
      line-height: 1.55 !important;
      margin: 0 !important;
      font-weight: 500 !important;
    }

    /* ==========================================================
         4. WHY CHOOSE US - 6 BENEFIT CARDS
         ========================================================== */
    .ab-why-section {
      padding: 70px 0 90px;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
    }

    .ab-why-header {
      text-align: center;
      margin-bottom: 50px;
    }

    .ab-why-header .ab-sub-label {
      font-size: 13px;
      font-weight: 800;
      color: #2563eb;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 8px;
      display: block;
    }

    .ab-why-header h2 {
      font-size: 34px;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }

    .ab-why-header h2 span {
      color: #2563eb;
    }

    .ab-benefits-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 20px;
    }

    .ab-benefit-item {
      text-align: center;
      padding: 0 10px;
      position: relative;
    }

    .ab-benefit-item:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 15%;
      right: 0;
      height: 70%;
      width: 1px;
      background: #e2e8f0;
    }

    .ab-benefit-icon {
      width: 56px;
      height: 56px;
      background: #e0edff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      color: #2563eb;
      font-size: 26px;
      transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
    }

    .ab-benefit-item:hover .ab-benefit-icon {
      transform: scale(1.1);
      background: #2563eb;
      color: #ffffff;
    }

    .ab-benefit-item h4 {
      font-size: 15px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 8px;
      line-height: 1.3;
    }

    .ab-benefit-item p {
      font-size: 12.5px;
      color: #64748b;
      line-height: 1.6;
      margin: 0;
    }

    /* ==========================================================
         RESPONSIVE MEDIA QUERIES
         ========================================================== */
    @media (max-width: 1200px) {
      .ab-hero-grid {
        grid-template-columns: 1fr;
        gap: 40px;
      }

      .ab-hero-left {
        padding-right: 0;
        text-align: center;
      }

      .ab-hero-left p {
        margin-left: auto;
        margin-right: auto;
      }

      .ab-benefits-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 32px 20px;
      }

      .ab-benefit-item:not(:last-child)::after {
        display: none;
      }
    }

    @media (max-width: 992px) {
      .ab-who-grid {
        grid-template-columns: 1fr;
        gap: 36px;
      }

      .ab-who-right {
        text-align: center;
      }

      .ab-mvv-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }

    @media (max-width: 768px) {
      .ab-hero {
        padding: 70px 0 50px;
      }

      .ab-hero-left h1 {
        font-size: 32px;
      }

      .ab-hero-collage-wrap {
        gap: 10px;
      }

      .ab-collage-card img {
        height: 140px;
      }

      .ab-center-badge {
        padding: 14px 18px;
        min-width: 140px;
      }

      .ab-vocal-orange,
      .ab-vocal-green {
        font-size: 18px;
      }

      .ab-who-right h2,
      .ab-why-header h2 {
        font-size: 26px;
      }

      .ab-benefits-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .ab-mvv-card {
        padding: 18px 16px !important;
        gap: 14px !important;
      }

      .ab-mvv-icon {
        width: 50px !important;
        height: 50px !important;
        font-size: 22px !important;
      }

      .ab-mvv-content h3 {
        font-size: 17px !important;
      }

      .ab-mvv-content p {
        font-size: 12.5px !important;
      }
    }

    @media (max-width: 480px) {
      .ab-hero-left h1 {
        font-size: 26px;
      }

      .ab-benefits-grid {
        grid-template-columns: 1fr;
        gap: 28px;
      }
    }
  </style>

  <div class="ab-page">

    <!-- ==========================================================
           1. HERO BANNER SECTION WITH TOP CURVED LOGO TAB & COLLAGE
           ========================================================== -->
    <section class="ab-hero">

      <!-- Top Center White Slanted Logo Banner -->
      <div class="ab-hero-logo-banner-wrap">
        <div class="ab-hero-logo-banner">
          <div class="ab-hero-logo-text">
            <span class="logo-indian">indian</span>
            <span class="logo-msme">MSME</span>
            <span class="logo-org">.org<sup>™</sup></span>
          </div>
          <div class="ab-hero-logo-sub">B2B E-COMMERCE PLATFORM</div>
        </div>
      </div>

      <!-- Faint India Map SVG Background -->
      <svg class="ab-hero-map-bg" viewBox="0 0 500 500" fill="none" stroke="#60a5fa" stroke-width="1.5">
        <path
          d="M150,50 Q200,20 250,50 T350,60 T400,120 T380,200 T420,280 T350,380 T250,450 T180,380 T120,300 T80,200 T120,100 Z"
          stroke-dasharray="6 6" />
        <circle cx="250" cy="150" r="4" fill="#60a5fa" />
        <circle cx="300" cy="220" r="4" fill="#60a5fa" />
        <circle cx="200" cy="280" r="4" fill="#60a5fa" />
        <line x1="250" y1="150" x2="300" y2="220" stroke="#60a5fa" stroke-width="1" />
        <line x1="300" y1="220" x2="200" y2="280" stroke="#60a5fa" stroke-width="1" />
      </svg>

      <div class="ab-container">

        <div class="ab-hero-grid">

          <!-- Left Text Content -->
          <div class="ab-hero-left">
            <span class="ab-hero-tag">ABOUT US</span>
            <h1>Empowering Businesses.<br><span>Strengthening Bharat.</span></h1>
            <p>IndianMSME.org is a dedicated B2B e-commerce platform committed to the growth and empowerment of MSMEs
              across India.</p>
          </div>

          <!-- Right Image Collage & Vocal for Local Center Badge (EXACTLY 3 IMAGES) -->
          <div class="ab-hero-right">

            <div class="ab-hero-collage-wrap-3">
              <!-- Image 1: Top-Left Handshake -->
              <div class="ab-collage-card-3 card-top-left-3">
                <img src="{{ asset('images/about/about-handshake.png') }}" alt="Partnership & Trade">
              </div>

              <!-- Image 2: Bottom-Left Factory Machinery -->
              <div class="ab-collage-card-3 card-bottom-left-3">
                <img src="{{ asset('images/about/about-factory.png') }}" alt="Manufacturing & Industry">
              </div>

              <!-- Image 3: Right Full-Height Warehouse Seller -->
              <div class="ab-collage-card-3 card-right-full-3">
                <img src="{{ asset('images/about/about-warehouse-man.png') }}" alt="Empowered MSME Businessman" style="object-position: center top;">
              </div>

              <!-- Center Diamond Badge: Vocal for Local -->
              <div class="ab-center-badge">
                <span class="ab-vocal-orange">Vocal</span>
                <span class="ab-vocal-for">for</span>
                <span class="ab-vocal-green">Local</span>
                <span class="ab-vocal-hindi">आत्मनिर्भर भारत</span>
              </div>
            </div>

          </div>

        </div>

      </div>
    </section>

    <!-- ==========================================================
           2. WHO WE ARE SECTION
           ========================================================== -->
    <section class="ab-who-section">
      <div class="ab-container">

        <div class="ab-who-grid">

          <!-- Left Team Image -->
          <div class="ab-who-img-wrap">
            <img src="{{ asset('images/about/about-team-6.png') }}" alt="Indian MSME Professional Team">

            <!-- Floating Badge -->
            <div class="ab-who-badge">
              <div class="ab-badge-icon">
                <i class="hgi hgi-stroke hgi-user-group"></i>
              </div>
              <div class="ab-badge-text">
                Building a Stronger<br><span style="color: #2563eb;">MSME Ecosystem</span>
              </div>
            </div>
          </div>

          <!-- Right Text Content -->
          <div class="ab-who-right">
            <span class="ab-sub-label">WHO WE ARE</span>
            <h2>A Platform Built for <span style="color: #2563eb;">MSMEs</span>, by <span
                style="color: #ea580c;">Bharat</span>, for <span style="color: #16a34a;">Bharat</span>.</h2>

            <p>We are on a mission to create a unified, transparent, and technology-driven ecosystem where Distributors,
              Dealers, Retailers, Manufacturers, and Service Providers can connect, trade, and grow together.</p>

            <p>IndianMSME.org brings together the entire supply chain on one platform — enabling businesses to expand
              their reach, optimize operations, and unlock new opportunities.</p>
          </div>

        </div>

      </div>
    </section>

    <!-- ==========================================================
           3. MISSION, VISION, VALUES (3 CARDS)
           ========================================================== -->
    <section class="ab-mvv-section">
      <div class="ab-container">

        <div class="ab-mvv-grid">

          <!-- Mission Card -->
          <div class="ab-mvv-card card-mission">
            <div class="ab-mvv-icon">
              <i class="hgi hgi-stroke hgi-target-01"></i>
            </div>
            <div class="ab-mvv-content">
              <h3>Our Mission</h3>
              <p>To empower MSMEs by providing them with the right tools, connections, and support to grow, compete, and succeed globally.</p>
            </div>
          </div>

          <!-- Vision Card -->
          <div class="ab-mvv-card card-vision">
            <div class="ab-mvv-icon">
              <i class="hgi hgi-stroke hgi-eye"></i>
            </div>
            <div class="ab-mvv-content">
              <h3>Our Vision</h3>
              <p>To become India's most trusted B2B e-commerce platform and a driving force in building an <strong>Atmanirbhar Bharat.</strong></p>
            </div>
          </div>

          <!-- Values Card -->
          <div class="ab-mvv-card card-values">
            <div class="ab-mvv-icon">
              <i class="hgi hgi-stroke hgi-diamond-01"></i>
            </div>
            <div class="ab-mvv-content">
              <h3>Our Values</h3>
              <p>Trust, Transparency, Quality, Innovation, and Growth – these are the pillars that drive everything we do.</p>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ==========================================================
           4. WHY CHOOSE US (6 BENEFITS GRID)
           ========================================================== -->
    <section class="ab-why-section">
      <div class="ab-container">

        <div class="ab-why-header">
          <span class="ab-sub-label">WHY CHOOSE INDIANMSME.ORG?</span>
          <h2>Benefits That Drive <span>Real Growth</span></h2>
        </div>

        <div class="ab-benefits-grid">

          <!-- 1. Trusted Network -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-agreement-01"></i>
            </div>
            <h4>Trusted Network</h4>
            <p>Connect with verified businesses across India.</p>
          </div>

          <!-- 2. B2B Made Easy -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-shopping-cart-01"></i>
            </div>
            <h4>B2B Made Easy</h4>
            <p>Simplified buying, selling & order management.</p>
          </div>

          <!-- 3. Better Margins -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-coins-01"></i>
            </div>
            <h4>Better Margins</h4>
            <p>Competitive pricing and transparent deals.</p>
          </div>

          <!-- 4. Business Growth -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-analytics-01"></i>
            </div>
            <h4>Business Growth</h4>
            <p>Tools and support to scale your business.</p>
          </div>

          <!-- 5. Secure Platform -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-security-check"></i>
            </div>
            <h4>Secure Platform</h4>
            <p>Safe transactions and data security.</p>
          </div>

          <!-- 6. Dedicated Support -->
          <div class="ab-benefit-item">
            <div class="ab-benefit-icon">
              <i class="hgi hgi-stroke hgi-customer-service"></i>
            </div>
            <h4>Dedicated Support</h4>
            <p>We're here to support you at every step.</p>
          </div>

        </div>

      </div>
    </section>

  </div>
@endsection