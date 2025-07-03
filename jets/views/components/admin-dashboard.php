<?php
function renderAdminDashboard($bookings, $currentUser) {
    $totalBookings = count($bookings);
    $pendingBookings = count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; }));
    $confirmedBookings = count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }));
    $totalRevenue = array_sum(array_column(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }), 'total_cost'));
    
    ob_start();
    ?>
    <div class="mt-12">
        <div class="glass-effect rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                Dashboard สถิติ
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">การจองทั้งหมด</p>
                            <p class="text-3xl font-bold"><?php echo $totalBookings; ?></p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-full">
                            <i class="fas fa-calendar-check text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm">รอดำเนินการ</p>
                            <p class="text-3xl font-bold"><?php echo $pendingBookings; ?></p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-full">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">ยืนยันแล้ว</p>
                            <p class="text-3xl font-bold"><?php echo $confirmedBookings; ?></p>
                        </div>
                        <div class="p-3 bg-white/20 rounded-full">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ตารางรายละเอียดการจอง -->
            <div class="mt-10">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-plane mr-2 text-blue-600"></i>คำขอจองที่รอดำเนินการ</h3>
                <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-xl shadow border">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">เครื่องบิน</th>
                            <th class="py-3 px-4 text-left">วันที่บิน</th>
                            <th class="py-3 px-4 text-left">ลูกค้า</th>
                            <th class="py-3 px-4 text-left">สถานะ</th>
                            <th class="py-3 px-4 text-left">อนุมัติ/ปฏิเสธ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach ($bookings as $b): if (($b['status'] ?? '') !== 'pending') continue; ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo $i++; ?></td>
                            <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($b['jet_model'] ?? '-'); ?></td>
                            <td class="py-3 px-4"><?php echo isset($b['departure_date']) ? date('d/m/Y', strtotime($b['departure_date'])) : '-'; ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($b['user_name'] ?? '-'); ?></td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>รอดำเนินการ
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold mr-2"><i class="fas fa-check mr-1"></i>อนุมัติ</button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold"><i class="fas fa-times mr-1"></i>ปฏิเสธ</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}