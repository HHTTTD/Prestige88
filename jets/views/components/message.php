<?php
function renderMessage($message, $messageType) {
    if (!$message) return '';
    
    ob_start();
    ?>
    <div class="mb-6">
        <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-6 py-4 rounded-xl border shadow-lg" role="alert">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3 text-lg"></i>
                <span class="font-medium"><?php echo htmlspecialchars($message); ?></span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}