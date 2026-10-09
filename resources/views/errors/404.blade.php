@extends('web.layouts.app')

@section('title', 'Page Not Found — Indian MSME')

@push('styles')
  <style>
    .notfound-page-wrap {
      background-color: #f8fafc;
      padding: 30px 0 80px 0;
      font-family: 'Poppins', sans-serif;
    }

    .notfound-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      padding: 0 40px;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .notfound-container {
        padding: 0 16px;
      }
    }

    /* Main Card Box */
    .notfound-card-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 55px 30px;
      max-width: 820px;
      margin: 0 auto;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    /* 404 Illustration Container */
    .notfound-graphic-wrap {
      position: relative;
      width: 280px;
      height: 200px;
      margin: 35px auto 20px auto;
      padding-top: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Heading & Subtitle */
    .notfound-heading {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin-top: 0;
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .notfound-subtitle {
      font-size: 14px;
      color: #64748b;
      margin-top: 0;
      margin-bottom: 28px;
      line-height: 1.5;
    }

    /* Action Buttons */
    .notfound-action-btns {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .btn-404-home {
      background-color: #071933;
      color: #ffffff !important;
      border-radius: 10px;
      padding: 12px 28px;
      font-size: 14px;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.2s ease;
    }

    .btn-404-home:hover {
      background-color: #0d285c;
      color: #ffffff !important;
    }

    .btn-404-shop {
      background-color: #ffffff;
      border: 1px solid #cbd5e1;
      color: #0f172a !important;
      border-radius: 10px;
      padding: 12px 28px;
      font-size: 14px;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: background-color 0.2s ease;
    }

    .btn-404-shop:hover {
      background-color: #f8fafc;
      color: #0f172a !important;
    }
  </style>
@endpush

@section('content')
  <div class="notfound-page-wrap">
    <div class="notfound-container">

      {{-- Main 404 Card Box --}}
      <div class="notfound-card-box">

        {{-- 404 Illustration Image --}}
        <div class="mb-4">
          <img src="{{ asset('ecommerce-assets/images/404-illustration.png') }}" alt="404 Page Not Found" style="width: 300px !important; max-width: 300px !important;" class="mx-auto" />
        </div>

        {{-- Heading & Subtitle --}}
        <h1 class="notfound-heading">Oops! Page Not Found</h1>
        <p class="notfound-subtitle">
          Sorry, we couldn't find the page you're looking for.<br>
          It might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        {{-- Action Buttons Row --}}
        <div class="notfound-action-btns">
          <a href="{{ route('home') }}" class="btn-404-home">
            <i class="hgi hgi-stroke hgi-home-01"></i>
            <span>Back To Home</span>
          </a>

          <a href="{{ route('shop') }}" class="btn-404-shop">
            <i class="hgi hgi-stroke hgi-compass"></i>
            <span>Explore Products</span>
          </a>
        </div>

      </div>

    </div>
  </div>
@endsection
