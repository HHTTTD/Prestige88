<?php

if (!function_exists('renderManageRewardsPage')) {
    function renderManageRewardsPage($users, $bookings) {
        // Load real data
        $specialOffers = OffersRewards::getSpecialOffers(false); // Get all offers, not just active
        $availableRewards = OffersRewards::getAvailableRewards(false); // Get all rewards, not just active
        $promoCodes = OffersRewards::getPromoCodes(false); // Get all promo codes, not just active
        $statistics = OffersRewards::getStatistics();
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-gift text-[#a39786]"></i>
                    Manage Offers & Rewards
                </h2>
                <div class="flex gap-2">
                    <button onclick="openAddOfferModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Add New Offer
                    </button>
                    <button onclick="exportReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-download"></i>
                        Export Report
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Total Users</p>
                            <p class="text-2xl font-bold text-blue-800"><?php echo count($users); ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Active Offers</p>
                            <p class="text-2xl font-bold text-green-800"><?php echo $statistics['active_offers']; ?></p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-tag text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-yellow-600 font-medium">Total Redemptions</p>
                            <p class="text-2xl font-bold text-yellow-800"><?php echo $statistics['total_redemptions']; ?></p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-ticket-alt text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-purple-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-600 font-medium">Active Rewards</p>
                            <p class="text-2xl font-bold text-purple-800"><?php echo $statistics['active_rewards']; ?></p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <i class="fas fa-star text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg">
                <button class="flex-1 py-2 px-4 rounded-md bg-white shadow-sm text-sm font-medium text-gray-700" onclick="showTab('special-offers')">
                    Special Offers
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" onclick="showTab('available-rewards')">
                    Available Rewards
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" onclick="showTab('promo-codes')">
                    Promo Codes
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700" onclick="showTab('user-points')">
                    User Points
                </button>
            </div>
            
            <!-- Special Offers Tab -->
            <div id="special-offers" class="tab-content">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Special Offers Management</h3>
                    <button onclick="openAddOfferModal()" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Special Offer
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($specialOffers as $offer): ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="<?php echo $offer['icon']; ?> text-green-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold"><?php echo htmlspecialchars($offer['title']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($offer['description']); ?></p>
                            </div>
                        </div>
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-green-600"><?php echo $offer['badge']; ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Valid Until:</span>
                                <span class="text-red-500"><?php echo $offer['valid_until'] ? date('M d, Y', strtotime($offer['valid_until'])) : 'No Limit'; ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Usage:</span>
                                <span class="text-blue-600"><?php echo $offer['usage_count']; ?> times</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Status:</span>
                                <span class="<?php echo $offer['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $offer['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editOffer('<?php echo $offer['id']; ?>')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteOffer('<?php echo $offer['id']; ?>')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Available Rewards Tab -->
            <div id="available-rewards" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Available Rewards Management</h3>
                    <button onclick="openAddRewardModal()" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Reward
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($availableRewards as $reward): ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="<?php echo $reward['icon']; ?> text-green-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold"><?php echo htmlspecialchars($reward['title']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($reward['description']); ?></p>
                            </div>
                        </div>
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Required Points:</span>
                                <span class="font-semibold text-yellow-600"><?php echo number_format($reward['required_points']); ?> pts</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tier:</span>
                                <span class="text-blue-600"><?php echo ucfirst($reward['tier']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Unlocked By:</span>
                                <span class="text-blue-600"><?php echo $reward['unlocked_by_count']; ?> users</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Status:</span>
                                <span class="<?php echo $reward['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $reward['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editReward('<?php echo $reward['id']; ?>')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteReward('<?php echo $reward['id']; ?>')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Promo Codes Tab -->
            <div id="promo-codes" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Promo Codes Management</h3>
                    <button onclick="openAddPromoModal()" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>Add Promo Code
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($promoCodes as $promo): ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-tag text-purple-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold"><?php echo htmlspecialchars($promo['code']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($promo['title']); ?></p>
                            </div>
                        </div>
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-green-600">
                                    <?php echo $promo['discount_type'] === 'percentage' ? $promo['discount_value'] . '%' : '$' . $promo['discount_value']; ?>
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Valid Until:</span>
                                <span class="text-red-500"><?php echo $promo['valid_until'] ? date('M d, Y', strtotime($promo['valid_until'])) : 'No Limit'; ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Usage:</span>
                                <span class="text-blue-600"><?php echo $promo['usage_count']; ?> times</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Status:</span>
                                <span class="<?php echo $promo['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $promo['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editPromo('<?php echo $promo['code']; ?>')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deletePromo('<?php echo $promo['code']; ?>')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- User Points Tab -->
            <div id="user-points" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">User Points & Tiers</h3>
                    <button onclick="exportUserData()" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-3 text-left">User</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Current Points</th>
                                <th class="px-4 py-3 text-left">Tier</th>
                                <th class="px-4 py-3 text-left">Total Spent</th>
                                <th class="px-4 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <?php 
                            $userPoints = 0;
                            $totalSpent = 0;
                            foreach ($bookings as $booking) {
                                if ($booking['user_id'] === $user['id'] && in_array($booking['status'], ['confirmed', 'completed'])) {
                                    $userPoints += floor($booking['total_cost'] / 100);
                                    $totalSpent += $booking['total_cost'];
                                }
                            }
                            
                            $tier = 'Silver';
                            $tierColor = 'bg-gray-100 text-gray-700';
                            if ($userPoints >= 10000) {
                                $tier = 'Platinum';
                                $tierColor = 'bg-purple-100 text-purple-700';
                            } elseif ($userPoints >= 4000) {
                                $tier = 'Gold';
                                $tierColor = 'bg-yellow-100 text-yellow-700';
                            }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-[#a39786] rounded-full flex items-center justify-center text-white text-sm">
                                            <?php echo strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-semibold"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user['username']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-star text-yellow-500"></i>
                                        <span class="font-semibold"><?php echo number_format($userPoints); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $tierColor; ?>">
                                        <?php echo $tier; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-green-600">
                                    $<?php echo number_format($totalSpent, 2); ?>
                                </td>
                                <td class="px-4 py-3 flex items-center gap-3">
                                    <button onclick="adjustUserPoints('<?php echo $user['id']; ?>', <?php echo $userPoints; ?>)" class="text-blue-600 hover:text-blue-800" title="Adjust Points">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewUserHistory('<?php echo $user['id']; ?>')" class="text-green-600 hover:text-green-800" title="View History">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button onclick="sendUserReward('<?php echo $user['id']; ?>')" class="text-purple-600 hover:text-purple-800" title="Send Reward">
                                        <i class="fas fa-gift"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Add/Edit Offer Modal -->
        <div id="offer-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold" id="offer-modal-title">Add Special Offer</h3>
                    <button onclick="closeOfferModal()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <form id="offer-form" class="space-y-4">
                    <input type="hidden" name="action" value="add_offer">
                    <input type="hidden" name="offer_id" id="offer-id">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" name="title" id="offer-title" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Icon (FontAwesome class)</label>
                            <input type="text" name="icon" id="offer-icon" class="w-full rounded-lg border-gray-300" value="fas fa-gift" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" id="offer-description" rows="2" class="w-full rounded-lg border-gray-300" required></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Badge Text</label>
                            <input type="text" name="badge" id="offer-badge" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Note</label>
                            <input type="text" name="note" id="offer-note" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Discount Type</label>
                            <select name="discount_type" id="offer-discount-type" class="w-full rounded-lg border-gray-300">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Discount Value</label>
                            <input type="number" name="discount_value" id="offer-discount-value" class="w-full rounded-lg border-gray-300" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Target Audience</label>
                            <select name="target_audience" id="offer-target-audience" class="w-full rounded-lg border-gray-300">
                                <option value="all">All Users</option>
                                <option value="new_users">New Users</option>
                                <option value="premium_users">Premium Users</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Valid Until</label>
                            <input type="date" name="valid_until" id="offer-valid-until" class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Max Usage</label>
                            <input type="number" name="max_usage" id="offer-max-usage" class="w-full rounded-lg border-gray-300" placeholder="Leave empty for unlimited">
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="offer-is-active" class="rounded" checked>
                        <label class="text-sm font-medium">Active</label>
                    </div>
                    
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-3 rounded-lg">
                            Save Offer
                        </button>
                        <button type="button" onclick="closeOfferModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Add/Edit Reward Modal -->
        <div id="reward-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold" id="reward-modal-title">Add Available Reward</h3>
                    <button onclick="closeRewardModal()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <form id="reward-form" class="space-y-4">
                    <input type="hidden" name="action" value="add_reward">
                    <input type="hidden" name="reward_id" id="reward-id">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" name="title" id="reward-title" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Icon (FontAwesome class)</label>
                            <input type="text" name="icon" id="reward-icon" class="w-full rounded-lg border-gray-300" value="fas fa-star" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" id="reward-description" rows="2" class="w-full rounded-lg border-gray-300" required></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Required Points</label>
                            <input type="number" name="required_points" id="reward-required-points" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tier</label>
                            <select name="tier" id="reward-tier" class="w-full rounded-lg border-gray-300">
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                                <option value="black">Black</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="reward-is-active" class="rounded" checked>
                        <label class="text-sm font-medium">Active</label>
                    </div>
                    
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-3 rounded-lg">
                            Save Reward
                        </button>
                        <button type="button" onclick="closeRewardModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Add/Edit Promo Code Modal -->
        <div id="promo-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold" id="promo-modal-title">Add Promo Code</h3>
                    <button onclick="closePromoModal()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <form id="promo-form" class="space-y-4">
                    <input type="hidden" name="action" value="add_promo">
                    <input type="hidden" name="promo_code" id="promo-code">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Code</label>
                            <input type="text" name="code" id="promo-code-input" class="w-full rounded-lg border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" name="title" id="promo-title" class="w-full rounded-lg border-gray-300" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" id="promo-description" rows="2" class="w-full rounded-lg border-gray-300" required></textarea>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Discount Type</label>
                            <select name="discount_type" id="promo-discount-type" class="w-full rounded-lg border-gray-300">
                                <option value="percentage">Percentage</option>
                                <option value="fixed_amount">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Discount Value</label>
                            <input type="number" name="discount_value" id="promo-discount-value" class="w-full rounded-lg border-gray-300" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Max Usage</label>
                            <input type="number" name="max_usage" id="promo-max-usage" class="w-full rounded-lg border-gray-300" placeholder="Leave empty for unlimited">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Valid Until</label>
                        <input type="date" name="valid_until" id="promo-valid-until" class="w-full rounded-lg border-gray-300">
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="promo-is-active" class="rounded" checked>
                        <label class="text-sm font-medium">Active</label>
                    </div>
                    
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-3 rounded-lg">
                            Save Promo Code
                        </button>
                        <button type="button" onclick="closePromoModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.remove('hidden');
            
            // Update tab button styles
            const tabButtons = document.querySelectorAll('[onclick^="showTab"]');
            tabButtons.forEach(button => {
                button.classList.remove('bg-white', 'shadow-sm', 'text-gray-700');
                button.classList.add('text-gray-500');
            });
            
            // Highlight active tab button
            event.target.classList.remove('text-gray-500');
            event.target.classList.add('bg-white', 'shadow-sm', 'text-gray-700');
        }
        
        // Offer Modal Functions
        function openAddOfferModal() {
            document.getElementById('offer-modal-title').textContent = 'Add Special Offer';
            document.getElementById('offer-form').reset();
            document.getElementById('offer-form').querySelector('[name="action"]').value = 'add_offer';
            document.getElementById('offer-id').value = '';
            document.getElementById('offer-modal').classList.remove('hidden');
        }
        
        function editOffer(offerId) {
            // Load offer data and populate form
            fetch('?action=get_offer&offer_id=' + offerId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const offer = data.offer;
                        document.getElementById('offer-modal-title').textContent = 'Edit Special Offer';
                        document.getElementById('offer-form').querySelector('[name="action"]').value = 'update_offer';
                        document.getElementById('offer-id').value = offer.id;
                        document.getElementById('offer-title').value = offer.title;
                        document.getElementById('offer-icon').value = offer.icon;
                        document.getElementById('offer-description').value = offer.description;
                        document.getElementById('offer-badge').value = offer.badge;
                        document.getElementById('offer-note').value = offer.note;
                        document.getElementById('offer-discount-type').value = offer.discount_type;
                        document.getElementById('offer-discount-value').value = offer.discount_value;
                        document.getElementById('offer-target-audience').value = offer.target_audience;
                        document.getElementById('offer-valid-until').value = offer.valid_until;
                        document.getElementById('offer-max-usage').value = offer.max_usage;
                        document.getElementById('offer-is-active').checked = offer.is_active;
                        document.getElementById('offer-modal').classList.remove('hidden');
                    }
                });
        }
        
        function closeOfferModal() {
            document.getElementById('offer-modal').classList.add('hidden');
        }
        
        function deleteOffer(offerId) {
            if (confirm('Are you sure you want to delete this offer?')) {
                const formData = new FormData();
                formData.append('action', 'delete_offer');
                formData.append('offer_id', offerId);
                
                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Offer deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
        
        // Reward Modal Functions
        function openAddRewardModal() {
            document.getElementById('reward-modal-title').textContent = 'Add Available Reward';
            document.getElementById('reward-form').reset();
            document.getElementById('reward-form').querySelector('[name="action"]').value = 'add_reward';
            document.getElementById('reward-id').value = '';
            document.getElementById('reward-modal').classList.remove('hidden');
        }
        
        function editReward(rewardId) {
            // Load reward data and populate form
            fetch('?action=get_reward&reward_id=' + rewardId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const reward = data.reward;
                        document.getElementById('reward-modal-title').textContent = 'Edit Available Reward';
                        document.getElementById('reward-form').querySelector('[name="action"]').value = 'update_reward';
                        document.getElementById('reward-id').value = reward.id;
                        document.getElementById('reward-title').value = reward.title;
                        document.getElementById('reward-icon').value = reward.icon;
                        document.getElementById('reward-description').value = reward.description;
                        document.getElementById('reward-required-points').value = reward.required_points;
                        document.getElementById('reward-tier').value = reward.tier;
                        document.getElementById('reward-is-active').checked = reward.is_active;
                        document.getElementById('reward-modal').classList.remove('hidden');
                    }
                });
        }
        
        function closeRewardModal() {
            document.getElementById('reward-modal').classList.add('hidden');
        }
        
        function deleteReward(rewardId) {
            if (confirm('Are you sure you want to delete this reward?')) {
                const formData = new FormData();
                formData.append('action', 'delete_reward');
                formData.append('reward_id', rewardId);
                
                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reward deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
        
        // Promo Code Modal Functions
        function openAddPromoModal() {
            document.getElementById('promo-modal-title').textContent = 'Add Promo Code';
            document.getElementById('promo-form').reset();
            document.getElementById('promo-form').querySelector('[name="action"]').value = 'add_promo';
            document.getElementById('promo-code').value = '';
            document.getElementById('promo-modal').classList.remove('hidden');
        }
        
        function editPromo(code) {
            // Load promo data and populate form
            fetch('?action=get_promo&code=' + code)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const promo = data.promo;
                        document.getElementById('promo-modal-title').textContent = 'Edit Promo Code';
                        document.getElementById('promo-form').querySelector('[name="action"]').value = 'update_promo';
                        document.getElementById('promo-code').value = promo.code;
                        document.getElementById('promo-code-input').value = promo.code;
                        document.getElementById('promo-title').value = promo.title;
                        document.getElementById('promo-description').value = promo.description;
                        document.getElementById('promo-discount-type').value = promo.discount_type;
                        document.getElementById('promo-discount-value').value = promo.discount_value;
                        document.getElementById('promo-valid-until').value = promo.valid_until;
                        document.getElementById('promo-max-usage').value = promo.max_usage;
                        document.getElementById('promo-is-active').checked = promo.is_active;
                        document.getElementById('promo-modal').classList.remove('hidden');
                    }
                });
        }
        
        function closePromoModal() {
            document.getElementById('promo-modal').classList.add('hidden');
        }
        
        function deletePromo(code) {
            if (confirm('Are you sure you want to delete this promo code?')) {
                const formData = new FormData();
                formData.append('action', 'delete_promo');
                formData.append('code', code);
                
                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Promo code deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
        
        // Form submissions
        document.getElementById('offer-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Offer saved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
        
        document.getElementById('reward-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reward saved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
        
        document.getElementById('promo-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Promo code saved successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        });
        
        // Utility functions
        function exportReport() {
            alert('Export functionality will be implemented here');
        }
        
        function exportUserData() {
            alert('User data export functionality will be implemented here');
        }
        
        function adjustUserPoints(userId, currentPoints) {
            const newPoints = prompt('Enter new points value:', currentPoints);
            if (newPoints !== null && !isNaN(newPoints)) {
                alert('Points adjustment functionality will be implemented here');
            }
        }
        
        function viewUserHistory(userId) {
            alert('User history functionality will be implemented here');
        }
        
        function sendUserReward(userId) {
            alert('Send reward functionality will be implemented here');
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
?> 