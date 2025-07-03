<?php
function renderAuthPage($showRegister = false, $message = '', $messageType = '') {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>prestige88 - <?php echo $showRegister ? 'สมัครสมาชิก' : 'เข้าสู่ระบบ'; ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                min-height: 100vh;
                background: url('') center center/cover no-repeat;
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                position: relative;
            }
            body:before {
                content: '';
                position: fixed;
                left: 0; top: 0; right: 0; bottom: 0;
                background: rgba(44, 44, 44, 0.45);
                z-index: 0;
            }
            .auth-container {
                max-width: 440px;
                margin: 60px auto 0 auto;
                padding: 44px 36px 32px 36px;
                background: rgba(255,255,255,0.97);
                border-radius: 12px;
                box-shadow: 0 4px 32px 0 rgba(0,0,0,0.13);
                position: relative;
                z-index: 1;
            }
            .auth-logo {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-bottom: 18px;
            }
            .auth-title {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.7rem;
                font-family: 'Georgia', 'Times New Roman', serif;
                font-size: 2.3rem;
                font-weight: 600;
                color: #222;
                letter-spacing: 1px;
                margin-bottom: 2px;
            }
            .auth-title img {
                height: 2.1rem;
                margin-bottom: 2px;
            }
            .auth-logo-icon {
                font-size: 2.1rem;
                color: #222;
                margin-bottom: 2px;
            }
            .auth-subtitle { display: none; }
            .input-group {
                display: flex;
                align-items: center;
                border: 1.5px solid #888;
                border-radius: 8px;
                background: #fff;
                margin-bottom: 22px;
                overflow: hidden;
            }
            .input-icon {
                background: #b3a89c;
                color: #fff;
                width: 52px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
            }
            .input-field {
                border: none;
                outline: none;
                flex: 1;
                height: 48px;
                padding: 0 12px;
                font-size: 1rem;
                background: transparent;
                color: #3d3630;
            }
            .input-group-password {
                position: relative;
            }
            .toggle-password {
                position: absolute;
                right: 12px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #b3a89c;
                font-size: 1.1rem;
                cursor: pointer;
            }
            .auth-label {
                font-size: 1.01rem;
                color: #222;
                margin-bottom: 4px;
                margin-left: 2px;
                font-weight: 500;
            }
            .auth-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }
            .checkbox-group {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 18px;
            }
            .auth-checkbox {
                width: 16px;
                height: 16px;
                accent-color: #b3a89c;
            }
            .forgot-link {
                color: #222;
                font-size: 0.97rem;
                text-decoration: none;
                opacity: 0.8;
            }
            .forgot-link:hover {
                text-decoration: underline;
                opacity: 1;
            }
            .auth-btn {
                width: 100%;
                background: #b3a89c;
                color: #fff;
                font-size: 1.13rem;
                font-weight: 500;
                border: none;
                border-radius: 8px;
                padding: 18px 0;
                margin-top: 10px;
                margin-bottom: 8px;
                cursor: pointer;
                transition: background 0.2s;
                box-shadow: 0 1px 4px 0 rgba(0,0,0,0.04);
                letter-spacing: 0.5px;
            }
            .auth-btn:hover {
                background: #a19486;
            }
            .auth-bottom {
                text-align: center;
                color: #b3a89c;
                font-size: 0.98rem;
                margin-top: 18px;
                opacity: 0.7;
            }
            .auth-bottom a {
                color: #b3a89c;
                text-decoration: underline;
                margin-left: 2px;
                font-weight: 500;
                opacity: 1;
            }
            @media (max-width: 600px) {
                .auth-container { padding: 24px 6vw 18px 6vw; }
            }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl floating-animation"></div>
            <div class="absolute bottom-20 right-20 w-96 h-96 bg-blue-300/20 rounded-full blur-3xl floating-animation" style="animation-delay: -3s;"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-purple-300/20 rounded-full blur-3xl floating-animation" style="animation-delay: -1.5s;"></div>
        </div>
        
        <div class="relative max-w-md w-full mx-4">
            <?php if ($message): ?>
            <div class="mb-6">
                <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-4 py-3 rounded-xl border">
                    <div class="flex items-center">
                        <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                        <span class="text-sm"><?php echo $message; ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($showRegister): ?>
                <?php echo renderRegisterForm(); ?>
            <?php else: ?>
                <?php echo renderLoginForm(); ?>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function renderLoginForm() {
    ob_start();
    ?>
    <div class="auth-container">
        <div class="auth-logo">
            <div class="auth-title">PRESTIGE <img src="/assets/images/icon_logo.svg" alt="Prestige Logo"></div>
        </div>
        <form method="POST" autocomplete="off">
            <input type="hidden" name="login" value="1">
            <div class="auth-label">Username</div>
            <div class="input-group">
                <span class="input-icon"><i class="fa-regular fa-user"></i></span>
                <input type="text" name="username" class="input-field" placeholder="Enter your username" required>
            </div>
            <div class="auth-row">
                <div class="auth-label" style="margin-bottom:0;">Password</div>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>
            <div class="input-group input-group-password">
                <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="input-field password-input" placeholder="ENTER YOUR PASSWORD" required>
                <button type="button" class="toggle-password" onclick="togglePassword(this)"><i class="fa-regular fa-eye" style="color:#b3a89c;"></i></button>
            </div>
            <button type="submit" class="auth-btn">SIGN IN</button>
        </form>
        <div class="auth-bottom">
            Don't have an account? <a href="?show=register">Sign Up</a>
        </div>
    </div>
    <script>
    function togglePassword(btn) {
        const input = btn.parentElement.querySelector('.password-input');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i class="fa-regular fa-eye-slash" style="color:#b3a89c;"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i class="fa-regular fa-eye" style="color:#b3a89c;"></i>';
        }
    }
    </script>
    <?php
    return ob_get_clean();
}

