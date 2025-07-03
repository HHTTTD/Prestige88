<?php

if (!function_exists('renderManageSettingsPage')) {
    function renderManageSettingsPage() {
        ob_start();
        ?>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-cogs text-[#a39786]"></i>
                System Settings
            </h2>
            <form id="settings-form">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- General Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">General Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="site_description">Site Description</label>
                            <textarea rows="3" id="site_description" name="site_description" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="contact_phone">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                        </div>
                    </div>
                </div>
                <!-- OTP Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">OTP Configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="otp_expiry">OTP Expiry Time (minutes)</label>
                            <input type="number" id="otp_expiry" name="otp_expiry" min="1" max="60" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="otp_length">OTP Length</label>
                            <select id="otp_length" name="otp_length" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                                <option value="4">4 digits</option>
                                <option value="6">6 digits</option>
                                <option value="8">8 digits</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="enable_sms" name="enable_sms" class="rounded border-gray-300 text-[#a39786] focus:ring-[#a39786]">
                            <label for="enable_sms" class="ml-2 text-sm text-gray-700">Enable SMS OTP</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="enable_email_otp" name="enable_email_otp" class="rounded border-gray-300 text-[#a39786] focus:ring-[#a39786]">
                            <label for="enable_email_otp" class="ml-2 text-sm text-gray-700">Enable Email OTP</label>
                        </div>
                    </div>
                </div>
                <!-- Membership Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Membership Tiers</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="silver_points">Silver Tier (points)</label>
                                <input type="number" id="silver_points" name="silver_points" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="silver_discount">Silver Discount (%)</label>
                                <input type="number" id="silver_discount" name="silver_discount" min="0" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="gold_points">Gold Tier (points)</label>
                                <input type="number" id="gold_points" name="gold_points" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="gold_discount">Gold Discount (%)</label>
                                <input type="number" id="gold_discount" name="gold_discount" min="0" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="platinum_points">Platinum Tier (points)</label>
                                <input type="number" id="platinum_points" name="platinum_points" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="platinum_discount">Platinum Discount (%)</label>
                                <input type="number" id="platinum_discount" name="platinum_discount" min="0" max="100" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- System Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">System Configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="points_rate">Points Rate (per $)</label>
                            <input type="number" id="points_rate" name="points_rate" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                            <p class="text-xs text-gray-500 mt-1">Points earned per dollar spent</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="default_currency">Default Currency</label>
                            <select id="default_currency" name="default_currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]">
                                <option value="USD">USD ($)</option>
                                <option value="THB">THB (฿)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="rounded border-gray-300 text-[#a39786] focus:ring-[#a39786]">
                            <label for="maintenance_mode" class="ml-2 text-sm text-gray-700">Maintenance Mode</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="debug_mode" name="debug_mode" class="rounded border-gray-300 text-[#a39786] focus:ring-[#a39786]">
                            <label for="debug_mode" class="ml-2 text-sm text-gray-700">Debug Mode</label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="flex gap-4 mt-8 pt-6 border-t">
                <button type="button" id="save-settings" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Save Settings
                </button>
                <button type="button" id="reset-settings" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-undo mr-2"></i>
                    Reset to Default
                </button>
                <button type="button" id="export-settings" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Export Settings
                </button>
            </div>
            <div id="settings-form-message" class="mt-4"></div>
            </form>
        </div>
        <script>
        // Helper: fill form from settings object
        function fillSettingsForm(settings) {
            document.getElementById('site_name').value = settings.site_name || '';
            document.getElementById('site_description').value = settings.site_description || '';
            document.getElementById('contact_email').value = settings.contact_email || '';
            document.getElementById('contact_phone').value = settings.contact_phone || '';
            document.getElementById('otp_expiry').value = settings.otp_expiry || 5;
            document.getElementById('otp_length').value = settings.otp_length || 6;
            document.getElementById('enable_sms').checked = !!settings.enable_sms;
            document.getElementById('enable_email_otp').checked = !!settings.enable_email_otp;
            document.getElementById('silver_points').value = settings.membership_tiers?.silver?.points || 0;
            document.getElementById('silver_discount').value = settings.membership_tiers?.silver?.discount || 0;
            document.getElementById('gold_points').value = settings.membership_tiers?.gold?.points || 4000;
            document.getElementById('gold_discount').value = settings.membership_tiers?.gold?.discount || 10;
            document.getElementById('platinum_points').value = settings.membership_tiers?.platinum?.points || 10000;
            document.getElementById('platinum_discount').value = settings.membership_tiers?.platinum?.discount || 20;
            document.getElementById('points_rate').value = settings.points_rate || 100;
            document.getElementById('default_currency').value = settings.default_currency || 'USD';
            document.getElementById('maintenance_mode').checked = !!settings.maintenance_mode;
            document.getElementById('debug_mode').checked = !!settings.debug_mode;
        }
        // Load settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('api/settings.php?action=get')
                .then(res => res.json())
                .then(data => {
                    if (data.success) fillSettingsForm(data.settings);
                });
        });
        // Save settings
        document.getElementById('save-settings').onclick = function() {
            const form = document.getElementById('settings-form');
            const fd = new FormData(form);
            // Build settings object
            const settings = {
                site_name: fd.get('site_name'),
                site_description: fd.get('site_description'),
                contact_email: fd.get('contact_email'),
                contact_phone: fd.get('contact_phone'),
                otp_expiry: parseInt(fd.get('otp_expiry')),
                otp_length: parseInt(fd.get('otp_length')),
                enable_sms: !!fd.get('enable_sms'),
                enable_email_otp: !!fd.get('enable_email_otp'),
                membership_tiers: {
                    silver: {
                        points: parseInt(fd.get('silver_points')),
                        discount: parseInt(fd.get('silver_discount'))
                    },
                    gold: {
                        points: parseInt(fd.get('gold_points')),
                        discount: parseInt(fd.get('gold_discount'))
                    },
                    platinum: {
                        points: parseInt(fd.get('platinum_points')),
                        discount: parseInt(fd.get('platinum_discount'))
                    }
                },
                points_rate: parseInt(fd.get('points_rate')),
                default_currency: fd.get('default_currency'),
                maintenance_mode: !!fd.get('maintenance_mode'),
                debug_mode: !!fd.get('debug_mode')
            };
            fetch('api/settings.php?action=save', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(settings)
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('settings-form-message').innerHTML = data.success ? '<span class="text-green-600">'+data.message+'</span>' : '<span class="text-red-600">'+data.message+'</span>';
            });
        };
        // Reset settings
        document.getElementById('reset-settings').onclick = function() {
            if (!confirm('Reset all settings to default?')) return;
            fetch('api/settings.php?action=reset', {method: 'POST'})
                .then(res => res.json())
                .then(data => {
                    if (data.success) fillSettingsForm(data.settings);
                    document.getElementById('settings-form-message').innerHTML = data.success ? '<span class="text-green-600">'+data.message+'</span>' : '<span class="text-red-600">'+data.message+'</span>';
                });
        };
        // Export settings
        document.getElementById('export-settings').onclick = function() {
            window.open('api/settings.php?action=export', '_blank');
        };
        </script>
        <?php
        return ob_get_clean();
    }
}
?> 