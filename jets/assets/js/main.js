document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    const successMessage = document.querySelector('.bg-green-100');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.transition = 'opacity 0.5s';
            successMessage.style.opacity = '0';
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 5000);
    }

    // Real-time total cost calculation
    const jetRadios = document.querySelectorAll('input[name="jet_id"]');
    const hoursInput = document.querySelector('input[name="flight_hours"]');
    
    function updateTotalCost() {
        const selectedJet = document.querySelector('input[name="jet_id"]:checked');
        const hours = parseFloat(hoursInput?.value || 0);
        
        if (selectedJet && hours > 0) {
            // Get jet prices from PHP (this would need to be populated)
            const jetData = window.jetPrices || {};
            const baseTotal = jetData[selectedJet.value] * hours;
            
            // Apply membership discount
            const userDiscount = window.userDiscount || 0;
            const discountAmount = baseTotal * (userDiscount / 100);
            const totalCost = baseTotal - discountAmount;
            
            // Update or create cost display
            let costDisplay = document.getElementById('cost-display');
            if (!costDisplay) {
                costDisplay = document.createElement('div');
                costDisplay.id = 'cost-display';
                costDisplay.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-xl';
                hoursInput.parentNode.parentNode.appendChild(costDisplay);
            }
            
            let discountHtml = '';
            if (userDiscount > 0) {
                discountHtml = `
                    <div class="text-xs text-gray-500 line-through">
                        ราคาปกติ: ${baseTotal.toLocaleString()} บาท
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>ราคาหลังส่วนลด:
                        </span>
                        <div class="flex items-center">
                            <span class="text-lg font-bold text-green-600">
                                ${totalCost.toLocaleString()} บาท
                            </span>
                            <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                                -${userDiscount}%
                            </span>
                        </div>
                    </div>
                `;
            } else {
                discountHtml = `
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>ค่าใช้จ่ายโดยประมาณ:
                        </span>
                        <span class="text-lg font-bold text-green-600">
                            ${totalCost.toLocaleString()} บาท
                        </span>
                    </div>
                `;
            }
            
            costDisplay.innerHTML = `
                ${discountHtml}
                <div class="text-xs text-gray-500 mt-1">
                    ${jetData[selectedJet.value].toLocaleString()} บาท/ชม. × ${hours} ชม.
                </div>
            `;
        }
    }

    jetRadios.forEach(radio => {
        radio.addEventListener('change', updateTotalCost);
    });

    if (hoursInput) {
        hoursInput.addEventListener('input', updateTotalCost);
    }

    // Form validation
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const passengers = parseInt(document.querySelector('input[name="passengers"]').value);
            const selectedJet = document.querySelector('input[name="jet_id"]:checked');
            
            if (selectedJet) {
                const jetCapacities = window.jetCapacities || {};
                
                if (passengers > jetCapacities[selectedJet.value]) {
                    e.preventDefault();
                    alert(`จำนวนผู้โดยสารเกินความจุของเครื่องบิน (สูงสุด ${jetCapacities[selectedJet.value]} คน)`);
                    return false;
                }
            }
        });
    }

    // Phone number formatting
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1-$2');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,4})/, '$1-$2-$3');
            }
            e.target.value = value;
        });
    }

    // Password confirmation validation
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    
    if (passwordInput && confirmPasswordInput) {
        function validatePassword() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('รหัสผ่านไม่ตรงกัน');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    }

    // Smooth scroll for booking form
    const bookingForm = document.querySelector('form[method="POST"]');
    if (bookingForm && window.location.hash === '#book') {
        bookingForm.scrollIntoView({ behavior: 'smooth' });
    }
});