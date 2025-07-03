<?php
function renderRegisterForm() {
    ob_start();
    ?>
    <!-- Registration Form -->
    <div class="center-container">
        <div class="register-block">
            <div class="text-center mb-6">
            <div class="text-2xl font-serif font-bold tracking-wide text-gray-800 mb-1">PRESTIGE <span class="inline-block align-middle" style="font-size:1.3rem;"><img src="/assets/images/icon_logo.svg" alt="Prestige Logo" style="height:1.3rem;display:inline;"></span></div>
                <div class="register-subtitle">Sign Up For An Account</div>
            </div>
            
            <form method="POST" class="space-y-6" id="registerForm">
                <input type="hidden" name="register" value="1">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1 text-blue-600"></i>Username *
                        </label>
                        <input type="text" name="username" required minlength="3"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="username">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-blue-600"></i>Email *
                        </label>
                        <input type="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="email@example.com">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-1 text-purple-600"></i>Full Name *
                    </label>
                    <input type="text" name="full_name" required
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                           placeholder="นาย/นาง ชื่อ นามสกุล">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-green-600"></i>Phone Number *
                            <span class="text-xs text-gray-500">(For OTP verification)</span>
                        </label>
                        <input type="tel" name="phone" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="Enter phone number">
                        <div class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>We will send OTP code to this number for verification
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-orange-600"></i>Company/Organization
                        </label>
                        <input type="text" name="company"
                               value="<?php echo isset($_POST['company']) ? htmlspecialchars($_POST['company']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                               placeholder="ชื่อบริษัท (ถ้ามี)">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1 text-red-600"></i>Password *
                        </label>
                        <input type="password" name="password" required minlength="6" id="password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 pr-10"
                               placeholder="อย่างน้อย 6 ตัวอักษร">
                        <button type="button" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#b3a89c] bg-transparent border-0 outline-none" onclick="togglePassword('password', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <div class="text-xs text-gray-500 mt-1" id="passwordStrength"></div>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1 text-red-600"></i>Confirm Password *
                        </label>
                        <input type="password" name="confirm_password" required minlength="6" id="confirmPassword"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 pr-10"
                               placeholder="กรอกรหัสผ่านอีกครั้ง">
                        <button type="button" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#b3a89c] bg-transparent border-0 outline-none" onclick="togglePassword('confirmPassword', this)">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                        <div class="text-xs mt-1" id="passwordMatch"></div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="terms" required
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="terms" class="text-sm text-gray-600">
                        I agree to the <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Terms of Service</a> 
                        and <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">Privacy Policy</a> 
                        of prestige88 and agree to receive SMS messages for verification
                    </label>
                </div>
                
                <!-- OTP Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <i class="fas fa-mobile-alt text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-blue-800 mb-1">OTP Verification</h4>
                            <p class="text-sm text-blue-700">
                                After clicking Register, we will send OTP code to your phone 
                                Please make sure the phone number is correct and can receive SMS
                            </p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium py-4 px-6 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-mobile-alt mr-2"></i>Send OTP for Registration
                </button>
                
                <!-- VIP Benefits -->
                <div class="mt-8 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border border-yellow-200">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-crown mr-2 text-yellow-600"></i>VIP Member Benefits
                    </h4>
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-2"></i>
                            Book in advance
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-2"></i>
                            Member Discount 5%
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-2"></i>
                            Service 24/7
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-600 mr-2"></i>
                            Verified with OTP
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
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
        
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        
        // Password strength checker
        passwordInput.addEventListener('input', function(e) {
            const password = e.target.value;
            let strength = 0;
            let feedback = [];
            
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
            
            if (password.length > 0) {
                const strengthLevel = Math.min(strength, 5);
                passwordStrength.textContent = strengthTexts[strengthLevel].text;
                passwordStrength.className = `text-xs mt-1 ${strengthTexts[strengthLevel].color}`;
            } else {
                passwordStrength.textContent = '';
            }
            
            checkPasswordMatch();
        });
        
        // Password confirmation checker
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    passwordMatch.textContent = 'รหัสผ่านตรงกัน';
                    passwordMatch.className = 'text-xs mt-1 text-green-500';
                    confirmPasswordInput.classList.remove('border-red-300');
                    confirmPasswordInput.classList.add('border-green-300');
                } else {
                    passwordMatch.textContent = 'รหัสผ่านไม่ตรงกัน';
                    passwordMatch.className = 'text-xs mt-1 text-red-500';
                    confirmPasswordInput.classList.remove('border-green-300');
                    confirmPasswordInput.classList.add('border-red-300');
                }
            } else {
                passwordMatch.textContent = '';
                confirmPasswordInput.classList.remove('border-red-300', 'border-green-300');
            }
        }
        
        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังส่ง OTP...';
            submitBtn.disabled = true;
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
    });

    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
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