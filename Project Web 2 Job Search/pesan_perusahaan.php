<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login_perusahaan.php");
    exit;
}

include 'config/koneksi.php';

$id_perusahaan = (int) $_SESSION['id_user'];

// Ambil data user
$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user=$id_perusahaan");
$user = mysqli_fetch_assoc($result);

// Ambil id_lawan dari parameter (jika ada)
$id_lawan = (int)($_GET['id_lawan'] ?? 0);
$lawan = null;
if ($id_lawan > 0) {
    $r = $conn->prepare("SELECT id_user, nama, foto_profil, role, bidang_keahlian FROM users WHERE id_user = ?");
    $r->bind_param("i", $id_lawan);
    $r->execute();
    $lawan = $r->get_result()->fetch_assoc();
    $r->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan - Lokerin</title>
    <link rel="icon" type="image/png" href="assets/icon_head_lokerin.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb; color: #111827; line-height: 1.6;
        }

        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px; background: white; border-right: 1px solid #e5e7eb;
            padding: 24px 16px; display: flex; flex-direction: column;
            position: fixed; height: 100vh; overflow-y: auto;
        }

        .logo { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; padding: 0 8px; }
        .logo-img { width: 27px; height: 27px; object-fit: contain; }
        .logo-text { font-size: 18px; font-weight: 700; color: #0D9488; }

        .company-profile {
            display: flex; align-items: center; gap: 12px; padding: 12px;
            background: #f9fafb; border-radius: 12px; margin-bottom: 16px;
        }
        .company-avatar {
            width: 40px; height: 40px; background: #f59e0b; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 16px;
        }
        .company-info { flex: 1; }
        .company-name { font-size: 14px; font-weight: 600; color: #111827; }
        .company-industry { font-size: 12px; color: #6b7280; }

        nav { flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px;
            color: #6b7280; text-decoration: none; border-radius: 8px; margin-bottom: 4px;
            font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;
        }
        .nav-item:hover { background: #f9fafb; color: #111827; }
        .nav-item.active { background: #e0f2f1; color: #0d9488; }
        .nav-divider { height: 1px; background: #e5e7eb; margin: 16px 0; }
        .nav-item-logout { color: #ef4444; }
        .nav-item-logout:hover { background: #fef2f2; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; display: flex; height: 100vh; }

        /* Message List */
        .message-list-container {
            width: 360px; background: white; border-right: 1px solid #e5e7eb;
            display: flex; flex-direction: column;
        }
        .message-list-header { padding: 24px; border-bottom: 1px solid #e5e7eb; }
        .message-list-title { font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 16px; }
        .message-search { position: relative; }
        .message-search input {
            width: 100%; padding: 10px 16px 10px 40px; border: 1px solid #e5e7eb;
            border-radius: 8px; font-size: 14px; outline: none; transition: all 0.2s;
        }
        .message-search input:focus { border-color: #0d9488; }
        .message-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }

        .message-list { flex: 1; overflow-y: auto; }

        .message-item {
            padding: 16px 24px; border-bottom: 1px solid #f3f4f6; cursor: pointer;
            transition: all 0.2s; display: flex; gap: 12px;
        }
        .message-item:hover { background: #f9fafb; }
        .message-item.active { background: #e0f2f1; }
        .message-item.unread { background: #f0fdfa; }

        .msg-avatar {
            width: 48px; height: 48px; border-radius: 50%; background: #0d9488;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; flex-shrink: 0; font-size: 16px; overflow: hidden;
        }
        .msg-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .message-content { flex: 1; min-width: 0; }
        .message-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px; }
        .message-sender { font-size: 15px; font-weight: 600; color: #111827; }
        .message-time { font-size: 12px; color: #9ca3af; white-space: nowrap; }
        .message-preview { font-size: 14px; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 4px; }
        .message-item.unread .message-preview { color: #111827; font-weight: 500; }

        .message-label {
            display: inline-block; padding: 2px 8px; background: #f0fdfa;
            color: #0d9488; border-radius: 4px; font-size: 11px; font-weight: 600;
        }
        .unread-badge { width: 8px; height: 8px; background: #0d9488; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
        .no-contacts { padding: 40px 24px; text-align: center; color: #9ca3af; font-size: 14px; }

        /* Chat Container */
        .chat-container { flex: 1; display: flex; flex-direction: column; background: #f9fafb; }

        .chat-header {
            padding: 20px 32px; background: white; border-bottom: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; align-items: center;
        }
        .chat-header-info { display: flex; align-items: center; gap: 16px; }

        .chat-avatar {
            width: 48px; height: 48px; border-radius: 50%; background: #0d9488;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 18px; overflow: hidden;
        }
        .chat-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .chat-user-info h3 { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 2px; }
        .chat-user-info p { font-size: 14px; color: #6b7280; }

        .chat-messages { flex: 1; overflow-y: auto; padding: 24px 32px; }
        .chat-date-divider { text-align: center; margin: 24px 0; font-size: 13px; color: #9ca3af; }

        .message-bubble { max-width: 60%; margin-bottom: 8px; }
        .message-bubble.received { display: flex; gap: 12px; }
        .message-bubble.sent { margin-left: auto; display: flex; flex-direction: column; align-items: flex-end; }

        .bubble-content {
            background: white; padding: 12px 16px; border-radius: 12px;
            font-size: 14px; color: #111827; line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .message-bubble.sent .bubble-content { background: #0d9488; color: white; }
        .bubble-time { font-size: 12px; color: #9ca3af; margin-top: 4px; padding: 0 4px; }

        .chat-input-container { padding: 20px 32px; background: white; border-top: 1px solid #e5e7eb; }
        .chat-input-wrapper { display: flex; gap: 12px; align-items: flex-end; }
        .chat-input { flex: 1; }
        .chat-input textarea {
            width: 100%; padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 8px;
            font-size: 14px; font-family: inherit; resize: none; outline: none;
            min-height: 44px; max-height: 120px;
        }
        .chat-input textarea:focus { border-color: #0d9488; }

        .send-btn {
            width: 44px; height: 44px; border-radius: 8px; background: #0d9488;
            border: none; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: white; transition: background 0.2s;
        }
        .send-btn:hover { background: #0f766e; }
        .send-btn:disabled { background: #9ca3af; cursor: not-allowed; }

        .chat-empty {
            flex: 1; display: flex; flex-direction: column; align-items: center;
            justify-content: center; color: #9ca3af; gap: 16px;
        }
        .chat-empty svg { opacity: 0.4; }
        .chat-empty p { font-size: 16px; }

        @media (max-width: 1400px) { }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .message-list-container { width: 100%; }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="logo">
                <img class="logo-img" src="assets/logo_lokerin.png" alt="L">
                <span class="logo-text">Lokerin</span>
            </div>

            <div class="company-profile">
                <div class="company-avatar">
                    <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="company-info">
                    <div class="company-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                    <div class="company-industry"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                </div>
            </div>

            <nav>
                <a href="dashboard_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                <a href="posting_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="12" r="8"></circle><line x1="12" y1="1" x2="12" y2="3"></line></svg>
                    Posting Lowongan
                </a>
                <a href="kelola_lowongan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    Kelola Lowongan
                </a>
                <a href="kandidat.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Kandidat
                </a>
                <a href="profil_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profil Perusahaan
                </a>
                <a href="analytics.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Analytics
                </a>
                <a href="pesan_perusahaan.php" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    Pesan
                </a>
                <div class="nav-divider"></div>
                <a href="pengaturan_perusahaan.php" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 6v6"></path><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path><path d="M1 12h6m6 0h6"></path><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path></svg>
                    Pengaturan
                </a>
                <a href="logout.php" class="nav-item nav-item-logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Keluar
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Daftar Kontak Chat -->
            <div class="message-list-container">
                <div class="message-list-header">
                    <h1 class="message-list-title">Pesan</h1>
                    <div class="message-search">
                        <svg class="message-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="searchContact" placeholder="Cari pesan..." oninput="filterContacts()">
                    </div>
                </div>
                <div class="message-list" id="contactList">
                    <div class="no-contacts">Memuat kontak...</div>
                </div>
            </div>

            <!-- Area Chat -->
            <div class="chat-container" id="chatArea">
                <?php if ($lawan): ?>
                    <div class="chat-header">
                        <div class="chat-header-info">
                            <div class="chat-avatar">
                                <?php if (!empty($lawan['foto_profil']) && file_exists('uploads/foto_profil/' . $lawan['foto_profil'])): ?>
                                    <img src="uploads/foto_profil/<?= htmlspecialchars($lawan['foto_profil']) ?>" alt="">
                                <?php else: ?>
                                    <?= strtoupper(substr($lawan['nama'], 0, 2)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="chat-user-info">
                                <h3><?= htmlspecialchars($lawan['nama']) ?></h3>
                                <p><?= htmlspecialchars($lawan['bidang_keahlian'] ?? ($lawan['role'] === 'pelamar' ? 'Pelamar' : 'Perusahaan')) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <div class="chat-date-divider">Memuat pesan...</div>
                    </div>
                    <div class="chat-input-container">
                        <div class="chat-input-wrapper">
                            <div class="chat-input">
                                <textarea id="messageInput" placeholder="Ketik pesan..." rows="1" onkeydown="handleEnter(event)"></textarea>
                            </div>
                            <button class="send-btn" id="sendBtn" onclick="kirimPesan()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="chat-empty">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p>Pilih percakapan untuk mulai chatting</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const MY_ID = <?= $id_perusahaan ?>;
        const LAWAN_ID = <?= $id_lawan ?>;
        let lastMsgId = 0;
        let pollingInterval = null;
        let allContacts = [];

        function loadContacts() {
            fetch('daftar_kontak.php')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    allContacts = data.contacts;
                    renderContacts(allContacts);
                })
                .catch(() => {
                    document.getElementById('contactList').innerHTML = '<div class="no-contacts">Belum ada pesan</div>';
                });
        }

        function renderContacts(contacts) {
            const list = document.getElementById('contactList');
            if (contacts.length === 0) {
                list.innerHTML = '<div class="no-contacts">Belum ada percakapan</div>';
                return;
            }

            list.innerHTML = contacts.map(c => {
                const isActive = c.id_user === LAWAN_ID;
                const unreadClass = c.unread > 0 ? 'unread' : '';
                const activeClass = isActive ? 'active' : '';
                const avatarHTML = c.foto_profil
                    ? `<img src="uploads/foto_profil/${c.foto_profil}" alt="">`
                    : c.initials;
                const label = c.bidang_keahlian ? `<span class="message-label">${c.bidang_keahlian}</span>` : '';

                return `
                    <div class="message-item ${unreadClass} ${activeClass}" onclick="openChat(${c.id_user})">
                        <div class="msg-avatar">${avatarHTML}</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">${c.nama}</span>
                                <span class="message-time">${c.waktu_relatif}</span>
                            </div>
                            <p class="message-preview">${c.pengirim_terakhir === MY_ID ? 'Anda: ' : ''}${c.pesan_terakhir}</p>
                            ${label}
                        </div>
                        ${c.unread > 0 ? '<div class="unread-badge"></div>' : ''}
                    </div>
                `;
            }).join('');
        }

        function filterContacts() {
            const q = document.getElementById('searchContact').value.toLowerCase();
            const filtered = allContacts.filter(c => c.nama.toLowerCase().includes(q) || c.pesan_terakhir.toLowerCase().includes(q));
            renderContacts(filtered);
        }

        function openChat(id) {
            window.location.href = `pesan_perusahaan.php?id_lawan=${id}`;
        }

        function loadMessages() {
            if (LAWAN_ID <= 0) return;
            const url = `ambil_pesan.php?id_lawan=${LAWAN_ID}` + (lastMsgId > 0 ? `&after=${lastMsgId}` : '');

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const container = document.getElementById('chatMessages');

                    if (lastMsgId === 0 && data.messages.length === 0) {
                        container.innerHTML = '<div class="chat-date-divider">Belum ada pesan. Mulai percakapan!</div>';
                        return;
                    }
                    if (lastMsgId === 0) container.innerHTML = '<div class="chat-date-divider">Hari ini</div>';

                    data.messages.forEach(msg => {
                        const bubble = document.createElement('div');
                        bubble.className = `message-bubble ${msg.is_mine ? 'sent' : 'received'}`;
                        const time = new Date(msg.tanggal_kirim).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        bubble.innerHTML = `
                            <div class="bubble-content">${msg.isi_pesan}</div>
                            <div class="bubble-time">${time}</div>
                        `;
                        container.appendChild(bubble);
                        if (msg.id_pesan > lastMsgId) lastMsgId = msg.id_pesan;
                    });
                    container.scrollTop = container.scrollHeight;
                });
        }

        function kirimPesan() {
            const input = document.getElementById('messageInput');
            const text = input.value.trim();
            if (!text || LAWAN_ID <= 0) return;

            const btn = document.getElementById('sendBtn');
            btn.disabled = true;

            const formData = new FormData();
            formData.append('id_penerima', LAWAN_ID);
            formData.append('isi_pesan', text);

            fetch('kirim_pesan.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    if (data.success) {
                        input.value = '';
                        input.style.height = '44px';
                        loadMessages();
                        loadContacts();
                    } else {
                        alert('Gagal kirim: ' + data.message);
                    }
                })
                .catch(() => { btn.disabled = false; });
        }

        function handleEnter(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); kirimPesan(); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const ta = document.getElementById('messageInput');
            if (ta) {
                ta.addEventListener('input', () => {
                    ta.style.height = '44px';
                    ta.style.height = Math.min(ta.scrollHeight, 120) + 'px';
                });
            }
        });

        loadContacts();
        if (LAWAN_ID > 0) {
            loadMessages();
            pollingInterval = setInterval(loadMessages, 2000);
            setInterval(loadContacts, 5000);
        }
    </script>
</body>

</html>