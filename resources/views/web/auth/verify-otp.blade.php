@extends('web.layouts.app')

@section('title', 'OTP Verification — ADC Registration — Indian MSME')

@section('content')

  <!-- ========== Breadcrumb Section Start ========== -->
  <div style="background-color: #f8fafc; padding: 18px 0; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
      <div class="breadcrumb" style="font-size: 14px;">
        <ul>
          <li>
            <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none;">
              <i class="hgi hgi-stroke hgi-home-01" style="font-size: 18px; vertical-align: middle; margin-right: 4px;"></i>
              Home
            </a>
          </li>
          <li style="color: #cbd5e1; margin: 0 8px;">&#8226;</li>
          <li><a href="{{ route('login') }}" style="color: #64748b; text-decoration: none;">ADC Registration</a></li>
          <li style="color: #cbd5e1; margin: 0 8px;">&#8226;</li>
          <li><span style="font-weight: 700; color: #0f2942;">OTP Verification</span></li>
        </ul>
      </div>
    </div>
  </div>
  <!-- ========== Breadcrumb Section End ========== -->

  <!-- ========== Centered OTP Section Start ========== -->
  <section style="background-color: #f1f5f9; padding: 60px 0; min-height: 75vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 680px; margin: 0 auto; padding: 0 16px;">
      
      <!-- Main White Card -->
      <div style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 20px 40px rgba(15, 41, 66, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05); padding: 40px 32px;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 32px;">
          <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: #e6f6ee; color: #092040; margin-bottom: 16px; border: 1px solid #c2ebd4;">
            <i class="hgi hgi-stroke hgi-shield-key" style="font-size: 32px;"></i>
          </div>
          <h2 style="font-size: 28px; font-weight: 800; color: #0f2942; margin-bottom: 6px; letter-spacing: -0.5px;">
            ADC Registration OTP Verification
          </h2>
          <p style="font-size: 14px; font-weight: 600; color: #64748b; margin: 0;">
            Mobile Number: <strong style="color: #0f2942; font-family: monospace; font-size: 16px; background: #f8fafc; padding: 4px 12px; border-radius: 8px; border: 1px solid #cbd5e1;">+91 {{ $mobile }}</strong>
          </p>
        </div>

        <!-- Alert messages -->
        @if ($errors->any())
          <div style="margin-bottom: 24px; padding: 16px; border-radius: 14px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 13px; font-weight: 600;">
            @foreach ($errors->all() as $error)
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <i class="hgi hgi-stroke hgi-alert-circle" style="font-size: 18px; color: #dc2626;"></i>
                <span>{{ $error }}</span>
              </div>
            @endforeach
          </div>
        @endif

        <!-- Prominent TheekHai Instruction Box -->
        <div style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 18px; padding: 24px; margin-bottom: 32px;">
          
          @if(session('resend_success'))
            <p style="font-weight: 800; color: #166534; font-size: 16px; margin: 0 0 8px 0; line-height: 1.4;">
              ✓ A new OTP has been sent to your registered mobile number.
            </p>
            <p style="font-weight: 700; color: #0f2942; font-size: 14px; margin: 0 0 16px 0;">
              Please check the TheekHai App and enter the latest OTP.
            </p>
          @else
            <p style="font-weight: 800; color: #0f2942; font-size: 16px; margin: 0 0 8px 0; line-height: 1.4;">
              📱 Please enter the OTP sent to your registered mobile number to complete your ADC registration.
            </p>
            <p style="font-weight: 700; color: #166534; font-size: 14px; margin: 0 0 16px 0;">
              📲 You can check the OTP in the TheekHai App.
            </p>
          @endif

          <!-- EXPLICIT APP DOWNLOAD LINK BOX -->
          <div style="background: #ffffff; border: 2px dashed #22c55e; border-radius: 14px; padding: 18px; margin: 16px 0; text-align: left;">
            <div style="font-size: 14px; font-weight: 800; color: #0f2942; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <i class="hgi hgi-stroke hgi-link-01" style="color: #16a34a; font-size: 18px;"></i>
              <span>Download TheekHai App:</span>
            </div>
            
            <div style="margin-bottom: 12px;">
              <a href="https://play.google.com/store/apps/details?id=com.chat.theekhai" 
                 target="_blank" 
                 style="color: #2563eb; font-weight: 700; font-family: monospace; font-size: 13px; text-decoration: underline; word-break: break-all; display: inline-block;">
                https://play.google.com/store/apps/details?id=com.chat.theekhai
              </a>
            </div>

            <div>
              <a href="https://play.google.com/store/apps/details?id=com.chat.theekhai" 
                 target="_blank" 
                 style="display: inline-flex; align-items: center; gap: 8px; background: #16a34a; color: #ffffff; padding: 10px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.25);">
                <i class="hgi hgi-stroke hgi-play-store" style="font-size: 18px;"></i>
                <span>Get App on Google Play Store</span>
              </a>
            </div>
          </div>

          <!-- 5-Minute Countdown Timer Badge -->
          <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 16px; font-weight: 700; font-size: 13px; margin-top: 4px; transition: all 0.3s;" id="timer-box">
            <i class="hgi hgi-stroke hgi-clock-01" style="font-size: 16px; color: #d97706;"></i>
            <span id="timer-display-text">OTP expires in <strong id="countdown-timer" style="font-family: monospace; font-size: 15px;">05:00</strong></span>
          </div>

        </div>

        <!-- 6-Digit OTP Form -->
        <form action="{{ route('adc.verify-otp.post') }}" method="POST" id="otp-form" style="margin-top: 8px;">
          @csrf

          <div style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
              <label style="display: block; font-size: 13px; font-weight: 800; color: #0f2942; text-transform: uppercase; letter-spacing: 0.5px;">
                Enter 6-Digit OTP <span style="color: #ef4444;">*</span>
              </label>
              <button type="button" style="background: none; border: none; font-size: 12px; font-weight: 700; color: #092040; cursor: pointer; text-decoration: underline;" id="clear-otp-btn">
                Clear All
              </button>
            </div>

            <!-- 6 Individual Styled Boxes -->
            <div style="display: flex; justify-content: center; gap: 10px; margin: 16px 0;" id="otp-inputs-container">
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" autofocus />
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" />
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" />
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" />
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" />
              <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="otp-digit-box" />
            </div>

            <!-- Hidden input that holds the full combined OTP -->
            <input type="hidden" name="otp" id="hidden_otp" required />
          </div>

          <!-- Submit Button -->
          <button type="submit" id="btn-submit-otp" style="width: 100%; height: 54px; background: linear-gradient(135deg, #0f2942 0%, #1a3d5e 100%); color: #ffffff; border: none; border-radius: 14px; font-size: 16px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px rgba(15, 41, 66, 0.2); transition: all 0.2s;">
            <i class="hgi hgi-stroke hgi-tick-double-01" style="font-size: 22px; color: #48b77a;"></i>
            <span>Verify OTP & Complete ADC Registration</span>
          </button>
        </form>

        <!-- Resend OTP Section -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9; text-align: center;">
          <p style="font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 12px;" id="resend-subtext">
            Didn't receive the OTP on your TheekHai App? Resend button will activate when OTP expires in 5 minutes.
          </p>
          <form action="{{ route('adc.resend-otp') }}" method="POST" id="resend-otp-form" style="display: inline-block;">
            @csrf
            <button type="submit" id="resend-otp-btn" disabled style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: #e2e8f0; border: 1px solid #cbd5e1; border-radius: 10px; color: #94a3b8; font-size: 13px; font-weight: 700; cursor: not-allowed; transition: all 0.2s; opacity: 0.7;">
              <i class="hgi hgi-stroke hgi-reload" style="font-size: 16px; color: #94a3b8;" id="resend-icon"></i>
              <span id="resend-btn-text">Resend OTP in 05:00</span>
            </button>
          </form>
        </div>

      </div>
    </div>
  </section>
  <!-- ========== OTP Section End ========== -->

  <style>
    .otp-digit-box {
      width: 52px;
      height: 60px;
      text-align: center;
      font-size: 26px;
      font-weight: 800;
      font-family: monospace;
      background: #f8fafc;
      border: 2px solid #cbd5e1;
      border-radius: 14px;
      color: #0f2942;
      outline: none;
      transition: all 0.2s ease-in-out;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);
    }
    .otp-digit-box:focus {
      border-color: #092040 !important;
      background: #ffffff !important;
      box-shadow: 0 0 0 4px rgba(41, 158, 96, 0.18) !important;
    }
    #btn-submit-otp:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(15, 41, 66, 0.28) !important;
    }
  </style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 5-Minute Countdown Timer & Resend OTP activation
  let remainingSeconds = Math.max(0, parseInt('{{ $remainingSeconds ?? 300 }}', 10));
  
  const countdownEl = document.getElementById('countdown-timer');
  const timerDisplayText = document.getElementById('timer-display-text');
  const resendBtn = document.getElementById('resend-otp-btn');
  const resendBtnText = document.getElementById('resend-btn-text');
  const resendIcon = document.getElementById('resend-icon');
  const resendSubtext = document.getElementById('resend-subtext');
  const timerBox = document.getElementById('timer-box');

  function formatTime(secs) {
    const mins = Math.floor(secs / 60);
    const s = secs % 60;
    return `${String(mins).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }

  function updateTimer() {
    if (remainingSeconds > 0) {
      const formatted = formatTime(remainingSeconds);
      if (countdownEl) countdownEl.textContent = formatted;
      if (resendBtnText) resendBtnText.textContent = `Resend OTP in ${formatted}`;
      remainingSeconds--;
    } else {
      // 5 Minutes completed: Expire OTP notification & Enable Resend OTP button
      if (timerDisplayText) {
        timerDisplayText.innerHTML = '<span style="color:#b91c1c; font-weight:800;">⚠️ OTP has expired. Please click Resend OTP below.</span>';
      }
      if (timerBox) {
        timerBox.style.background = '#fef2f2';
        timerBox.style.borderColor = '#fca5a5';
      }
      if (resendBtn) {
        resendBtn.disabled = false;
        resendBtn.style.background = '#ffffff';
        resendBtn.style.color = '#0f2942';
        resendBtn.style.borderColor = '#092040';
        resendBtn.style.cursor = 'pointer';
        resendBtn.style.opacity = '1';
        resendBtn.style.boxShadow = '0 4px 12px rgba(41, 158, 96, 0.2)';
      }
      if (resendBtnText) resendBtnText.textContent = 'Resend OTP';
      if (resendIcon) resendIcon.style.color = '#092040';
      if (resendSubtext) resendSubtext.textContent = 'You can now request a new OTP for your TheekHai App.';
      clearInterval(timerInterval);
    }
  }

  updateTimer();
  const timerInterval = setInterval(updateTimer, 1000);

  // OTP 6 Digit Inputs logic
  const digitBoxes = Array.from(document.querySelectorAll('.otp-digit-box'));
  const hiddenOtp = document.getElementById('hidden_otp');
  const otpForm = document.getElementById('otp-form');
  const clearBtn = document.getElementById('clear-otp-btn');

  function updateHiddenOtp() {
    const fullOtp = digitBoxes.map(b => b.value).join('');
    hiddenOtp.value = fullOtp;
  }

  digitBoxes.forEach((box, index) => {
    box.addEventListener('input', function (e) {
      this.value = this.value.replace(/[^0-9]/g, '');
      if (this.value.length === 1) {
        if (index < digitBoxes.length - 1) {
          digitBoxes[index + 1].focus();
        }
      }
      updateHiddenOtp();
    });

    box.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && !this.value && index > 0) {
        digitBoxes[index - 1].focus();
      }
    });

    box.addEventListener('paste', function (e) {
      e.preventDefault();
      const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
      if (pasteData) {
        pasteData.split('').forEach((char, i) => {
          if (digitBoxes[i]) {
            digitBoxes[i].value = char;
          }
        });
        if (digitBoxes[pasteData.length - 1]) {
          digitBoxes[pasteData.length - 1].focus();
        } else {
          digitBoxes[5].focus();
        }
        updateHiddenOtp();
      }
    });
  });

  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      digitBoxes.forEach(b => b.value = '');
      updateHiddenOtp();
      digitBoxes[0].focus();
    });
  }

  if (otpForm) {
    otpForm.addEventListener('submit', function (e) {
      updateHiddenOtp();
      if (hiddenOtp.value.length !== 6) {
        e.preventDefault();
        alert('Please enter all 6 digits of the OTP.');
        digitBoxes[hiddenOtp.value.length < 6 ? hiddenOtp.value.length : 0].focus();
      } else {
        const btn = document.getElementById('btn-submit-otp');
        if (btn) {
          btn.disabled = true;
          btn.style.opacity = '0.75';
          btn.style.cursor = 'not-allowed';
          btn.style.pointerEvents = 'none';
          btn.innerHTML = '<svg class="animate-spin size-5 text-white inline-block me-2" viewBox="0 0 24 24" fill="none" style="width:20px;height:20px;display:inline-block;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> <span>Verifying OTP & Completing ADC Registration...</span>';
        }
      }
    });
  }

  const resendForm = document.getElementById('resend-otp-form');
  if (resendForm) {
    resendForm.addEventListener('submit', function () {
      const btn = document.getElementById('resend-otp-btn');
      if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.75';
        btn.style.cursor = 'not-allowed';
        btn.style.pointerEvents = 'none';
        btn.innerHTML = '<svg class="animate-spin size-4 text-slate-600 inline-block me-2" viewBox="0 0 24 24" fill="none" style="width:16px;height:16px;display:inline-block;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> <span>Sending OTP...</span>';
      }
    });
  }
});
</script>
@endpush