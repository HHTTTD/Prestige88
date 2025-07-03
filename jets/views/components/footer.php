<?php
function renderFooter($currentUser) {
    // ตรวจสอบข้อมูลผู้ใช้ก่อน
    $userName = $currentUser['full_name'] ?? 'ไม่ระบุ';
    $userRole = $currentUser['role'] ?? 'guest';
    
    ob_start();
    ?>
    <div class="text-center mt-12 text-gray-500">
        <div class="glass-effect rounded-xl p-6">
            <p class="flex items-center justify-center mb-2">
                <i class="fas fa-plane mr-2"></i>
                prestige88 Private Jet Booking System | Made with PHP + Tailwind CSS
            </p>
            <p class="text-sm">
                Current User: <strong><?php echo htmlspecialchars($userName); ?></strong> 
                (<?php echo ucfirst($userRole); ?>)
            </p>
            <div class="mt-4 text-xs text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> prestige88. All rights reserved.</p>
                <p>Version 1.0.0 | Last updated: <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// เพิ่มฟังก์ชันสำหรับ footer แบบง่าย (ถ้าไม่มีข้อมูลผู้ใช้)
function renderSimpleFooter() {
    ob_start();
    ?>
    <div class="text-center mt-12 text-gray-500">
        <div class="glass-effect rounded-xl p-6">
            <p class="flex items-center justify-center mb-2">
                <i class="fas fa-plane mr-2"></i>
                prestige88 Private Jet Booking System
            </p>
            <p class="text-sm">
                Made with using PHP + Tailwind CSS
            </p>
            <div class="mt-4 text-xs text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> prestige88. All rights reserved.</p>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Footer Navbar (Mobile)
if (!function_exists('renderFooterNavbar')) {
    function renderFooterNavbar($active = 'home') {
        $tabs = [
            [
                'id' => 'home',
                'label' => 'Home',
                'icon' => 'fa-home',
                'href' => '?',
            ],
            [
                'id' => 'search',
                'label' => 'Search',
                'icon' => 'fa-search',
                'href' => '?page=search',
            ],
            [
                'id' => 'tickets',
                'label' => 'My Tickets',
                'icon' => 'fa-ticket-alt',
                'href' => '?page=my-tickets',
            ],
            [
                'id' => 'offers',
                'label' => 'Offers',
                'icon' => 'fa-gift',
                'href' => '?page=offers',
            ],
            [
                'id' => 'profile',
                'label' => 'Profile',
                'icon' => 'fa-user',
                'href' => '?page=profile',
            ],
        ];
        ob_start();
        ?>
        <nav class="fixed bottom-0 left-0 right-0 z-40 flex flex-col items-center">
          <div id="footer-navbar-container" class="w-full flex flex-col items-center transition-transform duration-500" style="transform: translateY(0);">
            <!-- Toggle Drag Bar -->
            <div id="footer-navbar-toggle" class="w-full flex justify-center -mb-3 z-50 relative">
              <button onclick="toggleFooterNavbar()" class="w-16 h-2.5 bg-gray-300 hover:bg-[#a89c8a]/40 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center mt-2 mb-1 border border-[#e5e0d8]" style="outline:none;border-width:2px;"></button>
            </div>
            <!-- Footer Navbar -->
            <div id="footer-navbar" class="bg-white/80 backdrop-blur-md rounded-t-2xl shadow-2xl border-t-4 border-[#e5cfa3] flex justify-around items-center py-2 px-1 w-full md:max-w-lg" style="min-height:68px; margin-bottom:env(safe-area-inset-bottom,0);">
              <?php foreach ($tabs as $tab): ?>
                  <a href="<?php echo $tab['href']; ?>" class="flex flex-col items-center justify-center flex-1 group transition-all duration-200 <?php echo $active === $tab['id'] ? 'text-[#a89c8a] font-semibold' : 'text-gray-500'; ?>">
                      <i class="fas <?php echo $tab['icon']; ?> text-2xl mb-1 transition-all duration-200 <?php echo $active === $tab['id'] ? 'text-[#a89c8a]' : 'text-gray-400 group-hover:text-[#a89c8a]'; ?>"></i>
                      <span class="text-xs transition-all duration-200 <?php echo $active === $tab['id'] ? 'text-[#a89c8a]' : 'text-gray-400 group-hover:text-[#a89c8a]'; ?>"><?php echo $tab['label']; ?></span>
                      <?php if ($active === $tab['id']): ?>
                          <span class="block w-2 h-2 bg-[#a89c8a] rounded-full mt-1"></span>
                      <?php endif; ?>
                  </a>
              <?php endforeach; ?>
            </div>
          </div>
        </nav>
        <script>
        let footerNavbarOpen = true;
        function toggleFooterNavbar() {
            const container = document.getElementById('footer-navbar-container');
            if (footerNavbarOpen) {
                container.style.transform = 'translateY(calc(100% - 24px))';
            } else {
                container.style.transform = 'translateY(0)';
            }
            footerNavbarOpen = !footerNavbarOpen;
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
?>