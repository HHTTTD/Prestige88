<?php
session_start();

require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/JetController.php';

// Check admin permission
if (!AuthController::hasPermission('admin')) {
    header('Location: ../index.php');
    exit;
}

$currentUser = AuthController::getCurrentUser();
$message = '';
$messageType = '';

// Handle jet management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'add_jet':
                JetController::create($_POST);
                $message = 'เพิ่มเครื่องบินเรียบร้อยแล้ว!';
                $messageType = 'success';
                break;
                
            case 'update_jet':
                JetController::update($_POST['jet_id'], $_POST);
                $message = 'อัพเดตข้อมูลเครื่องบินเรียบร้อยแล้ว!';
                $messageType = 'success';
                break;
                
            case 'delete_jet':
                JetController::delete($_POST['jet_id']);
                $message = 'ลบเครื่องบินเรียบร้อยแล้ว!';
                $messageType = 'success';
                break;

            case 'set_points_rate':
                $rate = intval($_POST['points_rate']);
                if ($rate > 0) {
                    // อ่านไฟล์ constants.php
                    $constFile = realpath(__DIR__ . '/../utils/constants.php');
                    $constContent = file_get_contents($constFile);
                    // แก้ไขค่า POINTS_RATE
                    $constContent = preg_replace('/define\(\'POINTS_RATE\',\s*\d+\);/', "define('POINTS_RATE', $rate);", $constContent);
                    file_put_contents($constFile, $constContent);
                    $message = 'อัปเดตเรทคะแนนสำเร็จ!';
                    $messageType = 'success';
                    // reload config
                    define('POINTS_RATE', $rate);
                } else {
                    $message = 'เรทคะแนนต้องมากกว่า 0';
                    $messageType = 'error';
                }
                break;
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$jets = JetController::getAll();
$bookings = Database::loadBookings();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - prestige88</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gray-800 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">
                <i class="fas fa-cog mr-2"></i>Admin Dashboard
            </h1>
            <div class="flex items-center space-x-4">
                <a href="../index.php" class="hover:text-blue-300">
                    <i class="fas fa-home mr-1"></i>กลับหน้าหลัก
                </a>
                <span><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Messages -->
        <?php if ($message): ?>
        <div class="mb-6">
            <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-6 py-4 rounded-xl border">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-plane text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">เครื่องบินทั้งหมด</p>
                        <p class="text-2xl font-bold"><?php echo count($jets); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">การจองทั้งหมด</p>
                        <p class="text-2xl font-bold"><?php echo count($bookings); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">รอดำเนินการ</p>
                        <p class="text-2xl font-bold">
                            <?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">ผู้ใช้งาน</p>
                        <p class="text-2xl font-bold"><?php echo count(Database::loadUsers()); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jet Management -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-plane mr-2 text-blue-600"></i>จัดการเครื่องบิน
                </h2>
                <button onclick="toggleAddJetForm()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>เพิ่มเครื่องบิน
                </button>
            </div>

            <!-- Add Jet Form (Hidden by default) -->
            <div id="addJetForm" class="hidden mb-6 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-lg font-semibold mb-4">เพิ่มเครื่องบินใหม่</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="add_jet">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">รุ่นเครื่องบิน</label>
                        <input type="text" name="model" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนที่นั่ง</label>
                        <input type="number" name="capacity" required min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ราคาต่อชั่วโมง (บาท)</label>
                        <input type="number" name="price_per_hour" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ระยะการบิน (กม.)</label>
                        <input type="number" name="range_km" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ความเร็วสูงสุด (กม./ชม.)</label>
                        <input type="number" name="max_speed" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL รูปภาพ</label>
                        <input type="url" name="image" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">สิ่งอำนวยความสะดวก (คั่นด้วยเครื่องหมาย ,)</label>
                        <input type="text" name="amenities_string" placeholder="Wi-Fi, Bar, Bedroom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>บันทึก
                        </button>
                        <button type="button" onclick="toggleAddJetForm()" class="ml-2 bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>

            <!-- Jets Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">รุ่น</th>
                            <th class="px-4 py-3 text-left">ที่นั่ง</th>
                            <th class="px-4 py-3 text-left">ราคา/ชม.</th>
                            <th class="px-4 py-3 text-left">สถานะ</th>
                            <th class="px-4 py-3 text-left">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($jets as $jet): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($jet['model']); ?></td>
                            <td class="px-4 py-3"><?php echo $jet['capacity']; ?></td>
                            <td class="px-4 py-3"><?php echo number_format($jet['price_per_hour']); ?> บาท</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    <?php echo $jet['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $jet['status']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-800" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                        <input type="hidden" name="action" value="delete_jet">
                                        <input type="hidden" name="jet_id" value="<?php echo $jet['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Points Rate Setting -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold mb-4 text-gray-800"><i class="fas fa-star text-yellow-500 mr-2"></i>ตั้งค่าเรทคะแนนสะสม</h2>
            <form method="POST" class="flex items-center gap-4">
                <input type="hidden" name="action" value="set_points_rate">
                <label class="font-semibold">1 คะแนน ต่อ</label>
                <input type="number" name="points_rate" min="1" value="<?php echo defined('POINTS_RATE') ? POINTS_RATE : 100; ?>" class="border px-3 py-2 rounded-lg w-32" required>
                <span class="text-gray-700">บาท</span>
                <button type="submit" class="ml-4 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold">บันทึก</button>
            </form>
        </div>
    </div>

    <script>
        function toggleAddJetForm() {
            const form = document.getElementById('addJetForm');
            form.classList.toggle('hidden');
        }

        // Handle amenities conversion
        document.querySelector('form').addEventListener('submit', function(e) {
            const amenitiesInput = document.querySelector('input[name="amenities_string"]');
            if (amenitiesInput && amenitiesInput.value) {
                const amenities = amenitiesInput.value.split(',').map(s => s.trim()).filter(s => s);
                
                // Create hidden inputs for amenities array
                amenities.forEach(amenity => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'amenities[]';
                    input.value = amenity;
                    this.appendChild(input);
                });
            }
        });
    </script>
</body>
</html>