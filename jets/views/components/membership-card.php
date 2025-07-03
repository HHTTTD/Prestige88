<?php
function renderMembershipCard($currentUser, $bookings) {
    if ($currentUser['role'] !== 'client') return '';
    
    $userTier = $currentUser['membership_tier'] ?? 'silver';
    $tiers = Membership::getTiers();
    $tierInfo = $tiers[$userTier] ?? $tiers['silver'];
    
    // Get user statistics
    $userBookings = array_filter($bookings, function($booking) use ($currentUser) {
        return $booking['user_id'] === $currentUser['id'] && $booking['status'] === 'confirmed';
    });
    $totalBookings = count($userBookings);
    $totalSpending = array_sum(array_column($userBookings, 'total_cost'));
    
    // Calculate progress to next tier
    $nextTier = null;
    $progress = 0;
    $progressDetails = [];
    $tierOrder = ['silver', 'gold', 'platinum', 'black'];
    $currentIndex = array_search($userTier, $tierOrder);
    
    if ($currentIndex !== false && $currentIndex < count($tierOrder) - 1) {
        $nextTierKey = $tierOrder[$currentIndex + 1];
        $nextTier = $tiers[$nextTierKey];
        
        // คำนวณความคืบหน้าแยกตามเงื่อนไข
        $bookingProgress = 0;
        $spendingProgress = 0;
        
        if ($nextTier['min_bookings'] > 0) {
            $bookingProgress = ($totalBookings / $nextTier['min_bookings']) * 100;
        }
        
        if ($nextTier['min_spending'] > 0) {
            $spendingProgress = ($totalSpending / $nextTier['min_spending']) * 100;
        }
        
        // ใช้ค่าสูงสุดระหว่างสองเงื่อนไข (เพราะเป็น OR condition)
        $progress = max($bookingProgress, $spendingProgress);
        $progress = min($progress, 100);
        
        $progressDetails = [
            'booking_progress' => min($bookingProgress, 100),
            'spending_progress' => min($spendingProgress, 100),
            'booking_target' => $nextTier['min_bookings'],
            'spending_target' => $nextTier['min_spending'],
            'bookings_needed' => max(0, $nextTier['min_bookings'] - $totalBookings),
            'spending_needed' => max(0, $nextTier['min_spending'] - $totalSpending)
        ];
    }
    
    ob_start();
    ?>
    <div class="xl:col-span-3 mb-6">
        <div class="<?php echo $userTier === 'black' ? 'bg-gradient-to-r from-gray-900 to-black text-white' : 'bg-gradient-to-r from-'.$tierInfo['color'].' to-'.$tierInfo['color'].'-600 text-white'; ?> rounded-2xl shadow-xl p-8 relative overflow-hidden">
            
            <!-- Background Animation -->
            <div class="absolute inset-0 opacity-10">
                <div class="floating-circles">
                    <div class="circle circle-1"></div>
                    <div class="circle circle-2"></div>
                    <div class="circle circle-3"></div>
                </div>
            </div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <div class="flex items-center mb-2">
                            <i class="fas <?php echo $tierInfo['icon']; ?> text-3xl mr-4 animate-pulse"></i>
                            <div>
                                <h2 class="text-2xl font-bold"><?php echo $tierInfo['name_th']; ?></h2>
                                <p class="text-sm opacity-90"><?php echo $tierInfo['name']; ?></p>
                            </div>
                        </div>
                        <?php if ($tierInfo['discount'] > 0): ?>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-sm font-medium backdrop-blur-sm">
                            <i class="fas fa-percentage mr-2"></i>
                            ส่วนลด <?php echo $tierInfo['discount']; ?>% ทุกการจอง
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-right">
                        <div class="text-2xl font-bold counter" data-target="<?php echo $totalBookings; ?>">0</div>
                        <div class="text-sm opacity-90">การจองทั้งหมด</div>
                        <div class="text-lg font-semibold mt-2 counter" data-target="<?php echo number_format($totalSpending / 1000000, 1); ?>">0</div>
                        <div class="text-xs opacity-90">ล้านบาท ใช้จ่ายรวม</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <h4 class="font-semibold mb-2">
                            <i class="fas fa-star mr-2"></i>สิทธิประโยชน์
                        </h4>
                        <ul class="text-sm space-y-1 opacity-90">
                            <?php foreach (array_slice($tierInfo['benefits'], 0, 3) as $benefit): ?>
                            <li class="benefit-item"><i class="fas fa-check mr-2 text-green-300"></i><?php echo $benefit; ?></li>
                            <?php endforeach; ?>
                            <?php if (count($tierInfo['benefits']) > 3): ?>
                            <li class="text-xs"><i class="fas fa-plus mr-2"></i>และอีก <?php echo count($tierInfo['benefits']) - 3; ?> รายการ</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <?php if ($nextTier): ?>
                    <div class="md:col-span-2">
                        <h4 class="font-semibold mb-4">
                            <i class="fas fa-arrow-up mr-2"></i>เส้นทางสู่ <?php echo $nextTier['name_th']; ?>
                        </h4>
                        
                        <!-- Overall Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span>ความคืบหน้ารวม</span>
                                <span class="font-semibold">
                                    <span id="progress-percentage">0</span>%
                                </span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-3 shadow-inner">
                                <div id="main-progress-bar" 
                                     class="bg-gradient-to-r from-green-400 to-green-500 h-3 rounded-full transition-all duration-1000 ease-out shadow-lg relative overflow-hidden"
                                     style="width: 0%"
                                     data-target="<?php echo $progress; ?>">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detailed Progress -->
                        <div class="space-y-3">
                            <!-- Booking Progress -->
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span>การจอง: <?php echo $totalBookings; ?> / <?php echo $progressDetails['booking_target']; ?></span>
                                    <span id="booking-percentage">0%</span>
                                </div>
                                <div class="w-full bg-white/15 rounded-full h-2">
                                    <div id="booking-progress-bar" 
                                         class="bg-gradient-to-r from-green-300 to-green-400 h-2 rounded-full transition-all duration-800"
                                         style="width: 0%"
                                         data-target="<?php echo $progressDetails['booking_progress']; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Spending Progress -->
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span>ยอดใช้จ่าย: <?php echo number_format($totalSpending / 1000000, 1); ?>M / <?php echo number_format($progressDetails['spending_target'] / 1000000, 1); ?>M บาท</span>
                                    <span id="spending-percentage">0%</span>
                                </div>
                                <div class="w-full bg-white/15 rounded-full h-2">
                                    <div id="spending-progress-bar" 
                                         class="bg-gradient-to-r from-green-300 to-green-400 h-2 rounded-full transition-all duration-800"
                                         style="width: 0%"
                                         data-target="<?php echo $progressDetails['spending_progress']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Requirements -->
                        <div class="text-xs opacity-90 mt-3 space-y-1">
                            <p><strong>เงื่อนไข:</strong> <?php echo $nextTier['requirements']; ?></p>
                            <?php if ($progressDetails['bookings_needed'] > 0): ?>
                            <p><i class="fas fa-calendar mr-1"></i>ต้องจองเพิ่มอีก <span class="font-semibold text-green-300"><?php echo $progressDetails['bookings_needed']; ?></span> ครั้ง</p>
                            <?php endif; ?>
                            <?php if ($progressDetails['spending_needed'] > 0): ?>
                            <p><i class="fas fa-baht-sign mr-1"></i>ต้องใช้จ่ายเพิ่มอีก <span class="font-semibold text-green-300"><?php echo number_format($progressDetails['spending_needed']); ?></span> บาท</p>
                            <?php endif; ?>
                            <?php if ($progress >= 100): ?>
                            <p class="text-green-300 font-semibold animate-pulse">
                                <i class="fas fa-star mr-1"></i>คุณมีคุณสมบัติครบแล้ว! ระบบจะอัพเกรดอัตโนมัติในการจองถัดไป
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="md:col-span-2 text-center">
                        <i class="fas fa-trophy text-4xl mb-2 opacity-75 animate-bounce"></i>
                        <p class="text-lg font-semibold">คุณอยู่ในระดับสูงสุดแล้ว!</p>
                        <p class="text-sm opacity-90">ขอบคุณที่เป็นลูกค้า VIP ของเรา</p>
                        
                        <!-- Achievement Badges -->
                        <div class="flex justify-center space-x-2 mt-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-300 text-xs">
                                <i class="fas fa-crown mr-1"></i>Elite Member
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs">
                                <i class="fas fa-gem mr-1"></i>Premium
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Animation Styles */
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .animate-shimmer {
        animation: shimmer 2s infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        33% { transform: translateY(-10px) rotate(120deg); }
        66% { transform: translateY(5px) rotate(240deg); }
    }
    
    .floating-circles {
        position: absolute;
        width: 100%;
        height: 100%;
    }
    
    .circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: float 6s ease-in-out infinite;
    }
    
    .circle-1 {
        width: 60px;
        height: 60px;
        top: 20%;
        left: 10%;
        animation-delay: 0s;
    }
    
    .circle-2 {
        width: 40px;
        height: 40px;
        top: 60%;
        right: 15%;
        animation-delay: 2s;
    }
    
    .circle-3 {
        width: 80px;
        height: 80px;
        bottom: 10%;
        left: 60%;
        animation-delay: 4s;
    }
    
    .benefit-item {
        transition: all 0.3s ease;
    }
    
    .benefit-item:hover {
        transform: translateX(5px);
        color: #86efac;
    }
    
    /* Progress bar glow effect */
    #main-progress-bar {
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
    }
    
    /* Counter animation */
    .counter {
        transition: all 0.3s ease;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .circle {
            display: none;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter Animation
        function animateCounter(element, target, suffix = '') {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = suffix ? current.toFixed(1) + suffix : Math.floor(current);
            }, 20);
        }
        
        // Progress Bar Animation
        function animateProgressBar(element, targetPercent) {
            if (!element) return;
            
            let currentPercent = 0;
            const increment = targetPercent / 50;
            const timer = setInterval(() => {
                currentPercent += increment;
                if (currentPercent >= targetPercent) {
                    currentPercent = targetPercent;
                    clearInterval(timer);
                }
                element.style.width = currentPercent + '%';
            }, 30);
        }
        
        // Start animations after a short delay
        setTimeout(() => {
            // Animate counters
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseFloat(counter.dataset.target);
                const suffix = counter.textContent.includes('ล้าน') ? '' : '';
                animateCounter(counter, target, suffix);
            });
            
            // Animate main progress bar
            const mainProgressBar = document.getElementById('main-progress-bar');
            const mainTarget = parseFloat(mainProgressBar?.dataset.target || 0);
            if (mainProgressBar) {
                animateProgressBar(mainProgressBar, mainTarget);
                
                // Update percentage text
                const percentageElement = document.getElementById('progress-percentage');
                if (percentageElement) {
                    let currentPercent = 0;
                    const increment = mainTarget / 50;
                    const timer = setInterval(() => {
                        currentPercent += increment;
                        if (currentPercent >= mainTarget) {
                            currentPercent = mainTarget;
                            clearInterval(timer);
                        }
                        percentageElement.textContent = currentPercent.toFixed(1);
                    }, 30);
                }
            }
            
            // Animate detailed progress bars
            const bookingProgressBar = document.getElementById('booking-progress-bar');
            const spendingProgressBar = document.getElementById('spending-progress-bar');
            
            if (bookingProgressBar) {
                const bookingTarget = parseFloat(bookingProgressBar.dataset.target);
                setTimeout(() => {
                    animateProgressBar(bookingProgressBar, bookingTarget);
                    
                    // Update booking percentage
                    const bookingPercentElement = document.getElementById('booking-percentage');
                    if (bookingPercentElement) {
                        let current = 0;
                        const increment = bookingTarget / 30;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= bookingTarget) {
                                current = bookingTarget;
                                clearInterval(timer);
                            }
                            bookingPercentElement.textContent = current.toFixed(1) + '%';
                        }, 25);
                    }
                }, 300);
            }
            
            if (spendingProgressBar) {
                const spendingTarget = parseFloat(spendingProgressBar.dataset.target);
                setTimeout(() => {
                    animateProgressBar(spendingProgressBar, spendingTarget);
                    
                    // Update spending percentage
                    const spendingPercentElement = document.getElementById('spending-percentage');
                    if (spendingPercentElement) {
                        let current = 0;
                        const increment = spendingTarget / 30;
                        const timer = setInterval(() => {
                            current += increment;
                            if (current >= spendingTarget) {
                                current = spendingTarget;
                                clearInterval(timer);
                            }
                            spendingPercentElement.textContent = current.toFixed(1) + '%';
                        }, 25);
                    }
                }, 600);
            }
            
        }, 500);
        
        // Auto-refresh progress (สำหรับการอัพเดทแบบ real-time)
        setInterval(() => {
            // สามารถเพิ่ม AJAX call เพื่อดึงข้อมูลใหม่
            const progressBars = document.querySelectorAll('[id$="-progress-bar"]');
            progressBars.forEach(bar => {
                // เพิ่ม glow effect เมื่อมีการอัพเดท
                bar.style.boxShadow = '0 0 15px rgba(34, 197, 94, 0.8)';
                setTimeout(() => {
                    bar.style.boxShadow = '0 0 10px rgba(34, 197, 94, 0.5)';
                }, 500);
            });
        }, 10000); // ทุก 10 วินาที
        
        // Hover effects
        const membershipCard = document.querySelector('.xl\\:col-span-3');
        if (membershipCard) {
            membershipCard.addEventListener('mouseenter', () => {
                const circles = document.querySelectorAll('.circle');
                circles.forEach(circle => {
                    circle.style.animationDuration = '3s';
                });
            });
            
            membershipCard.addEventListener('mouseleave', () => {
                const circles = document.querySelectorAll('.circle');
                circles.forEach(circle => {
                    circle.style.animationDuration = '6s';
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
?>