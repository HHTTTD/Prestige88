<?php
function renderRecentSearches($recentSearches, $linkToSearchPage = false) {
    ob_start();
    $recentSearches = array_slice($recentSearches, 0, 5);
    ?>
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Recent Searches</h2>
        <div class="flex flex-col gap-4">
            <?php foreach ($recentSearches as $search): ?>
            <?php 
                $url = '?page=search&from=' . urlencode($search['from']) . '&to=' . urlencode($search['to']) . '&date=' . urlencode($search['date']) . '&passengers=1';
            ?>
            <div class="bg-white rounded-2xl shadow-md flex items-center justify-between px-6 py-4">
                <div>
                    <div class="font-bold text-lg text-black">
                        <?php if ($linkToSearchPage): ?>
                        <a href="<?php echo $url; ?>" class="hover:underline text-black">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($search['from']); ?>
                        <span class="mx-2" style="color:#a89c8a;">→</span>
                        <?php echo htmlspecialchars($search['to']); ?>
                        <?php if ($linkToSearchPage): ?></a><?php endif; ?>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 mt-1">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php echo htmlspecialchars($search['date']); ?>
                    </div>
                </div>
                <div>
                    <?php if ($linkToSearchPage): ?>
                    <a href="<?php echo $url; ?>"><i class="fas fa-search text-xl" style="color:#a89c8a;"></i></a>
                    <?php else: ?>
                    <i class="fas fa-search text-xl" style="color:#a89c8a;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
} 