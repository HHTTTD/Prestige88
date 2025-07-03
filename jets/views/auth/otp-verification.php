<?php
function renderOTPVerificationPage($sessionId, $phone, $message = '', $messageType = '') {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ยืนยัน OTP - prestige88</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .floating-animation {
                animation: float 6s ease-in-out infinite;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .otp-input {
                width: 50px;
                height: 50px;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                transition: all 0.2s;
            }
            .otp-input:focus {
                border-color: #3b82f6;
                outline: none;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-8 text-center">
                    <div class="mx-auto h-16 w-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-1">ยืนยัน OTP</h1>
                    <p class="text-blue-100 text-sm">เราได้ส่งรหัสยืนยันไปยังเบอร์โทร (รองรับเบอร์ไทยและต่างประเทศ)</p>
                </div>

                <div class="p-8">
                    <!-- Messages -->
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

                    <!-- Phone Display -->
                    <div class="text-center mb-6">
                        <p class="text-gray-600 text-sm mb-2">กรุณากรอกรหัส OTP 6 หลักที่ส่งไปยัง</p>
                        <p class="font-semibold text-gray-800">
                            <?php
                            // Mask middle digits for privacy, support international numbers
                            if (strlen($phone) > 6) {
                                $masked = substr($phone, 0, 3) . str_repeat('x', strlen($phone) - 5) . substr($phone, -2);
                            } else {
                                $masked = $phone;
                            }
                            echo htmlspecialchars($masked);
                            ?>
                        </p>
                    </div>

                    <!-- OTP Form -->
                    <form method="POST" id="otpForm" class="space-y-6">
                        <input type="hidden" name="action" value="verify_otp">
                        <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($sessionId); ?>">
                        
                        <!-- OTP Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                                กรอกรหัส OTP 6 หลัก
                            </label>
                            <div class="flex justify-center space-x-2">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                <input type="text" 
                                       class="otp-input" 
                                       id="otp<?php echo $i; ?>"
                                       maxlength="1" 
                                       pattern="[0-9]"
                                       inputmode="numeric"
                                       autocomplete="off">
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="otp" id="otpValue">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-4 px-6 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-check mr-2"></i>ยืนยัน OTP
                        </button>
                    </form>

                    <!-- Resend OTP -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 mb-3">ไม่ได้รับรหัส OTP?</p>
                        <form method="POST" id="resendForm" class="inline">
                            <input type="hidden" name="action" value="resend_otp">
                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($sessionId); ?>">
                            <button type="submit" 
                                    id="resendBtn"
                                    class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                <i class="fas fa-redo mr-1"></i>ส่งรหัสใหม่
                            </button>
                        </form>
                        <div id="countdown" class="text-xs text-gray-500 mt-2"></div>
                    </div>

                    <!-- Back to Register -->
                    <div class="mt-8 text-center">
                        <a href="?show=register" class="text-sm text-gray-600 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i>กลับไปแก้ไขข้อมูล
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OTP Input Handler
            const otpInputs = document.querySelectorAll('.otp-input');
            const otpValue = document.getElementById('otpValue');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;
                    
                    // อนุญาตเฉพาะตัวเลข
                    if (!/^[0-9]$/.test(value)) {
                        e.target.value = '';
                        return;
                    }
                    
                    // ไปช่องถัดไป
                    if (value && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                    
                    updateOTPValue();
                });
                
                input.addEventListener('keydown', function(e) {
                    // Backspace: ไปช่องก่อนหน้า
                    if (e.key === 'Backspace' && !input.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                    
                    // Arrow keys navigation
                    if (e.key === 'ArrowLeft' && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                    if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });
                
                // Paste handler
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text');
                    const digits = pasteData.replace(/\D/g, '').slice(0, 6);
                    
                    digits.split('').forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = digit;
                        }
                    });
                    
                    updateOTPValue();
                    
                    // Focus ช่องถัดไป
                    const nextIndex = Math.min(digits.length, otpInputs.length - 1);
                    otpInputs[nextIndex].focus();
                });
            });
            
            function updateOTPValue() {
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                otpValue.value = otp;
                
                // Auto submit เมื่อครบ 6 หลัก
                if (otp.length === 6) {
                    setTimeout(() => {
                        document.getElementById('otpForm').submit();
                    }, 500);
                }
            }
            
            // Countdown Timer
            let countdown = 60;
            const resendBtn = document.getElementById('resendBtn');
            const countdownDiv = document.getElementById('countdown');
            
            function startCountdown() {
                resendBtn.disabled = true;
                resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                const timer = setInterval(() => {
                    countdown--;
                    countdownDiv.textContent = `ส่งรหัสใหม่ได้ในอีก ${countdown} วินาที`;
                    
                    if (countdown <= 0) {
                        clearInterval(timer);
                        resendBtn.disabled = false;
                        resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        countdownDiv.textContent = '';
                        countdown = 60;
                    }
                }, 1000);
            }
            
            // เริ่ม countdown เมื่อโหลดหน้า
            startCountdown();
            
            // Auto-hide messages
            const message = document.querySelector('.bg-green-100, .bg-red-100');
            if (message) {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s';
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                }, 5000);
            }
            
            // Focus first input
            otpInputs[0].focus();
        });
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}