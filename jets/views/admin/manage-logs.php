<?php

if (!function_exists('renderManageLogsPage')) {
    function renderManageLogsPage($logs) {
        ob_start();
        ?>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-file-alt text-[#a39786]"></i>
                    System Logs
                </h2>
                <div class="flex gap-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-download"></i>
                        Export Logs
                    </button>
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-trash"></i>
                        Clear Logs
                    </button>
                </div>
            </div>
            
            <!-- Log Type Tabs -->
            <div class="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg">
                <button class="flex-1 py-2 px-4 rounded-md bg-white shadow-sm text-sm font-medium text-gray-700">
                    All Logs
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                    OTP Logs
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                    Activity Logs
                </button>
                <button class="flex-1 py-2 px-4 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                    Notification Logs
                </button>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Total Logs</p>
                            <p class="text-2xl font-bold text-blue-800"><?php echo count($logs); ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Success</p>
                            <p class="text-2xl font-bold text-green-800">
                                <?php echo count(array_filter($logs, function($log) { return strpos(strtolower($log), 'success') !== false; })); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-yellow-600 font-medium">Warnings</p>
                            <p class="text-2xl font-bold text-yellow-800">
                                <?php echo count(array_filter($logs, function($log) { return strpos(strtolower($log), 'warning') !== false; })); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-red-50 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-red-600 font-medium">Errors</p>
                            <p class="text-2xl font-bold text-red-800">
                                <?php echo count(array_filter($logs, function($log) { return strpos(strtolower($log), 'error') !== false; })); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Log Entries -->
            <div class="space-y-4 max-h-96 overflow-y-auto">
                <?php if (empty($logs)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-2"></i>
                        <p>No logs found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($logs) as $log): ?>
                    <div class="border rounded-lg p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <?php 
                                    $logLower = strtolower($log);
                                    if (strpos($logLower, 'error') !== false) {
                                        echo '<i class="fas fa-times-circle text-red-500"></i>';
                                        echo '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Error</span>';
                                    } elseif (strpos($logLower, 'warning') !== false) {
                                        echo '<i class="fas fa-exclamation-triangle text-yellow-500"></i>';
                                        echo '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">Warning</span>';
                                    } elseif (strpos($logLower, 'success') !== false) {
                                        echo '<i class="fas fa-check-circle text-green-500"></i>';
                                        echo '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Success</span>';
                                    } else {
                                        echo '<i class="fas fa-info-circle text-blue-500"></i>';
                                        echo '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">Info</span>';
                                    }
                                    ?>
                                    <span class="text-xs text-gray-500">
                                        <?php 
                                        // Extract timestamp if available
                                        if (preg_match('/\[(.*?)\]/', $log, $matches)) {
                                            echo $matches[1];
                                        } else {
                                            echo 'Unknown time';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 font-mono"><?php echo htmlspecialchars($log); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="text-blue-600 hover:text-blue-800" title="Copy Log">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800" title="Delete Log">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentLogType = 'all';

            // Export Logs
            document.querySelector('.bg-blue-600').onclick = function() {
                const formData = new FormData();
                formData.append('action', 'export_logs');
                formData.append('log_type', currentLogType);
                fetch('', { method: 'POST', body: formData })
                    .then(res => res.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'system_logs.txt';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    });
            };

            // Clear Logs
            document.querySelector('.bg-red-600').onclick = function() {
                if (!confirm('Are you sure you want to clear all logs?')) return;
                const formData = new FormData();
                formData.append('action', 'clear_logs');
                formData.append('log_type', currentLogType);
                fetch('', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => { if (data.success) location.reload(); });
            };

            // Filter Tabs
            document.querySelectorAll('.flex.space-x-1 button').forEach((btn, idx) => {
                btn.onclick = function() {
                    document.querySelectorAll('.flex.space-x-1 button').forEach(b => {
                        b.classList.remove('bg-white', 'shadow-sm', 'text-gray-700');
                        b.classList.add('text-gray-500');
                    });
                    this.classList.add('bg-white', 'shadow-sm', 'text-gray-700');
                    this.classList.remove('text-gray-500');
                    let type = 'all';
                    if (idx === 1) type = 'otp';
                    else if (idx === 2) type = 'activity';
                    else if (idx === 3) type = 'notification';
                    currentLogType = type;
                    filterLogs(type);
                };
            });

            function filterLogs(type) {
                const formData = new FormData();
                formData.append('action', 'filter_logs');
                formData.append('log_type', type);
                fetch('', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) renderLogs(data.logs);
                    });
            }

            // Render logs
            function renderLogs(logs) {
                const container = document.querySelector('.space-y-4.max-h-96');
                if (!logs || logs.length === 0) {
                    container.innerHTML = `<div class="text-center py-8 text-gray-500"><i class="fas fa-file-alt text-4xl mb-2"></i><p>No logs found.</p></div>`;
                    return;
                }
                container.innerHTML = logs.slice().reverse().map((log, i) => {
                    let type = 'info', icon = 'fa-info-circle', badge = 'bg-blue-100 text-blue-700', badgeText = 'Info';
                    const l = log.toLowerCase();
                    if (l.includes('error')) { type = 'error'; icon = 'fa-times-circle'; badge = 'bg-red-100 text-red-700'; badgeText = 'Error'; }
                    else if (l.includes('warning')) { type = 'warning'; icon = 'fa-exclamation-triangle'; badge = 'bg-yellow-100 text-yellow-700'; badgeText = 'Warning'; }
                    else if (l.includes('success')) { type = 'success'; icon = 'fa-check-circle'; badge = 'bg-green-100 text-green-700'; badgeText = 'Success'; }
                    let time = 'Unknown time';
                    const match = log.match(/\[(.*?)\]/);
                    if (match) time = match[1];
                    return `<div class="border rounded-lg p-4 hover:bg-gray-50 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas ${icon} text-${type === 'error' ? 'red' : type === 'warning' ? 'yellow' : type === 'success' ? 'green' : 'blue'}-500"></i>
                                <span class="px-2 py-1 ${badge} rounded-full text-xs font-semibold">${badgeText}</span>
                                <span class="text-xs text-gray-500">${time}</span>
                            </div>
                            <p class="text-sm text-gray-700 font-mono">${log.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="text-blue-600 hover:text-blue-800 copy-log" data-log="${encodeURIComponent(log)}" title="Copy Log"><i class="fas fa-copy"></i></button>
                            <button class="text-red-600 hover:text-red-800 delete-log" data-index="${logs.length-1-i}" title="Delete Log"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>`;
                }).join('');
                // Copy Log
                container.querySelectorAll('.copy-log').forEach(btn => {
                    btn.onclick = function() {
                        const text = decodeURIComponent(this.dataset.log);
                        navigator.clipboard.writeText(text);
                    };
                });
                // Delete Log
                container.querySelectorAll('.delete-log').forEach(btn => {
                    btn.onclick = function() {
                        if (!confirm('Delete this log entry?')) return;
                        const idx = this.dataset.index;
                        const formData = new FormData();
                        formData.append('action', 'delete_log');
                        formData.append('log_type', currentLogType);
                        formData.append('log_index', idx);
                        fetch('', { method: 'POST', body: formData })
                            .then(res => res.json())
                            .then(data => { if (data.success) filterLogs(currentLogType); });
                    };
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
?> 