<?php
function renderPopularDestinations($destinations) {
    ob_start();
    ?>
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Popular Destinations</h2>
        <div class="relative">
            <!-- Left Arrow -->
            <button class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-gray-600 hover:text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition-all duration-200 opacity-0 group-hover:opacity-100" onclick="scrollDestinations('left')">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <!-- Right Arrow -->
            <button class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-gray-600 hover:text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition-all duration-200 opacity-0 group-hover:opacity-100" onclick="scrollDestinations('right')">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="group">
                <div id="destinations-container" class="flex gap-6 overflow-x-auto pb-5 pt-5 hide-scrollbar scroll-smooth" style="scroll-behavior: smooth;">
                    <?php foreach ($destinations as $dest): ?>
                    <div class="bg-white rounded-2xl shadow-md w-56 flex-shrink-0 flex flex-col items-center p-4 cursor-pointer hover:shadow-lg transition-all duration-200 transform hover:scale-105" 
                         onclick="searchDestination('<?php echo htmlspecialchars($dest['name']); ?>')">
                        <img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" class="w-40 h-28 object-cover rounded-xl mb-2" />
                        <div class="text-center">
                            <div class="font-semibold text-base"><?php echo htmlspecialchars($dest['name']); ?></div>
                            <div class="text-sm text-gray-500 font-medium">From $<?php echo htmlspecialchars($dest['price']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Drag scroll styles */
        .drag-scroll {
            cursor: grab;
        }
        .drag-scroll:active {
            cursor: grabbing;
        }
        
        /* Prevent text selection during drag */
        #destinations-container {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        #destinations-container * {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        </style>
        
        <script>
        // Drag scroll functionality
        let isDown = false;
        let startX;
        let scrollLeft;
        
        const container = document.getElementById('destinations-container');
        
        container.addEventListener('mousedown', (e) => {
            isDown = true;
            container.classList.add('drag-scroll');
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });
        
        container.addEventListener('mouseleave', () => {
            isDown = false;
            container.classList.remove('drag-scroll');
        });
        
        container.addEventListener('mouseup', () => {
            isDown = false;
            container.classList.remove('drag-scroll');
        });
        
        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2;
            container.scrollLeft = scrollLeft - walk;
        });
        
        // Arrow scroll functionality
        function scrollDestinations(direction) {
            const container = document.getElementById('destinations-container');
            const scrollAmount = 300; // Adjust scroll amount as needed
            
            if (direction === 'left') {
                container.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else {
                container.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        }
        
        // Show/hide arrows based on scroll position
        container.addEventListener('scroll', () => {
            const leftArrow = document.querySelector('button[onclick="scrollDestinations(\'left\')"]');
            const rightArrow = document.querySelector('button[onclick="scrollDestinations(\'right\')"]');
            
            if (container.scrollLeft <= 0) {
                leftArrow.style.opacity = '0';
            } else {
                leftArrow.style.opacity = '1';
            }
            
            if (container.scrollLeft >= container.scrollWidth - container.clientWidth) {
                rightArrow.style.opacity = '0';
            } else {
                rightArrow.style.opacity = '1';
            }
        });
        
        // Initialize arrow visibility
        window.addEventListener('load', () => {
            const leftArrow = document.querySelector('button[onclick="scrollDestinations(\'left\')"]');
            const rightArrow = document.querySelector('button[onclick="scrollDestinations(\'right\')"]');
            
            if (container.scrollLeft <= 0) {
                leftArrow.style.opacity = '0';
            }
        });
        
        // Search destination function
        function searchDestination(destinationName) {
            // Navigate to search page with destination pre-filled
            window.location.href = `?page=search&to=${encodeURIComponent(destinationName)}`;
        }
        </script>
    </div>
    <?php
    return ob_get_clean();
} 