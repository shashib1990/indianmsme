@extends('web.layouts.app')

@section('title', 'Order Placed Successfully — Indian MSME')

@push('styles')
  <style>
    .order-success-wrap {
      background-color: #f8fafc;
      padding: 30px 0 80px 0;
      font-family: 'Poppins', sans-serif;
    }

    .order-success-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      padding: 0 40px;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .order-success-container {
        padding: 0 16px;
      }
    }

    /* Breadcrumb */
    .success-breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #64748b;
      font-weight: 500;
      margin-bottom: 24px;
    }

    .success-breadcrumb a {
      color: #334155;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .success-breadcrumb a:hover {
      color: #071933;
    }

    /* Main Card Box */
    .success-card-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 50px 30px;
      max-width: 820px;
      margin: 0 auto;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    /* Checkmark Circle + Confetti Dots */
    .success-check-wrapper {
      position: relative;
      width: 120px;
      height: 100px;
      margin: 0 auto 20px auto;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .success-check-circle {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background-color: #d1fae5;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 2;
    }

    /* Confetti Shapes */
    .confetti-dot {
      position: absolute;
      border-radius: 50%;
    }

    .confetti-dot-1 { width: 6px; height: 6px; background-color: #3b82f6; top: 15px; left: 15px; }
    .confetti-dot-2 { width: 8px; height: 8px; background-color: #facc15; top: 25px; right: 18px; transform: rotate(45deg); border-radius: 2px; }
    .confetti-dot-3 { width: 6px; height: 6px; background-color: #10b981; bottom: 20px; left: 22px; }
    .confetti-dot-4 { width: 7px; height: 7px; background-color: #a855f7; bottom: 15px; right: 25px; }
    .confetti-dot-5 { width: 5px; height: 5px; background-color: #f97316; top: 10px; right: 40px; }

    /* Heading & Text */
    .success-heading {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin-top: 0;
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .success-subtitle {
      font-size: 14px;
      color: #64748b;
      margin-top: 0;
      margin-bottom: 24px;
      line-height: 1.5;
    }

    /* Notification Box */
    .success-notify-box {
      background-color: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 14px;
      padding: 16px 20px;
      max-width: 540px;
      margin: 0 auto 28px auto;
      display: flex;
      align-items: center;
      gap: 14px;
      text-align: left;
    }

    .notify-icon-badge {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      background-color: #e0f2fe;
      color: #0284c7;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }

    /* Action Buttons */
    .success-action-btns {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .btn-success-home {
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

    .btn-success-home:hover {
      background-color: #0d285c;
      color: #ffffff !important;
    }

    .btn-success-orders {
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

    .btn-success-orders:hover {
      background-color: #f8fafc;
      color: #0f172a !important;
    }
  </style>
@endpush

@section('content')
  <div class="order-success-wrap">
    <div class="order-success-container">

      {{-- Main Success Card Box --}}
      <div class="success-card-box">

        {{-- Green Check Circle with Confetti Dots --}}
        <div class="success-check-wrapper">
          <span class="confetti-dot confetti-dot-1"></span>
          <span class="confetti-dot confetti-dot-2"></span>
          <span class="confetti-dot confetti-dot-3"></span>
          <span class="confetti-dot confetti-dot-4"></span>
          <span class="confetti-dot confetti-dot-5"></span>

          <div class="success-check-circle">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 13l4 4L19 7" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>

        {{-- Heading & Subtitle --}}
        <h1 class="success-heading">Order Placed Successfully!</h1>
        <p class="success-subtitle">
          Thank you for shopping with Indian MSME.<br>
          Your order has been placed successfully and is now being processed.
        </p>

        {{-- Notification Info Banner Box --}}
        <div class="success-notify-box">
          <div class="notify-icon-badge">
            <i class="hgi hgi-stroke hgi-package"></i>
          </div>
          <div>
            <div style="font-size: 13.5px; font-weight: 700; color: #0369a1;">
              We'll notify you once your order is confirmed.
            </div>
            <div style="font-size: 12.5px; color: #334155; margin-top: 2px;">
              You can track your order status anytime from 
              @if(Auth::guard('adc')->check())
                <a href="{{ route('adc.orders.index') }}" style="color: #0284c7; font-weight: 700; text-decoration: underline;">My Orders</a>
              @else
                <a href="{{ route('shop') }}" style="color: #0284c7; font-weight: 700; text-decoration: underline;">My Orders</a>
              @endif
              section.
            </div>
          </div>
        </div>

        {{-- Action Buttons Row --}}
        <div class="success-action-btns">
          <a href="{{ route('home') }}" class="btn-success-home">
            <i class="hgi hgi-stroke hgi-home-01"></i>
            <span>Back To Home</span>
          </a>

          @if(Auth::guard('adc')->check())
            <a href="{{ route('adc.orders.index') }}" class="btn-success-orders">
              <i class="hgi hgi-stroke hgi-package"></i>
              <span>My Orders</span>
            </a>
          @else
            <a href="{{ route('shop') }}" class="btn-success-orders">
              <i class="hgi hgi-stroke hgi-shopping-bag-02"></i>
              <span>Continue Shopping</span>
            </a>
          @endif
        </div>

      </div>

    </div>
  </div>
@endsection
