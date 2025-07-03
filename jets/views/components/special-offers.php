<?php
function renderSpecialOffers($offers) {
    ob_start();
    ?>
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Special Offers (<?php echo count($offers); ?> offers)</h2>
        <div class="flex flex-col gap-4">
            <?php if (empty($offers)): ?>
                <div class="text-center text-gray-500 py-8">
                    <p>No special offers available at the moment.</p>
                </div>
            <?php else: ?>
            <?php foreach ($offers as $offer): ?>
            <div class="flex items-center bg-[#a89c8a]/80 rounded-2xl overflow-hidden shadow-md">
                <div class="flex items-center justify-center w-24 h-24 bg-[#a89c8a]/60">
                    <i class="<?php echo $offer['icon']; ?> text-3xl text-white"></i>
                </div>
                <div class="flex-1 bg-white px-6 py-4">
                    <div class="font-bold text-lg mb-1"><?php echo htmlspecialchars($offer['title']); ?></div>
                    <div class="text-sm text-gray-700"><?php echo htmlspecialchars($offer['desc']); ?></div>
                    <div class="text-xs text-red-500 mt-1"><?php echo htmlspecialchars($offer['note']); ?></div>
                </div>
                <div class="flex flex-col items-center justify-center w-24 h-24 bg-[#a89c8a]/80">
                    <span class="font-bold text-white text-sm"><?php echo htmlspecialchars($offer['badge']); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
} 