function renderRegisterForm() {
    ob_start();
    ?>
    <div class="flex items-center justify-center min-h-screen bg-[#f5f6fa]">
      <div class="w-full max-w-md bg-white rounded-2xl shadow-xl px-8 py-10">
        <div class="text-center mb-6">
        <div class="text-2xl font-serif font-bold tracking-wide text-gray-800 mb-1">PRESTIGE <span class="inline-block align-middle" style="font-size:1.3rem;"><img src="/assets/images/icon_logo.svg" alt="Prestige Logo" style="height:1.3rem;display:inline;vertical-align:middle;position:relative;top:-6px;"></span></div>
          <div class="text-gray-600 text-sm mb-2">Sign Up For An Account</div>
        </div>
        <form method="POST" autocomplete="off">
          <input type="hidden" name="register" value="1">
          
          <!-- Username -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-regular fa-user"></i></span>
              <input type="text" name="username" required minlength="3" placeholder="Enter username" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
            </div>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" required placeholder="Enter email" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
            </div>
          </div>

          <!-- Full Name -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-regular fa-id-card"></i></span>
              <input type="text" name="full_name" required placeholder="Enter full name" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
            </div>
          </div>

          <!-- Phone Number -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-solid fa-phone"></i></span>
              <input type="tel" name="phone" required placeholder="Enter phone number" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
            </div>
            <div class="text-xs text-gray-500 mt-1">
              <i class="fas fa-info-circle mr-1"></i>We will send OTP to this number to verify identity
            </div>
          </div>

          <!-- Company -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Company/Organization (if any)</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-solid fa-building"></i></span>
              <input type="text" name="company" placeholder="Enter company name" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
            </div>
          </div>

          <!-- Password -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-solid fa-lock"></i></span>
              <input type="password" name="password" required minlength="6" placeholder="Enter password" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
              <button type="button" tabindex="-1" class="text-[#b3a89c] bg-transparent border-0 outline-none ml-2" onclick="togglePassword(this)">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
            <div class="text-xs text-gray-500 mt-1" id="passwordStrength"></div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <div class="flex items-center border border-gray-300 rounded-lg bg-white px-3">
              <span class="text-lg text-[#b3a89c] mr-2"><i class="fa-solid fa-lock"></i></span>
              <input type="password" name="confirm_password" required minlength="6" placeholder="Enter password again" class="flex-1 py-3 bg-transparent outline-none text-gray-800" />
              <button type="button" tabindex="-1" class="text-[#b3a89c] bg-transparent border-0 outline-none ml-2" onclick="togglePassword(this)">
                <i class="fa-regular fa-eye"></i>
              </button>
            </div>
            <div class="text-xs mt-1" id="passwordMatch"></div>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-center mb-4">
            <input type="checkbox" id="terms" required class="w-4 h-4 text-[#b3a89c] border-gray-300 rounded focus:ring-[#b3a89c] mr-2" />
            <label for="terms" class="text-xs text-gray-600">
              I agree to the <a href="#" class="text-[#b3a89c] hover:underline">Terms of Service</a> 
              and <a href="#" class="text-[#b3a89c] hover:underline">Privacy Policy</a> 
              of prestige88 and agree to receive SMS messages for verification.
            </label>
          </div>

          <!-- OTP Notice -->
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
            <div class="flex items-start">
              <i class="fas fa-mobile-alt text-blue-600 mt-1 mr-3"></i>
              <div>
                <h4 class="font-semibold text-blue-800 mb-1">OTP Verification</h4>
                <p class="text-sm text-blue-700">
                  After clicking sign up, we will send OTP 6 digits to your phone 
                  Please make sure the phone number is correct and can receive SMS
                </p>
              </div>
            </div>
          </div>

          <button type="submit" class="w-full bg-[#b3a89c] text-white font-medium py-3 rounded-lg text-base mb-2 hover:bg-[#a19486] transition duration-200" style="letter-spacing:0.5px;">
            <i class="fas fa-mobile-alt mr-2"></i>Send OTP to Register
          </button>

          <div class="text-center text-xs text-gray-500 mt-2">
          Already have an account? <a href="?show=login" class="text-[#b3a89c] hover:underline">Sign In</a>
          </div>
        </form>
      </div>
    </div>

    <script>
    function togglePassword(btn) {
        const input = btn.parentElement.querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i class="fa-regular fa-eye"></i>';
        }
    }

    // Password strength checker
    document.querySelector('input[name="password"]').addEventListener('input', function(e) {
        const password = e.target.value;
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        const strengthTexts = [
            { text: 'รหัสผ่านอ่อนแอมาก', color: 'text-red-500' },
            { text: 'รหัสผ่านอ่อนแอ', color: 'text-red-400' },
            { text: 'รหัสผ่านปานกลาง', color: 'text-yellow-500' },
            { text: 'รหัสผ่านดี', color: 'text-green-500' },
            { text: 'รหัสผ่านแข็งแกร่ง', color: 'text-green-600' },
            { text: 'รหัสผ่านแข็งแกร่งมาก', color: 'text-green-700' }
        ];
        
        const strengthDisplay = document.getElementById('passwordStrength');
        if (password.length > 0) {
            const strengthLevel = Math.min(strength, 5);
            strengthDisplay.textContent = strengthTexts[strengthLevel].text;
            strengthDisplay.className = `text-xs mt-1 ${strengthTexts[strengthLevel].color}`;
        } else {
            strengthDisplay.textContent = '';
        }
        
        checkPasswordMatch();
    });

    // Password confirmation checker
    document.querySelector('input[name="confirm_password"]').addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        const matchDisplay = document.getElementById('passwordMatch');
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                matchDisplay.textContent = 'รหัสผ่านตรงกัน';
                matchDisplay.className = 'text-xs mt-1 text-green-500';
            } else {
                matchDisplay.textContent = 'รหัสผ่านไม่ตรงกัน';
                matchDisplay.className = 'text-xs mt-1 text-red-500';
            }
        } else {
            matchDisplay.textContent = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const phoneInputs = document.querySelectorAll('input[name="phone"]');
        phoneInputs.forEach(function(phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                let formatted = '';
                if (value.length <= 3) {
                    formatted = value;
                } else if (value.length <= 6) {
                    formatted = value.slice(0, 3) + '-' + value.slice(3);
                } else if (value.length <= 10) {
                    formatted = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                } else {
                    formatted = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10) + '-' + value.slice(10, 14);
                }
                e.target.value = formatted;
            });
        });
    });

    // ลบขีดออกก่อน submit ฟอร์ม
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const phoneInputs = form.querySelectorAll('input[name="phone"]');
            phoneInputs.forEach(function(input) {
                input.value = input.value.replace(/[^0-9+]/g, '');
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}