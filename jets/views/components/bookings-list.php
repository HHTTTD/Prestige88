<?php

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '$' . number_format($amount, 2);
    }
}

function renderBookingsList($bookings, $currentUser) {
    ob_start();
    ?>
    <div class="glass-effect rounded-2xl shadow-xl">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-alt mr-3 text-green-600"></i>
                    <?php echo $currentUser['role'] === 'admin' ? 'การจองทั้งหมด' : 'การจองของคุณ'; ?>
                </h2>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-calendar-check mr-1"></i>
                    ทั้งหมด <?php echo count($bookings); ?> รายการ
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <?php if (empty($bookings)): ?>
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">ยังไม่มีการจอง</p>
                <p class="text-gray-400">เริ่มต้นการเดินทางสุดหรูของคุณกันเถอะ!</p>
            </div>
            <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($bookings as $booking): ?>
                    <?php echo renderBookingCard($booking, $currentUser); ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderBookingCard($booking, $currentUser) {
    ob_start();
    ?>
    <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition duration-200 bg-white/50">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-plane mr-2 text-blue-600"></i>
                    <?php echo htmlspecialchars($booking['jet_model']); ?>
                </h3>
                <p class="text-sm text-gray-600">
                    ID: <?php echo substr($booking['id'], 0, 8); ?>... • 
                    <?php if ($currentUser['role'] === 'admin'): ?>
                    ลูกค้า: <?php echo htmlspecialchars($booking['user_name']); ?>
                    <?php else: ?>
                    จองเมื่อ: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="flex items-center space-x-3">
                <?php echo renderBookingStatus($booking['status']); ?>
                <?php echo renderBookingActions($booking, $currentUser); ?>
            </div>
        </div>
        
        <?php echo renderBookingDetails($booking); ?>
        
        <?php if (!empty($booking['special_requests'])): ?>
        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
            <div class="text-sm text-gray-700">
                <i class="fas fa-star mr-2 text-yellow-600"></i>
                <strong>ความต้องการพิเศษ:</strong> 
                <?php echo htmlspecialchars($booking['special_requests']); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function renderBookingStatus($status) {
    $statusConfig = [
        'pending' => ['bg-yellow-100 text-yellow-800', 'fa-clock', 'รอดำเนินการ'],
        'confirmed' => ['bg-green-100 text-green-800', 'fa-check-circle', 'ยืนยันแล้ว'],
        'cancelled' => ['bg-red-100 text-red-800', 'fa-times-circle', 'ยกเลิกแล้ว'],
        'completed' => ['bg-blue-100 text-blue-800', 'fa-flag-checkered', 'เสร็จสิ้น']
    ];
    
    $config = $statusConfig[$status] ?? ['bg-gray-100 text-gray-800', 'fa-question-circle', $status];
    
    return sprintf(
        '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium %s">
            <i class="fas %s mr-1"></i>%s
        </span>',
        $config[0], $config[1], $config[2]
    );
}

function renderBookingActions($booking, $currentUser) {
    ob_start();
    
    // ตรวจสอบว่ามีการส่งข้อมูลมาครบถ้วนหรือไม่
    $bookingId = $booking['id'] ?? '';
    $bookingStatus = $booking['status'] ?? '';
    $userRole = $currentUser['role'] ?? '';
    
    if ($userRole === 'admin' && $bookingStatus === 'pending'): ?>
        <div class="flex space-x-2">
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" 
                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg transition duration-200"
                        title="อนุมัติการจอง">
                    <i class="fas fa-check"></i>
                </button>
            </form>
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" 
                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition duration-200"
                        title="ปฏิเสธการจอง"
                        onclick="return confirm('คุณต้องการปฏิเสธการจองนี้หรือไม่?')">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
    <?php endif;
    
    if ($bookingStatus === 'pending' || $bookingStatus === 'confirmed'): ?>
        <form method="POST" class="inline">
            <input type="hidden" name="action" value="cancel_booking">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingId); ?>">
            <button type="submit" 
                    class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs rounded-lg transition duration-200"
                    title="ยกเลิกการจอง"
                    onclick="return confirm('คุณต้องการยกเลิกการจองนี้หรือไม่?')">
                <i class="fas fa-ban"></i>
            </button>
        </form>
    <?php endif;
    
    return ob_get_clean();
}

function renderBookingDetails($booking) {
    ob_start();
    
    // ตรวจสอบและกำหนดค่าเริ่มต้นสำหรับข้อมูลที่อาจไม่มี
    $departureLocation = $booking['departure_location'] ?? 'ไม่ระบุ';
    $arrivalLocation = $booking['arrival_location'] ?? 'ไม่ระบุ';
    $departureDate = $booking['departure_date'] ?? '';
    $departureTime = $booking['departure_time'] ?? '';
    $passengers = $booking['passengers'] ?? 0;
    $flightHours = $booking['flight_hours'] ?? 0;
    $totalCost = $booking['total_cost'] ?? 0;
    $baseTotal = $booking['base_total'] ?? 0;
    $membershipDiscount = $booking['membership_discount'] ?? 0;
    
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        <div class="flex items-center text-sm text-gray-600">
            <i class="fas fa-route mr-2 text-green-600"></i>
            <span><?php echo htmlspecialchars($departureLocation); ?> 
            → <?php echo htmlspecialchars($arrivalLocation); ?></span>
        </div>
        
        <div class="flex items-center text-sm text-gray-600">
            <i class="fas fa-calendar mr-2 text-blue-600"></i>
            <span>
                <?php if ($departureDate): ?>
                    <?php echo date('d/m/Y', strtotime($departureDate)); ?>
                    <?php if ($departureTime): ?>
                        เวลา <?php echo date('H:i', strtotime($departureTime)); ?> น.
                    <?php endif; ?>
                <?php else: ?>
                    ไม่ระบุวันที่
                <?php endif; ?>
            </span>
        </div>
        
        <div class="flex items-center text-sm text-gray-600">
            <i class="fas fa-users mr-2 text-purple-600"></i>
            <span><?php echo $passengers; ?> ผู้โดยสาร</span>
        </div>
        
        <div class="flex items-center text-sm text-gray-600">
            <i class="fas fa-clock mr-2 text-orange-600"></i>
            <span><?php echo $flightHours; ?> ชั่วโมง</span>
        </div>
        
        <div class="flex items-center text-sm font-semibold text-green-600">
            <i class="fas fa-baht-sign mr-2"></i>
            <div>
                <?php if ($baseTotal > 0 && $membershipDiscount > 0): ?>
                <div class="text-xs text-gray-500 line-through">
                    <?php echo formatCurrency($baseTotal); ?>
                </div>
                <div class="flex items-center">
                    <span><?php echo formatCurrency($totalCost); ?></span>
                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">
                        -<?php echo $membershipDiscount; ?>%
                    </span>
                </div>
                <?php else: ?>
                <span><?php echo formatCurrency($totalCost); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// เพิ่มฟังก์ชันสำหรับการแสดงรายละเอียดการจองแบบ modal
function renderBookingModal($booking) {
    ob_start();
    ?>
    <div id="booking-modal-<?php echo $booking['id']; ?>" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        รายละเอียดการจอง #<?php echo substr($booking['id'], 0, 8); ?>
                    </h3>
                    <button onclick="closeModal('booking-modal-<?php echo $booking['id']; ?>')" 
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">ข้อมูลการเดินทาง</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>เครื่องบิน:</strong> <?php echo htmlspecialchars($booking['jet_model'] ?? ''); ?></p>
                            <p><strong>จาก:</strong> <?php echo htmlspecialchars($booking['departure_location'] ?? ''); ?></p>
                            <p><strong>ไป:</strong> <?php echo htmlspecialchars($booking['arrival_location'] ?? ''); ?></p>
                            <p><strong>วันที่:</strong> <?php echo $booking['departure_date'] ? date('d/m/Y', strtotime($booking['departure_date'])) : 'ไม่ระบุ'; ?></p>
                            <p><strong>เวลา:</strong> <?php echo $booking['departure_time'] ? date('H:i', strtotime($booking['departure_time'])) : 'ไม่ระบุ'; ?> น.</p>
                            <p><strong>ที่นั่ง:</strong> <?php echo htmlspecialchars($booking['seat'] ?? '-'); ?></p>
                            <p><strong>รถบัส:</strong> <?php echo htmlspecialchars($booking['bus_number'] ?? '-'); ?></p>
                            <p><strong>ประตูขึ้นเครื่อง:</strong> <?php echo htmlspecialchars($booking['boarding_gate'] ?? '-'); ?></p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">ข้อมูลการจอง</h4>
                        <div class="space-y-2 text-sm">
                            <p><strong>ผู้โดยสาร:</strong> <?php echo htmlspecialchars($booking['user_name'] ?? ''); ?></p>
                            <p><strong>จำนวน:</strong> <?php echo $booking['passengers'] ?? 0; ?> ท่าน</p>
                            <p><strong>ชั่วโมงบิน:</strong> <?php echo $booking['flight_hours'] ?? 0; ?> ชั่วโมง</p>
                            <p><strong>ราคา:</strong> <?php echo formatCurrency($booking['total_cost'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($booking['special_requests'])): ?>
                <div class="mt-4 pt-4 border-t">
                    <h4 class="font-semibold text-gray-700 mb-2">ความต้องการพิเศษ</h4>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-6 flex justify-end">
                    <button onclick="closeModal('booking-modal-<?php echo $booking['id']; ?>')"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
    </script>
    <?php
    return ob_get_clean();
}

function renderUpcomingTickets($bookings) {
    ob_start();
    ?>
    <div class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-100">Upcoming Tickets</h2>
            <button class="flex items-center gap-2 border border-[#a89c8a] text-[#a89c8a] px-4 py-1 rounded-xl hover:bg-[#a89c8a]/10 transition">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
        <div class="flex flex-col gap-8">
            <?php foreach ($bookings as $i => $booking): ?>
            <?php
                $status = strtolower($booking['status'] ?? '');
                $statusMap = [
                    'confirmed' => ['bg-[#a89c8a]/90 text-white', 'Confirmed'],
                    'pending' => ['bg-[#a89c8a]/90 text-white', 'Pending'],
                    'cancelled' => ['bg-[#a89c8a]/90 text-white', 'Cancelled'],
                    'completed' => ['bg-[#a89c8a]/90 text-white', 'Completed'],
                ];
                $statusStyle = $statusMap[$status][0] ?? 'bg-gray-300 text-gray-700';
                $statusLabel = $statusMap[$status][1] ?? ucfirst($status);
                $modalId = 'ticket-modal-' . ($booking['id'] ?? $i);
            ?>
            <div class="flex rounded-3xl overflow-hidden shadow-xl bg-white" style="min-height: 170px;">
                <div class="w-20 bg-[#a89c8a]/90 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col justify-between p-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-xl font-bold mb-1">
                                <?php echo htmlspecialchars($booking['departure_location']); ?>
                                <span class="mx-2 text-lg">→</span>
                                <?php echo htmlspecialchars($booking['arrival_location']); ?>
                            </div>
                            <div class="flex items-center text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                <?php echo date('M d,Y', strtotime($booking['departure_date'])); ?>
                            </div>
                            <div class="font-bold text-base mb-1">Ticket #<?php echo $booking['id']; ?></div>
                            <div class="text-xs text-gray-400 mb-2">Premium Seat $45.00</div>
                            <button type="button" onclick="document.getElementById('<?php echo $modalId; ?>').classList.remove('hidden')" class="flex items-center gap-2 bg-[#a89c8a]/90 text-white px-4 py-2 rounded-xl mt-2 text-base font-medium hover:bg-[#a89c8a] transition">
                                <i class="fas fa-qrcode"></i> Show Ticket
                            </button>
                        </div>
                        <div class="flex flex-col items-end gap-4 h-full">
                            <span class="inline-flex items-center px-6 py-2 rounded-xl text-base font-medium <?php echo $statusStyle; ?> mb-2"><?php echo $statusLabel; ?></span>
                            <div class="flex items-center text-gray-700 text-base gap-2"><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($booking['departure_time'] ?? '10:30')); ?></div>
                            <div class="text-right text-base text-gray-700">Seat <b><?php echo htmlspecialchars($booking['seat'] ?? '12A'); ?></b></div>
                            <button class="flex items-center gap-2 bg-[#a89c8a]/90 text-white px-4 py-2 rounded-xl mt-2 text-base font-medium hover:bg-[#a89c8a] transition"><i class="fas fa-share-alt"></i> Share</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Ticket Modal -->
            <div id="<?php echo $modalId; ?>" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
                <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
                    <button onclick="document.getElementById('<?php echo $modalId; ?>').classList.add('hidden')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                    <h3 class="text-lg font-semibold mb-4">Ticket Details</h3>
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-64 h-64 bg-[#a89c8a] flex items-center justify-center rounded-md mb-4 text-center text-black text-lg">
                            <?php if (!empty($booking['qr_code']) && file_exists($booking['qr_code'])): ?>
                                <img src="<?php echo htmlspecialchars($booking['qr_code']); ?>" alt="QR CODE" style="width:220px;height:220px;object-fit:contain;border-radius:12px;background:#fff;" />
                            <?php else: ?>
                                QR CODE
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm text-gray-800">
                        <div class="flex justify-between">
                            <span class="font-semibold">Passenger:</span>
                            <span><?php echo htmlspecialchars($booking['user_name'] ?? '-'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Ticket Number:</span>
                            <span><?php echo htmlspecialchars($booking['id']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Date:</span>
                            <span><?php echo date('M d, Y', strtotime($booking['departure_date'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Departure:</span>
                            <span><?php echo htmlspecialchars($booking['departure_location']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Arrival:</span>
                            <span><?php echo htmlspecialchars($booking['arrival_location']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Seat:</span>
                            <span><?php echo htmlspecialchars($booking['seat'] ?? '12A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Bus Number:</span>
                            <span><?php echo htmlspecialchars($booking['bus_number'] ?? '-'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Boarding Gate:</span>
                            <span><?php echo htmlspecialchars($booking['boarding_gate'] ?? '-'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Boarding Time:</span>
                            <span><?php echo date('h:i A', strtotime($booking['departure_time'] ?? '10:30')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}