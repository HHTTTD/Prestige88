<?php

if (!function_exists('renderManageJetsPage')) {
    function renderManageJetsPage($jets) {
        // ฟังก์ชันตรวจสอบ available slots ที่ว่างอยู่จริง
        function getAvailableSlotsCount($jet) {
            if (empty($jet['available_slots'])) {
                return 0;
            }
            
            $bookings = Database::loadBookings();
            $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
                return $booking['jet_id'] === $jet['id'] && 
                       in_array($booking['status'], ['pending', 'confirmed']);
            });
            
            $availableCount = 0;
            foreach ($jet['available_slots'] as $slot) {
                $isBooked = false;
                foreach ($confirmedBookings as $booking) {
                    if ($booking['departure_date'] === $slot['date'] && 
                        $booking['departure_location'] === $slot['departure']) {
                        $isBooked = true;
                        break;
                    }
                }
                if (!$isBooked) {
                    $availableCount++;
                }
            }
            
            return $availableCount;
        }
        
        // ฟังก์ชันเรียงลำดับ available slots ตามวันที่
        function sortAvailableSlots($slots) {
            if (empty($slots)) {
                return [];
            }
            
            usort($slots, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
            return $slots;
        }
        
        // ฟังก์ชันกรอง available slots ที่ว่างอยู่จริง
        function getAvailableSlotsForJet($jet) {
            if (empty($jet['available_slots'])) {
                return [];
            }
            
            $bookings = Database::loadBookings();
            $confirmedBookings = array_filter($bookings, function($booking) use ($jet) {
                return $booking['jet_id'] === $jet['id'] && 
                       in_array($booking['status'], ['pending', 'confirmed']);
            });
            
            $availableSlots = [];
            foreach ($jet['available_slots'] as $slot) {
                $isBooked = false;
                foreach ($confirmedBookings as $booking) {
                    if ($booking['departure_date'] === $slot['date'] && 
                        $booking['departure_location'] === $slot['departure']) {
                        $isBooked = true;
                        break;
                    }
                }
                if (!$isBooked) {
                    $availableSlots[] = $slot;
                }
            }
            
            // เรียงลำดับตามวันที่ (วันที่ใกล้สุดอยู่บน)
            return sortAvailableSlots($availableSlots);
        }
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-plane text-[#a39786]"></i>
                    Manage Jets
                </h2>
                <button onclick="openAddJetModal()" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Add New Jet
                </button>
            </div>
            
            <!-- Success/Error Messages -->
            <div id="message-container"></div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 text-left">Model</th>
                            <th class="px-4 py-3 text-left">Capacity</th>
                            <th class="px-4 py-3 text-left">Price/Hour</th>
                            <th class="px-4 py-3 text-left">Available Slots</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($jets)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-plane-slash text-4xl mb-2"></i>
                                    <p>No jets found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jets as $jet): ?>
                            <?php 
                            $availableSlots = getAvailableSlotsForJet($jet);
                            $availableSlotsCount = count($availableSlots);
                            $totalSlots = count($jet['available_slots'] ?? []);
                            ?>
                            <tr class="hover:bg-gray-50" data-jet-id="<?php echo htmlspecialchars($jet['id']); ?>">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-lg object-cover" src="<?php echo htmlspecialchars($jet['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($jet['model']); ?>">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($jet['model']); ?></div>
                                            <div class="text-sm text-gray-500">ID: <?php echo htmlspecialchars($jet['id']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $jet['capacity']; ?> ที่นั่ง
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    $<?php echo number_format($jet['price_per_hour']); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($availableSlotsCount > 0): ?>
                                        <div class="text-xs text-gray-700">
                                            <?php foreach (array_slice($availableSlots, 0, 3) as $slot): ?>
                                                <div>
                                                    <?php echo htmlspecialchars(($slot['departure'] ?? '-'). ' → ' . ($slot['arrival'] ?? '-')); ?> |
                                                    <?php echo htmlspecialchars($slot['date'] ?? '-'); ?>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($availableSlots) > 3): ?>
                                                <div class="text-gray-400 italic">+<?php echo count($availableSlots) - 3; ?> more</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-xs font-bold" style="color: #dc2626;">Fully Booked</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-0 py-3">
                                    <select class="status-dropdown px-2 py-1 rounded border border-gray-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#a39786]"
                                            data-jet-id="<?php echo htmlspecialchars($jet['id']); ?>"
                                            style="min-width: 110px; color: <?php echo ($jet['status'] ?? 'available') === 'available' ? '#16a34a' : '#dc2626'; ?>; background-color: <?php echo ($jet['status'] ?? 'available') === 'available' ? '#f0fdf4' : '#fef2f2'; ?>;">
                                        <option value="available" <?php if (($jet['status'] ?? 'available') === 'available') echo 'selected'; ?>>Available</option>
                                        <option value="unavailable" <?php if (($jet['status'] ?? '') === 'unavailable') echo 'selected'; ?>>Unavailable</option>
                                    </select>
                                    <span class="status-loading hidden text-xs text-gray-400 ml-2"><i class="fas fa-spinner fa-spin"></i></span>
                                </td>
                                <td class="px-4 py-3 flex items-center gap-3">
                                    <button onclick="editJet('<?php echo htmlspecialchars($jet['id']); ?>')" class="text-blue-600 hover:text-blue-800" title="Edit Jet">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewSlots('<?php echo htmlspecialchars($jet['id']); ?>')" class="text-green-600 hover:text-green-800" title="View Available Slots">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                    <button onclick="deleteJet('<?php echo htmlspecialchars($jet['id']); ?>')" class="text-red-600 hover:text-red-800" title="Delete Jet">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Jet Modal -->
        <div id="jet-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto overflow-x-hidden" style="border-radius: 1rem;">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800" id="modal-title">Add New Jet</h3>
                    <button onclick="closeJetModal()" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <form id="jet-form" class="space-y-6">
                    <input type="hidden" name="action" value="add_jet">
                    <input type="hidden" name="jet_id" id="edit-jet-id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jet Model *</label>
                            <input type="text" name="model" id="jet-model" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                   placeholder="e.g., Gulfstream G650">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                            <input type="url" name="image" id="jet-image" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                   placeholder="https://example.com/jet-image.jpg">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
                            <input type="number" name="capacity" id="jet-capacity" min="1" max="20" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                   placeholder="e.g., 8">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Speed (km/h) *</label>
                            <input type="number" name="max_speed" id="jet-max-speed" min="100" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                   placeholder="e.g., 950">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Range (km) *</label>
                            <input type="number" name="range_km" id="jet-range" min="100" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                   placeholder="e.g., 7000">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price per Hour ($) *</label>
                        <input type="number" name="price_per_hour" id="jet-price" min="100" step="100" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                               placeholder="e.g., 5000">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                        <textarea name="amenities" id="jet-amenities" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]"
                                  placeholder="Enter amenities separated by commas (e.g., WiFi, Bar, Conference Room, Bedroom)"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Available Slots</label>
                        <div id="slots-list" class="space-y-3"></div>
                        <button type="button" onclick="addSlot()" class="mt-4 px-5 py-2 bg-gradient-to-r from-[#a39786] to-[#b3a89c] text-white rounded-lg text-base font-semibold shadow hover:from-[#b3a89c] hover:to-[#a39786] transition flex items-center gap-2"><i class="fas fa-plus"></i> Add Slot</button>
                        <input type="hidden" name="available_slots" id="available-slots-json">
                        <p class="text-xs text-gray-500 mt-2">Add each available flight slot. You can leave arrival blank for 'destination of your choice'.</p>
                    </div>
                    
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i>
                            <span id="submit-text">Add Jet</span>
                        </button>
                        <button type="button" onclick="closeJetModal()" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Slots Modal -->
        <div id="slots-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-4xl relative">
                <button onclick="closeSlotsModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <h3 id="slots-modal-title" class="text-lg font-semibold mb-4">Available Slots</h3>
                <div id="slots-content" class="space-y-4">
                    <!-- Dynamic content will be loaded here -->
                </div>
            </div>
        </div>

        <script>
        const jetsData = <?php echo json_encode($jets); ?>;

        let slots = [];

        function renderSlots() {
            const list = document.getElementById('slots-list');
            list.innerHTML = '';
            if (slots.length === 0) {
                list.innerHTML = '<div class="text-gray-400 text-sm mb-2">No slots. Click Add Slot.</div>';
            }
            slots.forEach((slot, idx) => {
                const row = document.createElement('div');
                row.className = 'flex flex-col md:flex-row md:items-end md:gap-4 gap-2 w-full bg-white/90 border border-gray-200 rounded-xl shadow-sm p-4 relative group hover:shadow-lg transition mb-1';
                row.innerHTML = `
                    <div class="flex flex-col flex-1 min-w-[120px] md:max-w-[160px]">
                        <label class="text-xs text-gray-500 mb-1 flex items-center gap-1"><i class='fas fa-calendar-alt'></i> Date</label>
                        <input type="date" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" value="${slot.date || ''}" onchange="updateSlot(${idx}, 'date', this.value)">
                    </div>
                    <div class="flex flex-col flex-1 min-w-[100px] md:max-w-[130px]">
                        <label class="text-xs text-gray-500 mb-1 flex items-center gap-1"><i class='fas fa-clock'></i> Departure Time</label>
                        <input type="time" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" value="${slot.departure_time || ''}" onchange="updateSlot(${idx}, 'departure_time', this.value)">
                    </div>
                    <div class="flex flex-col flex-1 min-w-[120px] md:max-w-[160px]">
                        <label class="text-xs text-gray-500 mb-1 flex items-center gap-1"><i class='fas fa-plane-departure'></i> Departure</label>
                        <input type="text" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" placeholder="Departure" value="${slot.departure || ''}" onchange="updateSlot(${idx}, 'departure', this.value)">
                    </div>
                    <div class="flex flex-col flex-1 min-w-[120px] md:max-w-[160px]">
                        <label class="text-xs text-gray-500 mb-1 flex items-center gap-1"><i class='fas fa-plane-arrival'></i> Arrival (optional)</label>
                        <input type="text" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" placeholder="Arrival (optional)" value="${slot.arrival || ''}" onchange="updateSlot(${idx}, 'arrival', this.value)">
                    </div>
                    <div class="flex flex-col flex-1 min-w-[100px] md:max-w-[130px]">
                        <label class="text-xs text-gray-500 mb-1 flex items-center gap-1"><i class='fas fa-clock'></i> Arrival Time (optional)</label>
                        <input type="time" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#a39786] focus:border-[#a39786]" value="${slot.arrival_time || ''}" onchange="updateSlot(${idx}, 'arrival_time', this.value)">
                    </div>
                    <button type="button" onclick="removeSlot(${idx})" class="absolute top-2 right-2 shadow rounded-full w-6 h-6 flex items-center justify-center bg-red-100 hover:bg-red-200 transition border-none opacity-80 group-hover:opacity-100" title="Remove Slot"><i class="fas fa-trash-alt text-xs text-red-600"></i></button>
                `;
                list.appendChild(row);
            });
            document.getElementById('available-slots-json').value = JSON.stringify(slots);
        }

        function addSlot() {
            slots.push({date:'', departure:'', arrival:'', departure_time:'', arrival_time:''});
            renderSlots();
        }
        function removeSlot(idx) {
            slots.splice(idx, 1);
            renderSlots();
        }
        function updateSlot(idx, key, value) {
            slots[idx][key] = value;
            renderSlots();
        }

        // On edit: load slots from textarea/json
        function loadSlotsFromJson(json) {
            try {
                const arr = JSON.parse(json);
                if (Array.isArray(arr)) {
                    slots = arr.map(slot => ({
                        date: slot.date || '',
                        departure: slot.departure || '',
                        arrival: slot.arrival || '',
                        departure_time: slot.departure_time || '',
                        arrival_time: slot.arrival_time || ''
                    }));
                }
            } catch(e) { slots = []; }
            renderSlots();
        }

        function openAddJetModal() {
            document.getElementById('modal-title').textContent = 'Add New Jet';
            document.getElementById('submit-text').textContent = 'Add Jet';
            document.querySelector('input[name="action"]').value = 'add_jet';
            document.getElementById('edit-jet-id').value = '';
            document.getElementById('jet-form').reset();
            document.getElementById('jet-modal').classList.remove('hidden');
            slots = [];
            renderSlots();
        }

        function editJet(jetId) {
            const jet = jetsData.find(j => j.id === jetId);
            if (!jet) return;

            document.getElementById('modal-title').textContent = 'Edit Jet';
            document.getElementById('submit-text').textContent = 'Update Jet';
            document.querySelector('input[name="action"]').value = 'update_jet';
            document.getElementById('edit-jet-id').value = jet.id;
            document.getElementById('jet-model').value = jet.model || '';
            document.getElementById('jet-image').value = jet.image || '';
            document.getElementById('jet-capacity').value = jet.capacity || '';
            document.getElementById('jet-max-speed').value = jet.max_speed || '';
            document.getElementById('jet-range').value = jet.range_km || '';
            document.getElementById('jet-price').value = jet.price_per_hour || '';
            document.getElementById('jet-amenities').value = Array.isArray(jet.amenities) ? jet.amenities.join(', ') : '';
            // Load slots from jet data (pre-populate for editing)
            loadSlotsFromJson(JSON.stringify(jet.available_slots || []));
            document.getElementById('jet-modal').classList.remove('hidden');
        }

        function viewSlots(jetId) {
            const jet = jetsData.find(j => j.id === jetId);
            if (!jet) return;
            
            document.getElementById('slots-modal-title').textContent = `Available Slots - ${jet.model}`;
            
            const slotsContent = document.getElementById('slots-content');
            if (!jet.available_slots || jet.available_slots.length === 0) {
                slotsContent.innerHTML = '<p class="text-gray-500">No available slots configured for this jet.</p>';
            } else {
                // เรียงลำดับ slots ตามวันที่ (วันที่ใกล้สุดอยู่บน)
                const sortedSlots = [...jet.available_slots].sort((a, b) => new Date(a.date) - new Date(b.date));
                
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
                sortedSlots.forEach((slot, index) => {
                    const date = new Date(slot.date + 'T00:00:00');
                    const formattedDate = date.toLocaleDateString('en-GB', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    html += `
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="font-semibold text-gray-800">Slot ${index + 1}</div>
                            <div class="text-sm text-gray-600 mt-2">
                                <div><strong>Date:</strong> ${formattedDate}</div>
                                <div><strong>Departure:</strong> ${slot.departure || 'Not specified'}</div>
                                <div><strong>Arrival:</strong> ${slot.arrival || 'Not specified'}</div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                slotsContent.innerHTML = html;
            }
            
            document.getElementById('slots-modal').classList.remove('hidden');
        }

        function closeJetModal() {
            document.getElementById('jet-modal').classList.add('hidden');
        }

        function closeSlotsModal() {
            document.getElementById('slots-modal').classList.add('hidden');
        }

        function showMessage(message, type) {
            const container = document.getElementById('message-container');
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            
            container.innerHTML = `
                <div class="border px-4 py-3 rounded mb-4 ${alertClass}">
                    ${message}
                </div>
            `;
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Form submission
        document.getElementById('jet-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            // Parse amenities
            const amenitiesText = formData.get('amenities');
            if (amenitiesText) {
                const amenities = amenitiesText.split(',').map(item => item.trim()).filter(item => item);
                formData.set('amenities', JSON.stringify(amenities));
            }
            
            // Parse available slots
            const slotsText = formData.get('available_slots');
            if (slotsText) {
                try {
                    const slots = JSON.parse(slotsText);
                    formData.set('available_slots', JSON.stringify(slots));
                } catch (e) {
                    showMessage('Invalid JSON format for available slots', 'error');
                    return;
                }
            }
            
            document.getElementById('available-slots-json').value = JSON.stringify(slots);
            
            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        closeJetModal();
                        // Reload page to show updated data
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showMessage('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
        });

        function deleteJet(jetId) {
            if (!confirm('Are you sure you want to delete this jet? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_jet');
            formData.append('jet_id', jetId);

            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Jet deleted successfully!', 'success');
                        // Remove the row from the table
                        const row = document.querySelector(`tr[data-jet-id="${jetId}"]`);
                        if (row) row.remove();
                    } else {
                        showMessage('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.status-dropdown').forEach(function(dropdown) {
                dropdown.addEventListener('change', function() {
                    const jetId = this.dataset.jetId;
                    const newStatus = this.value;
                    const row = this.closest('tr');
                    const loading = row.querySelector('.status-loading');
                    loading.classList.remove('hidden');
                    this.disabled = true;
                    const formData = new FormData();
                    formData.append('action', 'update_jet_status');
                    formData.append('jet_id', jetId);
                    formData.append('status', newStatus);
                    fetch('', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            loading.classList.add('hidden');
                            this.disabled = false;
                            if (data.success) {
                                if (newStatus === 'available') {
                                    this.style.color = '#16a34a';
                                    this.style.backgroundColor = '#f0fdf4';
                                } else {
                                    this.style.color = '#dc2626';
                                    this.style.backgroundColor = '#fef2f2';
                                }
                            } else {
                                alert('Error: ' + (data.message || 'Failed to update status'));
                            }
                        })
                        .catch(() => {
                            loading.classList.add('hidden');
                            this.disabled = false;
                            alert('An error occurred.');
                        });
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
?> 