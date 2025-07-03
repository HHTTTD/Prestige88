<?php

if (!function_exists('renderNavbar')) {
function renderNavbar($currentUser) {
        $notifications = [];
        $unreadCount = 0;
        if (isset($currentUser['id'])) {
            require_once 'models/Notification.php';
            $notifications = Notification::getForUser($currentUser['id']);
            $unreadCount = 0; 
            foreach($notifications as $n) {
                if (!$n['is_read']) $unreadCount++;
            }
        }
        
    ob_start();
    ?>
        <nav class="bg-[#a39786] shadow-xl fixed top-0 left-0 w-full z-50" x-data="{}">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <!-- Hamburger Icon (Mobile/Tablet) -->
                    <button id="hamburger-btn" class="mr-4 focus:outline-none">
                        <i class="fas fa-bars text-white text-2xl"></i>
                    </button>
                        <a href="?" class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-plane text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">prestige88</h1>
                        <p class="text-white text-sm">Private Jet Booking System</p>
                    </div>
                        </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notification Icon -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-white hover:text-gray-200 focus:outline-none relative p-2">
                                <i class="fas fa-bell text-xl"></i>
                                <?php if ($unreadCount > 0): ?>
                                <span class="absolute top-1 right-1 flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                </span>
                                <?php endif; ?>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                                 class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl overflow-hidden z-20 border">
                                <div class="py-3 px-4 flex justify-between items-center border-b">
                                    <h3 class="text-gray-700 font-semibold">Notifications</h3>
                                    <?php if ($unreadCount > 0): ?>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full"><?php echo $unreadCount; ?> New</span>
                                    <?php endif; ?>
                                </div>
                                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto" id="notification-list">
                                    <?php if (empty($notifications)): ?>
                                        <p class="text-gray-500 text-sm text-center py-10">You have no notifications.</p>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $notification): ?>
                                            <a href="<?php echo htmlspecialchars($notification['link']); ?>"
                                               class="block p-4 notification-item transition-colors <?php echo !$notification['is_read'] ? 'bg-blue-50 hover:bg-blue-100' : 'hover:bg-gray-50'; ?>"
                                               data-id="<?php echo htmlspecialchars($notification['id']); ?>">
                                                <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <p class="text-xs text-gray-500 mt-1.5">
                                                    <i class="far fa-clock mr-1"></i>
                                                    <?php 
                                                    $date = new DateTime($notification['created_at']);
                                                    echo $date->format('M d, Y \a\t H:i'); 
                                                    ?>
                                                </p>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($notifications)): ?>
                                 <div class="py-2 px-4 border-t bg-gray-50">
                                     <button class="text-sm text-blue-600 hover:underline w-full text-center py-1 focus:outline-none" id="mark-all-read-btn">
                                         Mark all as read
                    </button>
                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <!-- Side Drawer (Hamburger Menu) -->
            <div id="side-drawer" class="fixed top-0 left-0 h-full w-80 bg-white shadow-2xl z-[60] transform -translate-x-full transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <!-- Profile Header -->
                <div class="px-6 pt-8 pb-6 bg-gradient-to-br from-[#2c3744] to-[#a39786] rounded-br-3xl relative">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-full bg-white/30 flex items-center justify-center text-3xl font-bold text-[#2c3744] shadow border-2 border-white">
                            <?php echo strtoupper(mb_substr(trim($currentUser['full_name']), 0, 1, 'UTF-8')); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-white text-lg font-bold truncate leading-tight"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                                <a href="?page=profile" class="px-2 py-0.5 text-xs rounded-lg bg-white/30 text-white font-semibold hover:bg-white/50 transition whitespace-nowrap" style="backdrop-filter: blur(2px); text-decoration:none;">Edit Profile</a>
                            </div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-white/80 text-xs font-medium">
                                          <?php 
                                            $displayRole = ($currentUser['role'] === 'client') ? 'User' : ucfirst($currentUser['role']);
                                            echo $displayRole;
                                          ?>
                                </span>
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-400/90 text-[#2c3744]">
                                    <i class="fas fa-star mr-1"></i> Travel Points:
                                    <span class="ml-1">
                                             <?php 
                                                 if (function_exists('calculateUserPoints')) {
                                            echo number_format(calculateUserPoints($currentUser['id']));
                                        } else {
                                            echo '0';
                                                 } 
                                              ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <button id="close-drawer" class="ml-auto text-white text-2xl focus:outline-none absolute top-4 right-2"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                    <div class="flex-1 bg-white/95 px-6 py-4 flex flex-col gap-2 overflow-y-auto">
                    <a href="?" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-home"></i> Home</a>
                        <a href="?page=search" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-search"></i> Search Jets</a>
                    <a href="?page=bookings" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-list-alt"></i> My Bookings</a>
                    <a href="?page=my-tickets" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-ticket-alt"></i> My Tickets</a>
                    <a href="?page=profile" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-user"></i> Profile</a>
                    <a href="?page=offers" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-gift"></i> Offers & Rewards</a>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="?page=database" class="flex items-center gap-3 py-2 text-gray-800 hover:text-[#a39786] font-medium"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <?php endif; ?>
                </div>
                    <div class="px-6 pt-2 pb-6">
                    <a href="?logout=1" class="w-full block text-center border border-red-400 text-red-500 rounded-xl py-2 font-semibold hover:bg-red-50 transition"><i class="fas fa-sign-out-alt mr-1"></i> Sign Out</a>
                </div>
            </div>
        </div>
        <!-- Overlay -->
            <div id="drawer-overlay" class="fixed inset-0 bg-black/40 z-[59] hidden"></div>
        <script>
        // Hamburger menu logic
            document.addEventListener('DOMContentLoaded', function () {
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sideDrawer = document.getElementById('side-drawer');
        const drawerOverlay = document.getElementById('drawer-overlay');
        const closeDrawerBtn = document.getElementById('close-drawer');
        function openDrawer() {
                    if (sideDrawer) sideDrawer.classList.remove('-translate-x-full');
                    if (drawerOverlay) drawerOverlay.classList.remove('hidden');
        }
        function closeDrawer() {
                    if (sideDrawer) sideDrawer.classList.add('-translate-x-full');
                    if (drawerOverlay) drawerOverlay.classList.add('hidden');
        }
                if (hamburgerBtn) hamburgerBtn.addEventListener('click', openDrawer);
                if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
                if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);
            });

            // Notification click logic
            document.addEventListener('DOMContentLoaded', () => {
                const notificationList = document.getElementById('notification-list');
                if (notificationList) {
                    notificationList.addEventListener('click', (e) => {
                        const item = e.target.closest('.notification-item');
                        if (item && !item.dataset.handled) {
                            item.dataset.handled = 'true'; // Prevent double handling
                            const notificationId = item.dataset.id;
                            const formData = new FormData();
                            formData.append('action', 'mark_notification_read');
                            formData.append('notification_id', notificationId);
                            
                            fetch('index.php', { method: 'POST', body: formData })
                                .then(res => {
                                    if (!res.ok) { console.error('Failed to mark notification as read.'); }
                                    // Let the link navigation proceed
                                })
                                .catch(err => console.error('Notification fetch error:', err));
                        }
                    });
                }

                const markAllReadBtn = document.getElementById('mark-all-read-btn');
                if(markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const formData = new FormData();
                        formData.append('action', 'mark_all_notifications_read');
                        fetch('index.php', { method: 'POST', body: formData })
                            .then(res => res.json())
                            .then(data => {
                                if(data.success) {
                                    window.location.reload();
                                } else {
                                    alert('Could not mark all notifications as read.');
                                }
                            })
                            .catch(() => alert('An error occurred.'));
                    });
                }
            });
        </script>
    </nav>
    <?php
    return ob_get_clean();
    }
}