<?php
// This page is included and executed from index.php
// It has access to $currentUser, $message, $messageType variables.

require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'models/Jet.php';
require_once 'models/Booking.php';

// ดึงข้อมูลผู้ใช้ปัจจุบัน
if (!AuthController::isLoggedIn()) {
    header('Location: index.php'); // ถ้ายังไม่ล็อคอิน ให้กลับไปหน้าหลัก
    exit;
}
$currentUser = AuthController::getCurrentUser();

// ดึง Jet ID จาก URL
$jetId = $_GET['jet_id'] ?? null;
if (!$jetId) {
    echo "ไม่พบ ID ของเครื่องบิน";
    exit;
}

// โหลดข้อมูลเครื่องบิน
$jet = Jet::findById($jetId);
if (!$jet) {
    echo "ไม่พบข้อมูลเครื่องบิน";
    exit;
}

// โหลด available slots ของเครื่องบินลำนี้ที่ยังไม่ถูกจอง
$allBookings = Database::loadBookings();
$jetBookings = array_filter($allBookings, function($b) use ($jetId) {
    return $b['jet_id'] === $jetId && in_array($b['status'], ['pending', 'confirmed']);
});

$availableSlots = [];
if (isset($jet['available_slots']) && is_array($jet['available_slots'])) {
    foreach ($jet['available_slots'] as $slot) {
        $isBooked = false;
        foreach ($jetBookings as $booking) {
            if ($booking['departure_date'] === $slot['date'] && $booking['departure_location'] === $slot['departure']) {
                $isBooked = true;
                break;
            }
        }
        if (!$isBooked) {
            $availableSlots[] = $slot;
        }
    }
}

