<?php
if (!function_exists('renderUpcomingTickets')) {
    function renderUpcomingTickets($bookings, $currentUser) {
        ob_start();
        ?>
        <div class="space-y-8" id="tickets-container">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-16 text-white">
                    <i class="fas fa-ticket-alt text-6xl opacity-50 mb-4"></i>
                    <p class="text-xl">You have no upcoming tickets.</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white/10 rounded-2xl p-6 text-white backdrop-blur-sm ticket-card" data-booking-id="<?php echo htmlspecialchars($booking['id']); ?>">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-2xl font-bold tracking-wider">
                                    <?php echo htmlspecialchars($booking['departure_location']); ?>
                                    <i class="fas fa-plane mx-4"></i>
                                    <?php echo htmlspecialchars($booking['arrival_location']); ?>
                                </div>
                                <div class="text-sm opacity-80 mt-2">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <?php echo date('D, M j, Y', strtotime($booking['departure_date'])); ?>
                                </div>
                                <div class="text-sm opacity-80">
                                    Ticket #<?php echo htmlspecialchars($booking['id']); ?>
                                </div>
                            </div>
                            <div class="text-center">
                                <span class="inline-block px-4 py-1 bg-green-500/80 rounded-full text-xs font-semibold uppercase tracking-wider">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                                <div class="mt-2 text-lg font-semibold"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></div>
                                <div class="text-sm opacity-80">Seat <?php echo htmlspecialchars($booking['seat'] ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/20 flex justify-end gap-4">
                             <button class="show-ticket-btn bg-white/20 hover:bg-white/30 px-6 py-2 rounded-lg text-sm font-medium transition-colors">Show Ticket</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Ticket Modal -->
        <div id="ticket-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative text-gray-800">
                <button onclick="closeTicketModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <h3 class="text-2xl font-bold mb-6 text-center">Ticket Details</h3>

                <div id="ticket-modal-content">
                    <!-- Dynamic content will be injected here -->
                </div>

                <div id="ticket-modal-form" class="hidden">
                    <form id="edit-ticket-form" class="space-y-4">
                        <!-- Dynamic form fields will be injected here -->
                    </form>
                </div>
            </div>
        </div>

        <script>
            if (typeof bookingsData === 'undefined') {
                const bookingsData = <?php echo json_encode(array_values($bookings)); ?>;
                const currentUserRole = '<?php echo $currentUser['role']; ?>';
            }

            function closeTicketModal() {
                document.getElementById('ticket-modal').classList.add('hidden');
            }

            function showTicketModal(bookingId) {
                const booking = bookingsData.find(b => b.id === bookingId);
                if (!booking) return;

                // Debug: log booking data to check if qr_code exists
                console.log('Booking data:', booking);
                console.log('QR Code path:', booking.qr_code);

                const modalContent = document.getElementById('ticket-modal-content');
                const modalFormContainer = document.getElementById('ticket-modal-form');
                
                modalContent.classList.remove('hidden');
                modalFormContainer.classList.add('hidden');

                modalContent.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="w-64 h-64 bg-gray-200 mx-auto flex items-center justify-center rounded-lg">
                            ${booking.qr_code ? `<img src="${booking.qr_code}" alt="QR CODE" class="w-full h-full object-contain rounded-md" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />` : ''}
                            <i class="fas fa-qrcode text-6xl text-gray-400" ${booking.qr_code ? 'style="display:none;"' : ''}></i>
                        </div>
                        ${booking.qr_code ? `<p class="text-xs text-gray-500 mt-2">QR Code: ${booking.qr_code}</p>` : '<p class="text-xs text-gray-500 mt-2">No QR Code uploaded</p>'}
                    </div>
                    <div class="space-y-3 text-sm">
                        <p><strong>Passenger:</strong> <span class="detail-passenger">${booking.user_name}</span></p>
                        <p><strong>Ticket Number:</strong> <span class="detail-id">${booking.id}</span></p>
                        <p><strong>Date:</strong> <span class="detail-date">${new Date(booking.departure_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span></p>
                        <p><strong>Departure:</strong> <span class="detail-departure">${booking.departure_location}</span></p>
                        <p><strong>Arrival:</strong> <span class="detail-arrival">${booking.arrival_location}</span></p>
                        <p><strong>Boarding Time:</strong> <span class="detail-time">${new Date('1970-01-01T' + booking.departure_time).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span></p>
                        <p><strong>Seat:</strong> <span class="detail-seat">${booking.seat || '-'}</span></p>
                        <p><strong>Bus Number:</strong> <span class="detail-bus">${booking.bus_number || '-'}</span></p>
                        <p><strong>Boarding Gate:</strong> <span class="detail-gate">${booking.boarding_gate || '-'}</span></p>
                        <p><strong>Special Requests:</strong> <span class="detail-requests">${booking.special_requests || '-'}</span></p>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        ${currentUserRole === 'admin' ? `<button onclick=\"editTicket('${booking.id}')\" class=\"px-4 py-2 bg-blue-600 text-white rounded-lg text-sm\">Edit</button>` : ''}
                        <button onclick="closeTicketModal()" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">Close</button>
                    </div>
                `;

                document.getElementById('ticket-modal').classList.remove('hidden');
            }

            function editTicket(bookingId) {
                const booking = bookingsData.find(b => b.id === bookingId);
                if (!booking) return;

                const modalContent = document.getElementById('ticket-modal-content');
                const modalFormContainer = document.getElementById('ticket-modal-form');
                const form = modalFormContainer.querySelector('form');

                form.innerHTML = `
                    <input type="hidden" name="action" value="update_booking">
                    <input type="hidden" name="booking_id" value="${booking.id}">
                     <div>
                        <label class="block text-sm font-medium mb-1">Passenger</label>
                        <input type="text" name="user_name" class="w-full rounded-lg border-gray-300" value="${booking.user_name}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Departure Location</label>
                        <input type="text" name="departure_location" class="w-full rounded-lg border-gray-300" value="${booking.departure_location}">
                    </div>
                    <div>
                         <label class="block text-sm font-medium mb-1">Arrival Location</label>
                         <input type="text" name="arrival_location" class="w-full rounded-lg border-gray-300" value="${booking.arrival_location}">
                     </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date</label>
                            <input type="date" name="departure_date" class="w-full rounded-lg border-gray-300" value="${booking.departure_date}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Time</label>
                            <input type="time" name="departure_time" class="w-full rounded-lg border-gray-300" value="${booking.departure_time}">
                        </div>
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Passengers</label>
                            <input type="number" name="passengers" class="w-full rounded-lg border-gray-300" value="${booking.passengers}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Flight Hours</label>
                            <input type="number" name="flight_hours" class="w-full rounded-lg border-gray-300" value="${booking.flight_hours}">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Seat</label>
                            <input type="text" name="seat" class="w-full rounded-lg border-gray-300" value="${booking.seat || ''}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Bus Number</label>
                            <input type="text" name="bus_number" class="w-full rounded-lg border-gray-300" value="${booking.bus_number || ''}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Boarding Gate</label>
                            <input type="text" name="boarding_gate" class="w-full rounded-lg border-gray-300" value="${booking.boarding_gate || ''}">
                        </div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300">
                            <option value="pending" ${booking.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="confirmed" ${booking.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="cancelled" ${booking.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            <option value="completed" ${booking.status === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Special Requests</label>
                        <textarea name="special_requests" rows="2" class="w-full rounded-lg border-gray-300">${booking.special_requests || ''}</textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Save Changes</button>
                        <button type="button" onclick="showTicketModal('${booking.id}')" class="px-4 py-2 bg-gray-200 rounded-lg text-sm">Cancel</button>
                    </div>
                `;

                modalContent.classList.add('hidden');
                modalFormContainer.classList.remove('hidden');
            }

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.show-ticket-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const ticketCard = e.target.closest('.ticket-card');
                        const bookingId = ticketCard.dataset.bookingId;
                        showTicketModal(bookingId);
                    });
                });

                document.getElementById('edit-ticket-form').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const form = e.target;
                    const formData = new FormData(form);
                    const bookingId = formData.get('booking_id');

                    try {
                        const response = await fetch('', { method: 'POST', body: formData });
                        const result = await response.json();
                        
                        if (result.success) {
                            alert('Booking updated successfully!');
                            const index = bookingsData.findIndex(b => b.id === bookingId);
                            if (index !== -1) {
                                bookingsData[index] = { ...bookingsData[index], ...result.booking };
                            }
                            window.location.reload(); 
                        } else {
                            alert('Error: ' + (result.message || 'Update failed'));
                        }
                    } catch (error) {
                        alert('An error occurred. Please try again.');
                        console.error('Submit Error:', error);
                    }
                });
            });

        </script>
        <?php
        return ob_get_clean();
    }
}
?> 