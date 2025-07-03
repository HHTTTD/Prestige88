<?php

if (!function_exists('renderManageUsersPage')) {
    function renderManageUsersPage($users) {
        ob_start();
        ?>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                <i class="fas fa-users-cog text-[#a39786]"></i>
                Manage Users
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 text-left">Full Name</th>
                            <th class="px-4 py-3 text-left">Username</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Tier</th>
                            <th class="px-4 py-3 text-left">Joined</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody" class="divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    <i class="fas fa-user-slash text-4xl mb-2"></i>
                                    <p>No users found.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                                <td class="px-4 py-3 font-mono"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                        <?php echo ($user['role'] === 'admin') ? 'bg-red-100 text-red-700' : ($user['role'] === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'); ?>">
                                        <?php echo ($user['role'] === 'client') ? 'User' : ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?php echo ucfirst($user['membership_tier'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-3"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td class="px-4 py-3 flex items-center gap-3">
                                    <button class="text-blue-600 hover:text-blue-800" onclick="openEditUserModal('<?php echo $user['id']; ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="text-purple-600 hover:text-purple-800 ml-2" onclick="openNotifyModal('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                        <i class="fas fa-paper-plane"></i> Notify
                                    </button>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                    <button class="text-red-600 hover:text-red-800 ml-2" onclick="openDeleteUserModal('<?php echo $user['id']; ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            let usersData = <?php echo json_encode(array_values($users)); ?>;
            let editingUserId = null;
            function openEditUserModal(userId) {
                const user = usersData.find(u => u.id === userId);
                if (!user) return false;
                document.getElementById('edit-user-id').value = user.id;
                document.getElementById('edit-full-name').value = user.full_name || '';
                document.getElementById('edit-email').value = user.email || '';
                document.getElementById('edit-role').value = user.role || 'user';
                document.getElementById('edit-tier').value = user.membership_tier || 'silver';
                document.getElementById('edit-user-modal').classList.remove('hidden');
                return false;
            }
            window.openEditUserModal = openEditUserModal;
            function closeEditUserModal() {
                document.getElementById('edit-user-modal').classList.add('hidden');
            }
            window.closeEditUserModal = closeEditUserModal;
            function openDeleteUserModal(userId) {
                editingUserId = userId;
                document.getElementById('delete-user-modal').classList.remove('hidden');
                return false;
            }
            window.openDeleteUserModal = openDeleteUserModal;
            function closeDeleteUserModal() {
                document.getElementById('delete-user-modal').classList.add('hidden');
            }
            window.closeDeleteUserModal = closeDeleteUserModal;
            // --- AJAX for Edit User ---
            const editUserForm = document.getElementById('edit-user-form');
            if (editUserForm) {
                editUserForm.onsubmit = async function(e) {
                    e.preventDefault();
                    try {
                        const form = e.target;
                        const formData = new FormData(form);
                        formData.append('action', 'update_user');
                        const res = await fetch('', { method: 'POST', body: formData });
                        const data = await res.json();
                        if (data.success) {
                            showUserMessage('User updated successfully!', 'success');
                            closeEditUserModal();
                            // Update table row in real time
                            const idx = usersData.findIndex(u => u.id === data.user.id);
                            if (idx !== -1) usersData[idx] = data.user;
                            updateUserTable();
                        } else {
                            showUserMessage(data.message || 'Update failed', 'error');
                        }
                    } catch (err) {
                        console.error('Edit user AJAX error:', err);
                    }
                };
            } else {
                console.error('editUserForm not found!');
            }
            // --- AJAX for Delete User ---
            const confirmDeleteBtn = document.getElementById('confirm-delete-user');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.onclick = async function() {
                    if (!editingUserId) return;
                    const formData = new FormData();
                    formData.append('action', 'delete_user');
                    formData.append('user_id', editingUserId);
                    const res = await fetch('', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.success) {
                        showUserMessage('User deleted successfully!', 'success');
                        closeDeleteUserModal();
                        usersData = usersData.filter(u => u.id !== editingUserId);
                        updateUserTable();
                    } else {
                        showUserMessage(data.message || 'Delete failed', 'error');
                    }
                };
            }
            function updateUserTable() {
                const tbody = document.getElementById('users-tbody');
                if (!tbody) return;
                tbody.innerHTML = usersData.length === 0 ? `<tr><td colspan="7" class="text-center py-8 text-gray-500"><i class="fas fa-user-slash text-4xl mb-2"></i><p>No users found.</p></td></tr>` : usersData.map(user => `
                    <tr>
                        <td class="px-4 py-3">${user.full_name || '-'}</td>
                        <td class="px-4 py-3 font-mono">${user.username}</td>
                        <td class="px-4 py-3">${user.email}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 font-semibold leading-tight rounded-full ${user.role === 'admin' ? 'bg-red-100 text-red-700' : (user.role === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700')}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                        <td class="px-4 py-3">${user.membership_tier ? user.membership_tier.charAt(0).toUpperCase() + user.membership_tier.slice(1) : 'N/A'}</td>
                        <td class="px-4 py-3">${user.created_at ? new Date(user.created_at).toLocaleDateString() : '-'}</td>
                        <td class="px-4 py-3 flex items-center gap-3">
                            <button class="text-blue-600 hover:text-blue-800" onclick="openEditUserModal('${user.id}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="text-purple-600 hover:text-purple-800 ml-2" onclick="openNotifyModal('${user.id}', '${user.full_name}')">
                                <i class="fas fa-paper-plane"></i> Notify
                            </button>
                            ${user.role !== 'admin' ? `
                            <button class="text-red-600 hover:text-red-800 ml-2" onclick="openDeleteUserModal('${user.id}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            ` : ''}
                        </td>
                    </tr>
                `).join('');
            }
            function showUserMessage(msg, type) {
                const container = document.getElementById('user-message-container');
                const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                container.innerHTML = `<div class="border px-4 py-3 rounded mb-4 ${alertClass}">${msg}</div>`;
                setTimeout(() => { container.innerHTML = ''; }, 4000);
            }

            window.openNotifyModal = function(userId, userName) {
                document.getElementById('notify-user-id').value = userId;
                document.getElementById('notify-user-name').textContent = userName;
                document.getElementById('notify-modal').classList.remove('hidden');
            }

            window.closeNotifyModal = function() {
                document.getElementById('notify-modal').classList.add('hidden');
                document.getElementById('notify-user-form').reset();
            }

            document.getElementById('notify-user-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                const button = form.querySelector('button[type="submit"]');
                button.disabled = true;

                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        showUserMessage('Notification sent successfully!', 'success');
                        closeNotifyModal();
                    } else {
                        showUserMessage('Error: ' + (result.message || 'Failed to send notification'), 'error');
                    }
                } catch (error) {
                    showUserMessage('An error occurred. Please try again.', 'error');
                    console.error('Notify user AJAX error:', error);
                } finally {
                    button.disabled = false;
                }
            });
        });
        </script>
        <!-- Edit User Modal -->
        <div id="edit-user-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
          <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md relative">
            <button onclick="closeEditUserModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Edit User</h3>
            <form id="edit-user-form" class="space-y-4">
              <input type="hidden" name="user_id" id="edit-user-id">
              <div>
                <label class="block text-sm font-medium mb-1">Full Name</label>
                <input type="text" name="full_name" id="edit-full-name" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">E-mail</label>
                <input type="email" name="email" id="edit-email" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" id="edit-role" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]">
                  <option value="admin">Admin</option>
                  <option value="client">User</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Tier</label>
                <select name="membership_tier" id="edit-tier" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#a89c8a]">
                  <option value="silver">Silver</option>
                  <option value="gold">Gold</option>
                  <option value="platinum">Platinum</option>
                  <option value="black">Black</option>
                </select>
              </div>
              <button type="submit" class="w-full bg-[#a89c8a] hover:bg-[#b3a89c] text-white font-semibold py-3 rounded-xl mt-2">Save Change</button>
            </form>
          </div>
        </div>
        <!-- Delete User Modal -->
        <div id="delete-user-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
          <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm relative">
            <button onclick="closeDeleteUserModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-semibold mb-4">Delete User</h3>
            <p class="mb-6">Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="flex gap-4">
              <button id="confirm-delete-user" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-xl">Delete</button>
              <button onclick="closeDeleteUserModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-xl">Cancel</button>
            </div>
          </div>
        </div>
        <!-- Notify User Modal -->
        <div id="notify-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg relative">
                <button onclick="closeNotifyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                <h3 class="text-xl font-bold mb-4 text-gray-800">Send Notification</h3>
                <form id="notify-user-form">
                    <input type="hidden" name="action" value="send_notification">
                    <input type="hidden" id="notify-user-id" name="user_id">
                    
                    <p class="mb-4">Sending message to: <strong id="notify-user-name" class="font-semibold"></strong></p>

                    <div>
                        <label for="notification-message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea id="notification-message" name="message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a39786]" placeholder="Type your notification message here..." required></textarea>
                    </div>

                    <div class="mt-4">
                        <label for="notification-link" class="block text-sm font-medium text-gray-700 mb-2">Link (Optional)</label>
                        <select id="notification-link" name="link" class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#a39786] bg-white">
                            <option value="?page=home">Home Page</option>
                            <option value="?page=my-tickets">My Tickets</option>
                            <option value="?page=offers">Offers & Rewards</option>
                            <option value="?page=profile">Profile Page</option>
                            <option value="?page=search">Search Jets Page</option>
                        </select>
                    </div>

                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" onclick="closeNotifyModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#a39786] hover:bg-[#8b7d6b] text-white font-semibold py-2 px-6 rounded-lg transition">
                            <i class="fas fa-paper-plane mr-2"></i>Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div id="user-message-container"></div>
        <?php
        return ob_get_clean();
    }
}
?> 