// Sort available slots by date
usort($availableSlots, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Private Jet - <?php echo htmlspecialchars($jet['model']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .input-disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <?php include 'views/components/navbar.php'; echo renderNavbar($currentUser); ?>
    
    <main class="container mx-auto px-4 py-8 mt-24">
                 <div class="mb-6">
                    <a href="?page=search" class="text-[#a39786] hover:text-[#8b7d6b] flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Back to Search Jets
                    </a>
                </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12">
            
            <!-- Left Column: Jet Details -->
            <div class="lg:col-span-2">
                 <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28">
                     <img src="<?php echo htmlspecialchars($jet['image']); ?>" alt="<?php echo htmlspecialchars($jet['model']); ?>" class="w-full h-56 object-cover rounded-xl mb-6 shadow-md">
                     <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($jet['model']); ?></h1>
                     <p class="text-gray-500 mb-6">Specifications & Amenities</p>
                     
                     <div class="grid grid-cols-2 gap-4 text-sm mb-6">
                         <div class="bg-gray-50 p-4 rounded-xl">
                             <div class="font-semibold text-gray-600">Capacity</div>
                             <div class="text-lg font-bold text-gray-800 flex items-center mt-1"><i class="fas fa-users mr-2 text-gray-400"></i><?php echo htmlspecialchars($jet['capacity']); ?></div>
                         </div>
                         <div class="bg-gray-50 p-4 rounded-xl">
                             <div class="font-semibold text-gray-600">Max Speed</div>
                             <div class="text-lg font-bold text-gray-800 flex items-center mt-1"><i class="fas fa-tachometer-alt mr-2 text-gray-400"></i><?php echo number_format($jet['max_speed']); ?> km/h</div>
                         </div>
                         <div class="bg-gray-50 p-4 rounded-xl col-span-2">
                             <div class="font-semibold text-gray-600">Range</div>
                             <div class="text-lg font-bold text-gray-800 flex items-center mt-1"><i class="fas fa-globe mr-2 text-gray-400"></i><?php echo number_format($jet['range_km']); ?> km</div>
                         </div>
                     </div>

                     <?php if (!empty($jet['amenities'])): ?>
                     <div>
                         <h3 class="text-md font-semibold text-gray-700 mb-3">Amenities</h3>
                         <div class="flex flex-wrap gap-2">
                             <?php foreach ($jet['amenities'] as $amenity): ?>
                                 <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1.5 rounded-full"><?php echo htmlspecialchars($amenity); ?></span>
                             <?php endforeach; ?>
                         </div>
                     </div>
                     <?php endif; ?>
                 </div>
            </div>

            <!-- Right Column: Booking Form -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Booking Details</h2>
                    <p class="text-gray-500 mb-8">Complete the form below to request your flight.</p>
                    
                    <form action="index.php" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="create_booking">
                        <input type="hidden" name="jet_id" value="<?php echo htmlspecialchars($jet['id']); ?>">
                        <input type="hidden" name="jet_model" value="<?php echo htmlspecialchars($jet['model']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($currentUser['id']); ?>">
                        <input type="hidden" name="user_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>">
                        
                        <!-- Flight Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center"><i class="fas fa-calendar-alt mr-3 text-[#a89c8a]"></i>Flight Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="available_date" class="block text-sm font-medium text-gray-700 mb-1">Available Date</label>
                                    <select id="available_date" name="departure_date" class="mt-1 block w-full px-4 py-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" required>
                                        <option value="">Select an available date</option>
                                        <?php if (empty($availableSlots)): ?>
                                            <option value="" disabled>No available dates for this jet</option>
                                        <?php else: ?>
                                            <?php foreach ($availableSlots as $slot): ?>
                                                <option value="<?php echo htmlspecialchars($slot['date']); ?>" data-departure="<?php echo htmlspecialchars($slot['departure']); ?>" data-arrival="<?php echo htmlspecialchars($slot['arrival'] ?? ''); ?>">
                                                    <?php echo date('F j, Y', strtotime($slot['date'])) . ' - From ' . htmlspecialchars($slot['departure']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" name="departure_time" id="departure_time" value="10:00" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" required>
                                </div>
                                <div>
                                    <label for="departure_location" class="block text-sm font-medium text-gray-700 mb-1">Departure</label>
                                    <input type="text" id="departure_location" name="departure_location" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm input-disabled" readonly required>
                                </div>
                                <div>
                                    <label for="arrival_location" class="block text-sm font-medium text-gray-700 mb-1">Arrival</label>
                                    <input type="text" id="arrival_location" name="arrival_location" placeholder="Destination of your choice" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" required>
                                </div>
                            </div>
                        </div>

                        <!-- Passenger Details -->
                        <div>
                             <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center mt-6"><i class="fas fa-users mr-3 text-[#a89c8a]"></i>Passenger Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="passengers" class="block text-sm font-medium text-gray-700 mb-1">Passengers</label>
                                    <input type="number" name="passengers" id="passengers" placeholder="e.g., 4 (max <?php echo $jet['capacity']; ?>)" max="<?php echo $jet['capacity']; ?>" min="1" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" required>
                                </div>
                                <div>
                                    <label for="flight_hours" class="block text-sm font-medium text-gray-700 mb-1">Flight Hours (est.)</label>
                                    <input type="number" step="0.5" name="flight_hours" id="flight_hours" placeholder="e.g., 2.5" min="0.5" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
                                    <textarea name="special_requests" id="special_requests" rows="3" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a89c8a] focus:border-[#a89c8a]" placeholder="e.g., special meals, meeting arrangements, birthday celebration"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t border-gray-200">
                             <button type="submit" class="w-full bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-4 px-6 rounded-lg transition duration-300 flex items-center justify-center gap-2 text-lg shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-paper-plane"></i>
                                Send Booking Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateSelect = document.getElementById('available_date');
        const departureInput = document.getElementById('departure_location');
        const arrivalInput = document.getElementById('arrival_location');

        dateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const departure = selectedOption.getAttribute('data-departure');
            const arrival = selectedOption.getAttribute('data-arrival');
            
            departureInput.value = departure || '';
            
            if (arrival) {
                arrivalInput.value = arrival;
                arrivalInput.readOnly = true;
                arrivalInput.classList.add('input-disabled');
            } else {
                arrivalInput.value = '';
                arrivalInput.readOnly = false;
                arrivalInput.classList.remove('input-disabled');
            }
        });
        
        // Trigger change on load if a date is pre-selected or to set initial state
        if (dateSelect.value) {
            dateSelect.dispatchEvent(new Event('change'));
        }
    });
    </script>
</body>
</html>
<?php exit; ?> 