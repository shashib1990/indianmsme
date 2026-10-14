@extends('web.layouts.app')

@section('title', 'Become a Seller — Indian MSME')

@section('content')
  <style>
    /* ==========================================================
     VENDOR ACCOUNT PAGE - PIXEL-PERFECT CLONE
     ========================================================== */

    html,
    body {
      overflow-x: hidden;
      max-width: 100%;
    }

    * {
      box-sizing: border-box;
    }

    .va {
      font-family: 'Poppins', sans-serif;
      overflow-x: hidden;
      background: #fff;
    }

    /* ---- CONTAINER ---- */
    .va .va-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* ---- HERO SECTION ---- */
    .va .va-hero {
      background: #f3f5f8;
      overflow: hidden;
      position: relative;
    }

    .va .va-hero-inner {
      display: grid;
      grid-template-columns: 54% 46%;
      align-items: stretch;
      min-height: 510px;
      max-width: 1920px;
      margin: 0 auto;
    }

    /* Hero Left */
    .va .va-hero-left {
      padding: 60px 48px 52px max(64px, calc((100vw - 1200px) / 2 + 24px));
      display: flex;
      flex-direction: column;
      justify-content: center;
      z-index: 2;
    }

    .va .va-hero-left h1 {
      font-size: 48px;
      font-weight: 800;
      color: #1e293b;
      line-height: 1.16;
      margin: 0 0 18px;
      letter-spacing: -0.5px;
    }

    .va .va-hero-left h1 span {
      color: #2a9f4e;
    }

    .va .va-hero-left>p {
      font-size: 14.5px;
      color: #475569;
      line-height: 1.7;
      margin: 0 0 30px;
      max-width: 480px;
    }

    .va .va-hero-btns {
      display: flex;
      gap: 14px;
      flex-wrap: nowrap;
      margin-bottom: 36px;
    }

    .va .va-btn-green {
      background: #2a9f4e;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 12px 26px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(42, 159, 78, 0.28);
      transition: background 0.2s, box-shadow 0.2s;
      white-space: nowrap;
    }

    .va .va-btn-green:hover {
      background: #228440;
      color: #fff;
      box-shadow: 0 6px 16px rgba(42, 159, 78, 0.38);
    }

    .va .va-btn-outline {
      background: #fff;
      color: #374151;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 12px 26px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      cursor: pointer;
      transition: background 0.2s;
      white-space: nowrap;
    }

    .va .va-btn-outline:hover {
      background: #f9fafb;
      color: #111;
    }

    /* Feature Pills Row under hero buttons - single row of 4 blue cards */
    .va .va-hero-pills {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      max-width: 560px;
    }

    .va .va-pill-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 14px 10px 12px;
      text-align: center;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
      border: 1px solid #f1f5f9;
    }

    .va .va-pill-icon {
      width: 42px;
      height: 42px;
      background: #e0edff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 8px;
      color: #2563eb;
    }

    .va .va-pill-card span {
      font-size: 11px;
      font-weight: 700;
      color: #0f172a;
      display: block;
      line-height: 1.35;
    }

    /* Hero Right Image - Slanted Tilted Cut Line (Line Tichi) with Drop Shadow */
    .va .va-hero-right-wrap {
      position: relative;
      width: 100%;
      height: 100%;
      filter: drop-shadow(-12px 0 20px rgba(0, 0, 0, 0.12));
    }

    .va .va-hero-right {
      position: relative;
      width: 100%;
      height: 100%;
      clip-path: polygon(14% 0, 100% 0, 100% 100%, 0% 100%);
      overflow: hidden;
    }

    .va .va-hero-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center top;
      display: block;
      min-height: 510px;
    }

    /* ---- SECTION HEADINGS ---- */
    .va .va-section {
      padding: 72px 0;
    }

    .va .va-section-light {
      background: #f8fafc;
    }

    .va .va-section-white {
      background: #fff;
    }

    .va .va-section-cream {
      background: #fffcf9;
    }

    .va .va-section-title {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 12px;
      line-height: 1.3;
    }

    .va .va-section-subtitle {
      font-size: 14px;
      color: #6b7280;
      margin: 0 0 40px;
      line-height: 1.7;
      max-width: 560px;
    }

    .va .va-accent {
      height: 3px;
      width: 44px;
      border-radius: 2px;
      margin-bottom: 24px;
    }

    .va .va-accent-green {
      background: #2a9f4e;
    }

    .va .va-accent-orange {
      background: #ff6b2c;
    }

    .va .va-accent-blue {
      background: #142548;
    }

    .va .va-center {
      text-align: center;
    }

    .va .va-center .va-accent {
      margin-left: auto;
      margin-right: auto;
    }

    .va .va-center .va-section-subtitle {
      margin-left: auto;
      margin-right: auto;
    }

    /* ---- FORM SECTION ---- */
    .va .va-form-container {
      max-width: 1340px;
      margin: 0 auto;
      padding: 0 32px;
    }

    .va .va-form-grid {
      display: grid;
      grid-template-columns: 48% 52%;
      gap: 52px;
      align-items: center;
    }

    .va .va-form-left-col {
      display: flex;
      flex-direction: column;
    }

    .va .va-form-left-col .va-section-title {
      font-size: 36px;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 12px;
    }

    .va .va-form-laptop-wrap {
      margin-top: 24px;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
      background: #ffffff;
    }

    .va .va-form-laptop-img {
      width: 100%;
      height: 440px;
      object-fit: cover;
      display: block;
    }

    /* Form card clean white card */
    .va .va-form-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 12px 36px rgba(0, 0, 0, 0.06);
      border: 1px solid #e2e8f0;
      position: relative;
    }

    .va .va-form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 16px;
    }

    .va .va-form-row.full {
      grid-template-columns: 1fr;
    }

    .va .va-form-group {
      display: flex;
      flex-direction: column;
    }

    .va .va-label {
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
    }

    .va .va-input {
      height: 46px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 0 14px;
      font-size: 14px;
      color: #1e293b;
      background: #fff;
      width: 100%;
      transition: border-color 0.2s, box-shadow 0.2s;
      font-family: 'Poppins', sans-serif;
    }

    .va .va-input:focus {
      border-color: #2a9f4e;
      outline: none;
      box-shadow: 0 0 0 3px rgba(42, 159, 78, 0.12);
    }

    .va .va-input::placeholder {
      color: #94a3b8;
      font-size: 13px;
    }

    .va .va-submit-btn {
      width: 100%;
      height: 48px;
      background: #2a9f4e;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
      box-shadow: 0 4px 12px rgba(42, 159, 78, 0.25);
      font-family: 'Poppins', sans-serif;
      transition: background 0.2s;
    }

    .va .va-submit-btn:hover {
      background: #228440;
    }

    /* ---- WHY SELL - 6 CARDS ---- */
    .va .va-cards-6 {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 18px;
    }

    .va .va-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 28px 18px 24px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border: 1px solid #f1f5f9;
      transition: box-shadow 0.2s, transform 0.2s;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    .va .va-card:hover {
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.09);
      transform: translateY(-3px);
    }

    .va .va-card-icon-green {
      width: 72px;
      height: 72px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      color: #2a9f4e;
    }

    .va .va-card-icon-orange {
      width: 44px;
      height: 44px;
      background: #fff3ec;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
      color: #ff6b2c;
    }

    .va .va-card-icon-blue {
      width: 50px;
      height: 50px;
      background: #edf4fe;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
      color: #2563eb;
    }

    .va .va-card-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 8px;
      text-align: center;
      line-height: 1.35;
    }

    .va .va-card-desc {
      font-size: 12px;
      color: #64748b;
      line-height: 1.6;
      text-align: center;
      margin: 0;
    }

    /* ---- EVERYTHING YOU NEED ---- */
    .va .va-two-col {
      display: grid;
      grid-template-columns: 46% 54%;
      gap: 56px;
      align-items: center;
    }

    .va .va-checklist {
      list-style: none;
      padding: 0;
      margin: 28px 0 0;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .va .va-checklist li {
      display: flex;
      align-items: center;
      gap: 14px;
      font-size: 15px;
      font-weight: 600;
      color: #1e293b;
    }

    .va .va-check-dot {
      width: 24px;
      height: 24px;
      background: #2a9f4e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      color: #fff;
    }

    /* Slanted cut photo with floating stats widget */
    .va .va-ey-right-wrap {
      position: relative;
      width: 100%;
      filter: drop-shadow(-12px 0 24px rgba(0, 0, 0, 0.12));
    }

    .va .va-ey-img-clipped {
      width: 100%;
      clip-path: polygon(14% 0, 100% 0, 100% 100%, 0% 100%);
      overflow: hidden;
      border-radius: 16px;
    }

    .va .va-ey-img-clipped img {
      width: 100%;
      height: 520px;
      object-fit: cover;
      display: block;
    }

    .va .va-ey-stats-card {
      position: absolute;
      top: 50%;
      right: 32px;
      transform: translateY(-50%);
      background: #ffffff;
      border-radius: 18px;
      padding: 26px 20px;
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
      border: 1px solid #f1f5f9;
      width: 230px;
      z-index: 10;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .va .va-ey-stat-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    .va .va-ey-icon-blue {
      width: 38px;
      height: 38px;
      background: #e0edff;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2563eb;
      flex-shrink: 0;
    }

    .va .va-ey-icon-orange {
      width: 38px;
      height: 38px;
      background: #fff3ec;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ff6b2c;
      flex-shrink: 0;
    }

    .va .va-ey-label {
      font-size: 10.5px;
      color: #64748b;
      display: block;
      margin-bottom: 2px;
      font-weight: 500;
    }

    .va .va-ey-val {
      font-size: 15px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.2;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .va .va-ey-badge-green {
      font-size: 9.5px;
      font-weight: 700;
      color: #16a34a;
      background: #dcfce7;
      padding: 1px 5px;
      border-radius: 4px;
    }

    .va .va-ey-sub {
      font-size: 10px;
      color: #94a3b8;
      display: block;
    }

    .va .va-ey-link {
      font-size: 10.5px;
      font-weight: 700;
      color: #ff6b2c;
      text-decoration: none;
      display: block;
    }

    .va .va-ey-divider {
      height: 1px;
      background: #f1f5f9;
      width: 100%;
    }

    /* ---- BUILT TO HELP - 5 CARDS ---- */
    .va-section-cream {
      background: #fffcf8;
    }

    .va .va-cards-5 {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 18px;
      margin-top: 16px;
    }

    .va .va-card-5-item {
      background: #ffffff;
      border-radius: 16px;
      padding: 32px 18px 24px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border: 1px solid #f1f5f9;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .va .va-card-5-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.09);
    }

    .va .va-badge-orange {
      position: absolute;
      top: -14px;
      left: 50%;
      transform: translateX(-50%);
      width: 28px;
      height: 28px;
      background: #ff6b2c;
      border-radius: 50%;
      color: #fff;
      font-size: 11px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 3px 10px rgba(255, 107, 44, 0.3);
    }

    .va .va-card-icon-orange {
      width: 64px;
      height: 64px;
      background: #fff3ec;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 12px auto 16px;
      color: #ff6b2c;
    }

    .va .va-card-5-title {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 6px;
      text-align: center;
    }

    .va .va-card-5-accent {
      width: 20px;
      height: 2px;
      background: #2a9f4e;
      border-radius: 1px;
      margin: 0 auto 10px;
    }

    .va .va-card-5-desc {
      font-size: 12px;
      color: #64748b;
      line-height: 1.6;
      text-align: center;
      margin: 0;
    }

    /* ---- 4-STEP CARDS ---- */
    .va-section-blue {
      background: #f0f6ff;
    }

    .va .va-cards-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-top: 16px;
      position: relative;
    }

    .va .va-step-card-item {
      background: #ffffff;
      border-radius: 16px;
      padding: 36px 22px 28px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.06);
      border: 1px solid #e2e8f0;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .va .va-step-card-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .va .va-badge-dark {
      position: absolute;
      top: -16px;
      left: 50%;
      transform: translateX(-50%);
      width: 32px;
      height: 32px;
      background: #142548;
      border-radius: 50%;
      color: #fff;
      font-size: 14px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(20, 37, 72, 0.3);
    }

    .va .va-card-icon-blue {
      width: 72px;
      height: 72px;
      background: #e8f2fe;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 12px auto 18px;
      color: #2563eb;
    }

    .va .va-step-title {
      font-size: 15px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 8px;
      text-align: center;
    }

    .va .va-step-desc {
      font-size: 12.5px;
      color: #64748b;
      line-height: 1.6;
      text-align: center;
      margin: 0;
    }

    .va .va-step-card-item:not(:last-child)::after {
      content: '';
      position: absolute;
      top: 50%;
      right: -18px;
      transform: translateY(-50%);
      width: 14px;
      height: 0;
      border-top: 2px dashed #2563eb;
      z-index: 10;
    }

    .va .va-step-card-item:not(:last-child)::before {
      content: '›';
      position: absolute;
      top: 50%;
      right: -24px;
      transform: translateY(-50%);
      font-size: 18px;
      font-weight: 900;
      color: #2563eb;
      z-index: 10;
      line-height: 1;
    }

    /* ---- CTA BANNER (FULL WIDTH 100%, NO BORDER RADIUS) ---- */
    .va .va-cta-section-full {
      background: #091326;
      width: 100%;
      padding: 60px 0;
      border-radius: 0 !important;
    }

    .va .va-cta-banner-content {
      display: grid;
      grid-template-columns: 44% 56%;
      gap: 36px;
      align-items: center;
    }

    .va .va-cta-section-full h2 {
      font-size: 36px;
      font-weight: 800;
      color: #fff;
      line-height: 1.25;
      margin: 0 0 12px;
    }

    .va .va-cta-section-full p {
      font-size: 14.5px;
      color: #94a3b8;
      margin: 0 0 28px;
      line-height: 1.7;
    }

    .va .va-cta-btns {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    .va .va-btn-dark-secondary {
      background: transparent;
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 8px;
      padding: 13px 24px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      cursor: pointer;
      transition: background 0.2s, border-color 0.2s;
      white-space: nowrap;
    }

    .va .va-btn-dark-secondary:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(255, 255, 255, 0.4);
      color: #fff;
    }

    /* Single CTA image wrap for team-sellers.jpg */
    .va .va-cta-img-wrap {
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .va .va-cta-img-wrap img {
      width: 100%;
      height: 350px;
      object-fit: cover;
      display: block;
    }

    /* ---- TRUST BAR ---- */
    .va .va-trust-bar {
      padding: 40px 0;
      background: #ffffff;
      border-top: 1px solid #e2e8f0;
    }

    .va .va-trust-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 28px;
    }

    .va .va-trust-item {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 0;
      background: transparent;
      box-shadow: none;
    }

    .va .va-trust-icon {
      width: 52px;
      height: 52px;
      background: #e0edff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2563eb;
      flex-shrink: 0;
    }

    .va .va-trust-item h5 {
      font-size: 14.5px;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 3px;
    }

    .va .va-trust-item p {
      font-size: 12px;
      color: #64748b;
      margin: 0;
      line-height: 1.5;
    }

    /* Select2 overrides inside va */
    .va .select2-container--default .select2-selection--single {
      height: 46px !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      display: flex !important;
      align-items: center !important;
      background: #fff !important;
    }

    .va .select2-container--default .select2-selection--single .select2-selection__rendered {
      font-size: 14px !important;
      color: #1e293b !important;
      font-weight: 400 !important;
    }

    .va .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 44px !important;
    }

    .va .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #2a9f4e !important;
      box-shadow: 0 0 0 3px rgba(42, 159, 78, 0.12) !important;
    }

    .va .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #2a9f4e !important;
    }

    /* ==========================================================================
       RESPONSIVE MEDIA QUERIES (MOBILE & TABLET SUPPORT)
       ========================================================================== */

    /* -- Large tablets / small desktops (max 1200px) -- */
    @media (max-width: 1200px) {
      .va .va-hero-inner {
        grid-template-columns: 55% 45%;
      }
      .va .va-hero-left {
        padding: 50px 32px 44px 32px;
      }
      .va .va-cards-6 {
        grid-template-columns: repeat(3, 1fr);
      }
      .va .va-cards-5 {
        grid-template-columns: repeat(3, 1fr);
      }
      .va .va-form-container {
        padding-left: 32px;
        padding-right: 32px;
      }
    }

    /* -- Tablets (max 992px) -- */
    @media (max-width: 992px) {
      .va .va-hero-inner {
        grid-template-columns: 1fr;
        min-height: unset;
      }
      .va .va-hero-left {
        padding: 52px 24px 44px 24px;
      }
      .va .va-hero-left h1 {
        font-size: 36px;
      }
      .va .va-hero-right-wrap {
        display: none;
      }
      .va .va-form-grid {
        grid-template-columns: 1fr;
        gap: 28px;
      }
      .va .va-form-left-col {
        display: none;
      }
      .va .va-form-card {
        padding: 32px 24px;
      }
      .va .va-two-col {
        grid-template-columns: 1fr;
        gap: 32px;
      }
      .va .va-ey-img-clipped {
        clip-path: none;
        border-radius: 16px;
      }
      .va .va-ey-img-clipped img {
        height: 300px;
      }
      .va .va-ey-right-wrap {
        display: flex;
        flex-direction: column;
      }
      .va .va-ey-stats-card {
        position: relative;
        top: 0;
        right: 0;
        transform: none;
        width: 100%;
        margin-top: 20px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
      }
      .va .va-cards-6 {
        grid-template-columns: repeat(3, 1fr);
      }
      .va .va-cards-5 {
        grid-template-columns: repeat(3, 1fr);
      }
      .va .va-cards-4 {
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
      }
      .va .va-step-card-item:not(:last-child)::after,
      .va .va-step-card-item:not(:last-child)::before {
        display: none;
      }
      .va .va-cta-banner-content {
        grid-template-columns: 1fr;
        gap: 32px;
      }
      .va .va-cta-img-wrap img {
        height: 280px;
      }
      .va .va-cta-section-full {
        padding: 48px 0;
      }
      .va .va-trust-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
      }
    }

    /* -- Mobile (max 768px) -- */
    @media (max-width: 768px) {
      .va-section {
        padding: 44px 0;
      }
      .va .va-form-container {
        padding-left: 16px;
        padding-right: 16px;
      }
      .va .va-hero-left {
        padding: 40px 16px 36px 16px;
      }
      .va .va-hero-left h1 {
        font-size: 28px;
        line-height: 1.25;
      }
      .va .va-hero-left > p {
        font-size: 14px;
      }
      .va .va-hero-pills {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        max-width: 100%;
      }
      .va .va-section-title {
        font-size: 22px;
      }
      .va .va-section-subtitle {
        font-size: 14px;
      }
      .va .va-cards-6 {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
      }
      .va .va-cards-5 {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
      }
      .va .va-form-row {
        grid-template-columns: 1fr;
        gap: 14px;
      }
      .va .va-cta-section-full h2 {
        font-size: 26px;
      }
    }

    /* -- Small mobile (max 480px) -- */
    @media (max-width: 480px) {
      .va .va-form-container {
        padding-left: 14px;
        padding-right: 14px;
      }
      .va .va-hero-btns,
      .va .va-cta-btns {
        flex-direction: column;
        gap: 12px;
        width: 100%;
      }
      .va .va-hero-btns a,
      .va .va-cta-btns a {
        width: 100%;
        text-align: center;
        justify-content: center;
      }
      .va .va-cards-6 {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }
      .va .va-cards-5 {
        grid-template-columns: 1fr;
        gap: 24px;
      }
      .va .va-cards-4 {
        grid-template-columns: 1fr;
        gap: 28px;
      }
      .va .va-trust-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .va .va-hero-pills {
        grid-template-columns: repeat(2, 1fr);
      }
      .va .va-pill-card {
        padding: 10px 8px;
      }
      .va .va-cta-section-full h2 {
        font-size: 22px;
      }
      .va .va-cta-img-wrap img {
        height: 220px;
      }
    }

    html {
      scroll-behavior: smooth;
    }
  </style>

  <div class="va">

    {{-- ======================== HERO ======================== --}}
    <section class="va-hero">
      <div class="va-hero-inner">

        {{-- LEFT --}}
        <div class="va-hero-left">
          <h1>Grow Your Business<br>with <span>Indian MSME</span></h1>
          <p>Join India's growing marketplace for MSMEs, manufacturers, brands and sellers. Showcase your products, reach
            new customers and grow your business with powerful selling tools.</p>

          <div class="va-hero-btns">
            <a href="#register-form" class="va-btn-green">Become a Seller</a>
            <a href="{{ route('vendor.login') }}" class="va-btn-outline">Already a Seller? Login</a>
          </div>

          <div class="va-hero-pills">
            <div class="va-pill-card">
              <div class="va-pill-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <span>Built for<br>MSMEs</span>
            </div>
            <div class="va-pill-card">
              <div class="va-pill-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <span>Secure<br>&amp; Trusted</span>
            </div>
            <div class="va-pill-card">
              <div class="va-pill-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <span>Grow Your<br>Business</span>
            </div>
            <div class="va-pill-card">
              <div class="va-pill-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <span>Dedicated<br>Support</span>
            </div>
          </div>
        </div>

        {{-- RIGHT - image with slanted line tichi cut and drop shadow --}}
        <div class="va-hero-right-wrap">
          <div class="va-hero-right">
            <img src="{{ asset('images/vendor-account/hero-seller.jpg') }}" alt="Grow your business with Indian MSME"
              class="va-hero-img">
          </div>
        </div>

      </div>
    </section>

    {{-- ======================== FORM SECTION ======================== --}}
    <section class="va-section" id="register-form" style="background: #f8fafc; scroll-margin-top: 20px; padding: 72px 0;">
      <div class="va-form-container">

        <div class="va-form-grid">

          {{-- LEFT COLUMN: Title + Subtitle + Laptop Illustration --}}
          <div class="va-form-left-col">
            <h2 class="va-section-title">Become a Seller</h2>
            <p class="va-section-subtitle" style="margin-bottom: 12px;">Create your seller account and start your journey
              with Indian MSME.</p>
            <div class="va-accent va-accent-green" style="margin-bottom: 24px;"></div>

            <div class="va-form-laptop-wrap">
              <img src="{{ asset('images/vendor-account/laptop-store.jpg') }}" alt="Become a Seller"
                class="va-form-laptop-img">
            </div>
          </div>

          {{-- FORM CARD --}}
          <div class="va-form-card">
            @php
              $categories = \App\Models\Category::active()->orderBy('name')->get();
              $states = \App\Models\State::active()->orderBy('name')->get();
            @endphp

            @if($errors->any())
              <script>document.addEventListener('DOMContentLoaded', function () { @foreach($errors->all() as $error) Toastify({ text: "{{ $error }}", duration: 5000, close: true, gravity: "top", position: "right", style: { background: "#ef4444" } }).showToast(); @endforeach }); </script>
            @endif
            @if(session('success'))
              <script>document.addEventListener('DOMContentLoaded', function () { Toastify({ text: "{{ session('success') }}", duration: 5000, close: true, gravity: "top", position: "right", style: { background: "#10b981" } }).showToast(); }); </script>
            @endif

            <form action="{{ route('vendor.register.post') }}" method="POST">
              @csrf

              <div class="va-form-row">
                <div class="va-form-group">
                  <label class="va-label">Company Name <span style="color:#ef4444">*</span></label>
                  <input type="text" name="company_name" class="va-input" placeholder="e.g. Acme Corporation"
                    value="{{ old('company_name') }}" required>
                </div>
                <div class="va-form-group">
                  <label class="va-label">Company Type <span style="color:#ef4444">*</span></label>
                  <select name="company_type" class="va-input va-select-simple" required>
                    <option value="">— Select Type —</option>
                    <option value="Company" {{ old('company_type') == 'Company' ? 'selected' : '' }}>Company</option>
                    <option value="LLP" {{ old('company_type') == 'LLP' ? 'selected' : '' }}>LLP</option>
                    <option value="Proprietorship Firm" {{ old('company_type') == 'Proprietorship Firm' ? 'selected' : '' }}>
                      Proprietorship Firm</option>
                    <option value="Other" {{ old('company_type') == 'Other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
              </div>

              <div class="va-form-row">
                <div class="va-form-group">
                  <label class="va-label">Primary Category <span style="color:#ef4444">*</span></label>
                  <select name="category_id" class="va-input va-select-simple" required>
                    <option value="">— Select Category —</option>
                    @foreach($categories as $cat)
                      <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="va-form-group">
                  <label class="va-label">Seller Type <span style="color:#ef4444">*</span></label>
                  <select name="seller_type" id="seller_type" class="va-input va-select-simple" required>
                    <option value="">— Select Seller Type —</option>
                    <option value="Trader" {{ old('seller_type') == 'Trader' ? 'selected' : '' }}>Trader</option>
                    <option value="Manufacturer" {{ old('seller_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer
                    </option>
                    <option value="Distributor" {{ old('seller_type') == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                  </select>
                </div>
              </div>

              <div class="va-form-row full" id="brand-container" style="display:none;">
                <div class="va-form-group">
                  <label class="va-label">Brand Name <span style="color:#ef4444">*</span></label>
                  <input type="text" name="brand_name" id="brand_name" class="va-input" placeholder="e.g. Acme Brand"
                    value="{{ old('brand_name') }}">
                  <span style="font-size:12px;color:#6b7280;margin-top:4px;">Required for manufacturers.</span>
                </div>
              </div>

              <div class="va-form-row">
                <div class="va-form-group">
                  <label class="va-label">Authorized Representative <span style="color:#ef4444">*</span></label>
                  <input type="text" name="authorized_rep_name" class="va-input" placeholder="Full Name"
                    value="{{ old('authorized_rep_name') }}" required>
                </div>
                <div class="va-form-group">
                  <label class="va-label">Email Address <span style="color:#ef4444">*</span></label>
                  <input type="email" name="email" class="va-input" placeholder="vendor@domain.com"
                    value="{{ old('email') }}" required>
                </div>
              </div>

              <div class="va-form-row">
                <div class="va-form-group">
                  <label class="va-label">Mobile Number <span style="color:#ef4444">*</span></label>
                  <input type="text" id="mobile" name="mobile" class="va-input" placeholder="10-digit number"
                    value="{{ old('mobile') }}" required maxlength="10" pattern="[0-9]{10}"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <div class="va-form-group">
                  <label class="va-label">State <span style="color:#ef4444">*</span></label>
                  <select name="state" class="va-input va-select-state" required>
                    <option value="">— Select State —</option>
                    @foreach($states as $st)
                      <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="va-form-row">
                <div class="va-form-group">
                  <label class="va-label">District <span style="color:#ef4444">*</span></label>
                  <select name="district" class="va-input va-select-district" required>
                    <option value="">— Select State First —</option>
                  </select>
                </div>
                <div class="va-form-group">
                  <label class="va-label">Password <span style="color:#ef4444">*</span></label>
                  <input type="password" name="password" class="va-input" placeholder="At least 8 characters" required>
                </div>
              </div>

              <div class="va-form-row full">
                <div class="va-form-group">
                  <label class="va-label">Confirm Password <span style="color:#ef4444">*</span></label>
                  <input type="password" name="password_confirmation" class="va-input" placeholder="Repeat password"
                    required>
                </div>
              </div>

              <button type="submit" id="btn-submit-vendor-reg" class="va-submit-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Create Seller Account
              </button>
            </form>

            <!-- Seller Login Option -->
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;">
              <p style="font-size: 14px; color: #475569; margin-bottom: 12px; font-weight: 500;">
                Already registered as a Seller?
              </p>
              <a href="{{ route('vendor.login') }}" 
                 style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 46px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; font-weight: 700; color: #0f172a; text-decoration: none; transition: all 0.2s;"
                 onmouseover="this.style.background='#e2e8f0'; this.style.borderColor='#94a3b8';"
                 onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';">
                <svg width="18" height="18" fill="none" stroke="#2a9f4e" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span>Log In to Seller Portal</span>
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>

    {{-- ======================== WHY SELL - 6 CARDS ======================== --}}
    <section class="va-section va-section-light">
      <div class="va-form-container">
        <div class="va-center">
          <h2 class="va-section-title">Why Sell on Indian MSME?</h2>
          <div class="va-accent va-accent-green"></div>
        </div>

        <div class="va-cards-6">
          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <p class="va-card-title">Reach More Customers</p>
            <p class="va-card-desc">Showcase your products to customers across India.</p>
          </div>

          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0H9m3 0h3" />
              </svg>
            </div>
            <p class="va-card-title">Built for MSMEs</p>
            <p class="va-card-desc">A marketplace designed to help Indian businesses grow.</p>
          </div>

          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <p class="va-card-title">Easy Product Management</p>
            <p class="va-card-desc">Add, update and manage your products from one seller dashboard.</p>
          </div>

          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 022 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <p class="va-card-title">Order Management</p>
            <p class="va-card-desc">Manage orders, dispatches, invoices and fulfillment.</p>
          </div>

          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <p class="va-card-title">Secure Payments</p>
            <p class="va-card-desc">Track your sales, payments and transactions securely.</p>
          </div>

          <div class="va-card">
            <div class="va-card-icon-green">
              <svg width="60" height="60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <p class="va-card-title">Dedicated Seller Support</p>
            <p class="va-card-desc">Get assistance whenever you need help with your account.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ======================== EVERYTHING YOU NEED ======================== --}}
    <section class="va-section va-section-white">
      <div class="va-form-container">
        <div class="va-two-col">

          <div>
            <h2 class="va-section-title">Everything You Need<br>to Sell Online</h2>
            <div class="va-accent va-accent-green"></div>
            <p class="va-section-subtitle" style="margin-bottom: 0;">From product listing to order management, Indian MSME
              gives sellers the tools they need to manage their online business efficiently.</p>

            <ul class="va-checklist">
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Product &amp; inventory management</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Order management</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Seller dashboard</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Sales &amp; order tracking</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Pickup &amp; dispatch management</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Payment and transaction tracking</li>
              <li><span class="va-check-dot"><svg width="12" height="12" fill="none" stroke="currentColor"
                    stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg></span>Customer reach across India</li>
            </ul>
          </div>

          {{-- RIGHT: Slanted photo cut + floating stats card widget --}}
          <div class="va-ey-right-wrap">
            <div class="va-ey-img-clipped">
              <img src="{{ asset('images/vendor-account/seller-laptop-stats.jpg') }}" alt="Dashboard Stats">
            </div>

            {{-- FLOATING STATS WIDGET CARD --}}
            <div class="va-ey-stats-card">
              <div class="va-ey-stat-item">
                <div class="va-ey-icon-blue">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                  </svg>
                </div>
                <div>
                  <span class="va-ey-label">Total Orders</span>
                  <div class="va-ey-val">128 <span class="va-ey-badge-green">+18%</span></div>
                  <span class="va-ey-sub">This Month</span>
                </div>
              </div>

              <div class="va-ey-divider"></div>

              <div class="va-ey-stat-item">
                <div class="va-ey-icon-blue">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <div>
                  <span class="va-ey-label">Total Sales</span>
                  <div class="va-ey-val">₹2,45,680 <span class="va-ey-badge-green">+22%</span></div>
                  <span class="va-ey-sub">This Month</span>
                </div>
              </div>

              <div class="va-ey-divider"></div>

              <div class="va-ey-stat-item">
                <div class="va-ey-icon-orange">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <div>
                  <span class="va-ey-label">Pending Orders</span>
                  <div class="va-ey-val">16</div>
                  <a href="#register-form" class="va-ey-link">View All Orders</a>
                </div>
              </div>

              <div class="va-ey-divider"></div>

              <div class="va-ey-stat-item">
                <div class="va-ey-icon-orange">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                </div>
                <div>
                  <span class="va-ey-label">Products</span>
                  <div class="va-ey-val">254</div>
                  <a href="#register-form" class="va-ey-link">Manage Products</a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    {{-- ======================== BUILT TO HELP - 5 CARDS ======================== --}}
    <section class="va-section va-section-cream">
      <div class="va-form-container">
        <div class="va-center">
          <h2 class="va-section-title">Built to Help Your Business Grow</h2>
          <div class="va-accent va-accent-orange"></div>
        </div>

        <div class="va-cards-5">
          <div class="va-card-5-item">
            <span class="va-badge-orange">01</span>
            <div class="va-card-icon-orange">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <p class="va-card-5-title">Showcase Your Products</p>
            <div class="va-card-5-accent"></div>
            <p class="va-card-5-desc">Create product listings and showcase your products to potential buyers.</p>
          </div>

          <div class="va-card-5-item">
            <span class="va-badge-orange">02</span>
            <div class="va-card-icon-orange">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </div>
            <p class="va-card-5-title">Manage Your Business</p>
            <div class="va-card-5-accent"></div>
            <p class="va-card-5-desc">Manage products, inventory, orders and seller information from your dashboard.</p>
          </div>

          <div class="va-card-5-item">
            <span class="va-badge-orange">03</span>
            <div class="va-card-icon-orange">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
              </svg>
            </div>
            <p class="va-card-5-title">Receive Orders</p>
            <div class="va-card-5-accent"></div>
            <p class="va-card-5-desc">Get customer orders and manage the complete fulfillment process.</p>
          </div>

          <div class="va-card-5-item">
            <span class="va-badge-orange">04</span>
            <div class="va-card-icon-orange">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <p class="va-card-5-title">Track Your Performance</p>
            <div class="va-card-5-accent"></div>
            <p class="va-card-5-desc">Monitor your sales, orders and business activity in real time.</p>
          </div>

          <div class="va-card-5-item">
            <span class="va-badge-orange">05</span>
            <div class="va-card-icon-orange">
              <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <p class="va-card-5-title">Grow Your Reach</p>
            <div class="va-card-5-accent"></div>
            <p class="va-card-5-desc">Connect with customers beyond your local market and grow your brand.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ======================== 4 STEPS ======================== --}}
    <section class="va-section va-section-blue">
      <div class="va-form-container">
        <div class="va-center">
          <h2 class="va-section-title">Start Selling in 4 Simple Steps</h2>
          <div class="va-accent va-accent-blue"></div>
        </div>

        <div class="va-cards-4">
          <div class="va-step-card-item">
            <div class="va-badge-dark">1</div>
            <div class="va-card-icon-blue">
              <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <p class="va-step-title">Create Your Account</p>
            <p class="va-step-desc">Register as a seller by providing your basic business information.</p>
          </div>

          <div class="va-step-card-item">
            <div class="va-badge-dark">2</div>
            <div class="va-card-icon-blue">
              <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="va-step-title">Complete Verification</p>
            <p class="va-step-desc">Submit your required business and KYC details for verification.</p>
          </div>

          <div class="va-step-card-item">
            <div class="va-badge-dark">3</div>
            <div class="va-card-icon-blue">
              <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <p class="va-step-title">Add Your Products</p>
            <p class="va-step-desc">Upload your products, pricing, images and other required information.</p>
          </div>

          <div class="va-step-card-item">
            <div class="va-badge-dark">4</div>
            <div class="va-card-icon-blue">
              <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0H9m3 0h3" />
              </svg>
            </div>
            <p class="va-step-title">Start Selling</p>
            <p class="va-step-desc">Once approved, your products can go live and you can start receiving orders.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- ======================== CTA BANNER (FULL WIDTH 100%, NO BORDER RADIUS) ======================== --}}
    <section class="va-cta-section-full">
      <div class="va-form-container">
        <div class="va-cta-banner-content">
          <div>
            <h2>Ready to Grow<br>Your Business?</h2>
            <div class="va-accent va-accent-green" style="margin-bottom: 16px;"></div>
            <p>Join Indian MSME and take your products to customers across India.</p>
            <div class="va-cta-btns">
              <a href="#register-form" class="va-btn-green">Become a Seller</a>
              <a href="{{ route('vendor.login') }}" class="va-btn-dark-secondary">Already have an account? Login</a>
            </div>
          </div>

          {{-- Single team-sellers.jpg image --}}
          <div class="va-cta-img-wrap">
            <img src="{{ asset('images/vendor-account/team-sellers.jpg') }}" alt="Seller Team">
          </div>
        </div>
      </div>
    </section>

    {{-- ======================== TRUST BAR ======================== --}}
    <div class="va-trust-bar">
      <div class="va-form-container">
        <div class="va-trust-grid">
          <div class="va-trust-item">
            <div class="va-trust-icon">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <h5>Trusted Marketplace</h5>
              <p>Secure and reliable platform for your business.</p>
            </div>
          </div>

          <div class="va-trust-item">
            <div class="va-trust-icon">
              <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-6 8v-1c0-2 4-3.1 6-3.1s6 1.1 6 3.1v1H6zm11.5-12c1.38 0 2.5 1.12 2.5 2.5S18.88 11 17.5 11c-.28 0-.53-.05-.77-.13.36-.73.57-1.54.57-2.37 0-.83-.21-1.64-.57-2.37.24-.08.49-.13.77-.13zM18 14.75c1.47.53 2.5 1.48 2.5 2.75v1h-2v-1c0-.78-.36-1.5-1.02-2.06.18.09.36.2.52.31zM6.5 8C7.88 8 9 9.12 9 10.5S7.88 13 6.5 13c-.28 0-.53-.05-.77-.13.36-.73.57-1.54.57-2.37 0-.83-.21-1.64-.57-2.37.24-.08.49-.13.77-.13zM6 14.75c-1.47.53-2.5 1.48-2.5 2.75v1h2v-1c0-.78.36-1.5 1.02-2.06A4.98 4.98 0 006 14.75z" />
              </svg>
            </div>
            <div>
              <h5>Made for MSMEs</h5>
              <p>Built to empower Indian small businesses.</p>
            </div>
          </div>

          <div class="va-trust-item">
            <div class="va-trust-icon">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <div>
              <h5>Business Growth</h5>
              <p>Tools and support to help your business grow.</p>
            </div>
          </div>

          <div class="va-trust-item">
            <div class="va-trust-icon">
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div>
              <h5>We're Here to Help</h5>
              <p>Our support team is always ready to assist you.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@push('styles')
  <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('plugins/toastify/toastify.min.css') }}">
  <style>
    /* Fix horizontal scroll caused by theme layout */
    html,
    body {
      overflow-x: hidden !important;
      max-width: 100%;
    }

    .select2-container--default .select2-selection--single {
      height: 46px !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      display: flex !important;
      align-items: center !important;
      background: #fff !important;
      padding-left: 6px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      font-size: 14px !important;
      color: #1e293b !important;
      line-height: 44px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 44px !important;
      right: 8px !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #2a9f4e !important;
      box-shadow: 0 0 0 3px rgba(42, 159, 78, 0.12) !important;
    }

    .select2-dropdown {
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
      z-index: 9999 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #2a9f4e !important;
    }

    .select2-container--default .select2-results__option {
      font-size: 14px !important;
      padding: 8px 12px !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      border-radius: 6px !important;
      border: 1px solid #e2e8f0 !important;
      padding: 6px 10px !important;
      font-size: 13px !important;
    }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
  <script src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>
  <script>
    jQuery(document).ready(function ($) {
      // Remove NiceSelect if present
      $('select.va-select-simple, select.va-select-state, select.va-select-district').each(function () {
        $(this).next('.nice-select').remove();
        $(this).show();
      });

      // Init Select2 - state
      $('select.va-select-state').select2({ placeholder: '— Select State —', width: '100%' });

      // Init Select2 - district
      $('select.va-select-district').select2({ placeholder: '— Select State First —', width: '100%' });

      // Init Select2 - simple selects (no search)
      $('select.va-select-simple').select2({ minimumResultsForSearch: Infinity, width: '100%' });

      // District AJAX loader
      function loadDistricts(stateId, selectedId) {
        var $d = $('select.va-select-district');
        $d.empty().append(new Option('Loading...', '', true, true)).trigger('change');
        if (!stateId) { $d.empty().append(new Option('— Select State First —', '', true, true)).trigger('change'); return; }
        fetch('/api/locations/districts?state_id=' + stateId)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            $d.empty().append(new Option('— Select District —', '', !selectedId, !selectedId));
            data.forEach(function (d) {
              var sel = selectedId && d.id == selectedId;
              $d.append(new Option(d.name, d.id, sel, sel));
            });
            $d.trigger('change');
          })
          .catch(function () { $d.empty().append(new Option('Error loading', '', true, true)).trigger('change'); });
      }

      var oldState = $('select.va-select-state').val();
      var oldDistrict = "{{ old('district') }}";
      if (oldState) loadDistricts(oldState, oldDistrict);

      $('select.va-select-state').on('change', function () { loadDistricts($(this).val(), null); });

      // Brand name toggle
      function toggleBrand() {
        var t = ($('#seller_type').val() || '').toLowerCase();
        if (t === 'manufacturer') { $('#brand-container').show(); }
        else { $('#brand-container').hide(); $('#brand_name').val(''); }
      }
      $('#seller_type').on('change', toggleBrand);
      toggleBrand();

      // Form submit
      $('form').on('submit', function (e) {
        var mob = $('#mobile').val().trim();
        if (!/^\d{10}$/.test(mob)) {
          e.preventDefault();
          Toastify({ text: 'Mobile number must be exactly 10 digits.', duration: 3000, gravity: 'top', position: 'right', backgroundColor: '#ef4444' }).showToast();
          $('#mobile').focus();
          return false;
        }
        var $btn = $('#btn-submit-vendor-reg');
        $btn.prop('disabled', true).css('opacity', '0.75').html('<svg style="animation:spin 1s linear infinite;width:18px;height:18px;margin-right:8px" viewBox="0 0 24 24" fill="none"><circle opacity=".25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path opacity=".75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Registering Account...');
      });
    });
  </script>
  <style>
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
  </style>
@endpush