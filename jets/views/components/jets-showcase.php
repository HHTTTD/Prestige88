<?php
function renderJetsShowcase($jets) {
    ob_start();
    ?>
    <div class="mt-12">
        <div class="glass-effect rounded-2xl">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8 flex items-center justify-center">
                <i class="mr-3"></i>
                คลังเครื่องบินเจทส่วนตัว
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($jets as $jet): ?>
                    <?php $isAvailable = ($jet['status'] ?? 'available') === 'available'; ?>
                    <div class="bg-gray-100 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:scale-105 relative">
                        <div class="h-48 bg-gray-200 relative overflow-hidden">
                            <?php if (!empty($jet['image'])): ?>
                                <img src="<?php echo htmlspecialchars($jet['image']); ?>" alt="<?php echo htmlspecialchars($jet['model'] ?? ''); ?>" class="w-full h-full object-cover opacity-80">
                            <?php endif; ?>
                            <div class="absolute top-4 right-4">
                                <?php if ($isAvailable): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Available
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-500">
                                        <i class="fas fa-times-circle mr-1"></i>Unavailable
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($jet['model'] ?? ''); ?></h3>
                            <div class="grid grid-cols-2 gap-4 mb-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-blue-600"></i>
                                    <span><?php echo $jet['capacity'] ?? 0; ?> ที่นั่ง</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-tachometer-alt mr-2 text-red-600"></i>
                                    <span><?php echo number_format($jet['max_speed'] ?? 0); ?> km/h</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-globe mr-2 text-green-600"></i>
                                    <span><?php echo number_format($jet['range_km'] ?? 0); ?> km</span>
                                </div>
                                <div class="flex items-center font-semibold text-purple-600">
                                    <i class="fas fa-dollar-sign mr-2 text-[#d4af37]"></i>
                                    <span class="text-[#d4af37]"><?php echo number_format($jet['price_per_hour'], 2); ?>/hr</span>
                                </div>
                            </div>
                            <?php if (!empty($jet['amenities'])): ?>
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-gem mr-1 text-yellow-600"></i>สิ่งอำนวยความสะดวก:
                                    </h4>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($jet['amenities'] as $amenity): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?php echo htmlspecialchars($amenity); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($isAvailable): ?>
                                <a href="?page=booking&jet_id=<?php echo $jet['id']; ?>" class="mt-4 block w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-calendar-check mr-2"></i>จองเครื่องบินลำนี้
                                </a>
                            <?php else: ?>
                                <a href="#" class="mt-4 block w-full bg-gray-300 text-gray-500 font-semibold py-3 px-4 rounded-lg text-center cursor-not-allowed" tabindex="-1" aria-disabled="true" onclick="return false;">
                                    <i class="fas fa-calendar-times mr-2"></i>ไม่พร้อมให้จอง
                                </a>
                            <?php endif; ?>
                        </div>
                        <!-- Modal ฟอร์มจอง -->
                        <div id="jet-booking-modal-<?php echo htmlspecialchars($jet['id']); ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
                            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-xl relative flex flex-col gap-2 animate-fadein overflow-y-auto max-h-[50vh] min-h-[400px] pb-8">
                                <button onclick="document.getElementById('jet-booking-modal-<?php echo htmlspecialchars($jet['id']); ?>').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl"><i class="fas fa-times"></i></button>
                                <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">จอง <?php echo htmlspecialchars($jet['model']); ?></h2>
                                <form method="POST" class="space-y-5">
                                    <input type="hidden" name="action" value="create_booking">
                                    <input type="hidden" name="jet_id" value="<?php echo htmlspecialchars($jet['id']); ?>">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-map-marker-alt text-green-600"></i>จุดออกเดินทาง</label>
                                        <input type="text" name="departure_location" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น สนามบินสุวรรณภูมิ">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-map-marker-alt text-red-600"></i>จุดหมายปลายทาง</label>
                                        <input type="text" name="arrival_location" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น สนามบินภูเก็ต">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-calendar text-blue-600"></i>วันที่เดินทาง</label>
                                            <input type="date" name="departure_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-clock text-blue-600"></i>เวลาเดินทาง</label>
                                            <input type="time" name="departure_time" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-users text-purple-600"></i>จำนวนผู้โดยสาร</label>
                                            <input type="number" name="passengers" min="1" max="<?php echo $jet['capacity']; ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-hourglass-half text-orange-600"></i>ระยะเวลาบิน (ชม.)</label>
                                            <input type="number" name="flight_hours" min="0.5" step="0.5" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="2.5">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-star text-yellow-600"></i>ความต้องการพิเศษ</label>
                                        <textarea name="special_requests" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น อาหารพิเศษ, การประชุม, ฉลองวันเกิด"></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg text-lg flex items-center justify-center gap-2">
                                        <i class="fas fa-paper-plane"></i> ส่งคำขอจอง
                                    </button>
                                </form>
                            </div>
                        </div>
                        <script>
                        // ปิด modal เมื่อกด ESC
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape') {
                                var modal = document.getElementById('jet-booking-modal-<?php echo htmlspecialchars($jet['id']); ?>');
                                if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
                            }
                        });
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderJetCard($jet) {
    ob_start();
    $modalId = 'jet-booking-modal-' . htmlspecialchars($jet['id']);
    ?>
    <div class="bg-gray-100 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition duration-300 transform hover:scale-105 relative">
        <div class="h-48 bg-gray-200 relative overflow-hidden">
            <img src="<?php echo htmlspecialchars($jet['image']); ?>" 
                 alt="<?php echo htmlspecialchars($jet['model']); ?>"
                 class="w-full h-full object-cover opacity-80">
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>Available
                </span>
            </div>
        </div>
        <div class="p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($jet['model']); ?></h3>
            <div class="grid grid-cols-2 gap-4 mb-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <i class="fas fa-users mr-2 text-blue-600"></i>
                    <span><?php echo $jet['capacity']; ?> ที่นั่ง</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-tachometer-alt mr-2 text-red-600"></i>
                    <span><?php echo number_format($jet['max_speed']); ?> km/h</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-globe mr-2 text-green-600"></i>
                    <span><?php echo number_format($jet['range_km']); ?> km</span>
                </div>
                <div class="flex items-center font-semibold text-purple-600">
                    <i class="fas fa-dollar-sign mr-2 text-[#d4af37]"></i>
                    <span class="text-[#d4af37]"><?php echo number_format($jet['price_per_hour'], 2); ?>/hr</span>
                </div>
            </div>
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-gem mr-1 text-yellow-600"></i>สิ่งอำนวยความสะดวก:
                </h4>
                <div class="flex flex-wrap gap-1">
                    <?php foreach ($jet['amenities'] as $amenity): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?php echo htmlspecialchars($amenity); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (isset($jet['booking_url'])): ?>
            <a href="<?php echo htmlspecialchars($jet['booking_url']); ?>" 
               class="mt-4 block w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 px-4 rounded-lg text-center transition duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-calendar-check mr-2"></i>จองเครื่องบินลำนี้
            </a>
            <?php else: ?>
            <a href="?page=booking&jet_id=<?php echo $jet['id']; ?>" 
               class="mt-4 block w-full bg-gradient-to-r bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-calendar-check mr-2"></i>จองเครื่องบินลำนี้
            </a>
            <?php endif; ?>
        </div>
        <!-- Modal ฟอร์มจอง -->
        <div id="<?php echo $modalId; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" onclick="if(event.target===this)this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-xl relative flex flex-col gap-2 animate-fadein overflow-y-auto max-h-[50vh] min-h-[400px] pb-8">
                <button onclick="document.getElementById('<?php echo $modalId; ?>').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl"><i class="fas fa-times"></i></button>
                <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">จอง <?php echo htmlspecialchars($jet['model']); ?></h2>
                <form method="POST" class="space-y-5"></form>
                    <input type="hidden" name="action" value="create_booking">
                    <input type="hidden" name="jet_id" value="<?php echo htmlspecialchars($jet['id']); ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-map-marker-alt text-green-600"></i>จุดออกเดินทาง</label>
                        <input type="text" name="departure_location" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น สนามบินสุวรรณภูมิ">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-map-marker-alt text-red-600"></i>จุดหมายปลายทาง</label>
                        <input type="text" name="arrival_location" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น สนามบินภูเก็ต">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-calendar text-blue-600"></i>วันที่เดินทาง</label>
                            <input type="date" name="departure_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-clock text-blue-600"></i>เวลาเดินทาง</label>
                            <input type="time" name="departure_time" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-users text-purple-600"></i>จำนวนผู้โดยสาร</label>
                            <input type="number" name="passengers" min="1" max="<?php echo $jet['capacity']; ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-hourglass-half text-orange-600"></i>ระยะเวลาบิน (ชม.)</label>
                            <input type="number" name="flight_hours" min="0.5" step="0.5" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-2"><i class="fas fa-star text-yellow-600"></i>ความต้องการพิเศษ</label>
                        <textarea name="special_requests" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="เช่น อาหารพิเศษ, การประชุม, ฉลองวันเกิด"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl transition duration-200 transform hover:scale-105 shadow-lg text-lg flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> ส่งคำขอจอง
                    </button>
                </form>
            </div>
        </div>
        <script>
        // ปิด modal เมื่อกด ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('<?php echo $modalId; ?>');
                if (modal && !modal.classList.contains('hidden')) modal.classList.add('hidden');
            }
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}