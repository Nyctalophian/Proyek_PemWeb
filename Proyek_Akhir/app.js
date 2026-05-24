import express from 'express';
import mysql from 'mysql2/promise'; // Tambahan driver MySQL

const app = express();
const PORT = 8000;

app.use(express.urlencoded({ extended: false }));

// ==============================================================================
// 1. DATABASE CONNECTION & MODEL (BACKEND LOGIC)
// ==============================================================================

// Membuat Connection Pool MySQL
const pool = mysql.createPool({
    host: 'localhost',      // Sesuaikan dengan host database Anda
    user: 'root',           // Sesuaikan dengan user database Anda
    password: '',           // Sesuaikan dengan password database Anda
    database: 'lost_and_found',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// MODEL: Menangani semua interaksi langsung ke Database MySQL
const ItemModel = {
    getAll: async () => {
        // Alias description sebagai desc agar sesuai dengan View sebelumnya
        const [rows] = await pool.query('SELECT id, name, description AS `desc`, location, date, category, status, finder FROM items ORDER BY id DESC');
        return rows;
    },
    getByFinder: async (username) => {
        const [rows] = await pool.query('SELECT id, name, description AS `desc`, location, date, category, status, finder FROM items WHERE finder = ? ORDER BY id DESC', [username]);
        return rows; 
    },
    add: async (itemData) => {
        const { name, desc, location, date, category, finder } = itemData;
        const status = "Tersedia";
        const [result] = await pool.query(
            'INSERT INTO items (name, description, location, date, category, status, finder) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [name, desc, location, date, category, status, finder]
        );
        return result.insertId;
    },
    updateStatus: async (itemId, newStatus) => {
        const [result] = await pool.query('UPDATE items SET status = ? WHERE id = ?', [newStatus, itemId]);
        return result.affectedRows;
    }
};

const ClaimModel = {
    add: async (claimData) => {
        const { itemId, claimer, proof } = claimData;
        const [result] = await pool.query(
            'INSERT INTO claims (item_id, claimer, proof) VALUES (?, ?, ?)',
            [itemId, claimer, proof]
        );
        
        // Update status barang di DB menjadi "Menunggu Verifikasi"
        await ItemModel.updateStatus(itemId, "Menunggu Verifikasi");
        return result.insertId;
    }
};

// Global state untuk menyimpan sesi
let isLoggedIn = false;
let currentUser = null;
let role = 'guest';


// ==============================================================================
// 2. VIEW (FRONTEND LOGIC)
// ==============================================================================

const renderLayout = (title, content, activeMenu) => {
    const menuItems = [
        { id: 'dashboard', name: 'Beranda', link: '/dashboard' },
        { id: 'items', name: 'Barang Temuan', link: '/items' },
        ...(isLoggedIn && role === 'mahasiswa' ? [
            { id: 'report', name: 'Lapor Penemuan', link: '/report' },
            { id: 'my-reports', name: 'Laporan Saya', link: '/my-reports' }
        ] : [])
    ];

    const menuHtml = menuItems.map(m => `
        <li style="margin-bottom: 15px;">
            <a href="${m.link}" style="text-decoration: none; color: ${activeMenu === m.id ? '#ff7f00' : '#555'}; font-weight: ${activeMenu === m.id ? 'bold' : 'normal'};">
                ${m.name}
            </a>
        </li>
    `).join('');

    const profileHtml = isLoggedIn ? `
        <hr style="margin: 20px 0;">
        <p style="color: #666; font-size: 14px;">Lain-Lain</p>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 15px;"><a href="/profile" style="text-decoration: none; color: #555;">Profil Saya</a></li>
            <li>
                <form action="/logout" method="POST">
                    <button type="submit" style="background: none; border: none; color: red; cursor: pointer; padding: 0; font-size: 16px;">Logout (${currentUser})</button>
                </form>
            </li>
        </ul>
    ` : `
        <hr style="margin: 20px 0;">
        <a href="/" style="text-decoration: none; color: #1a4f8b; font-weight: bold;">Kembali ke Login</a>
    `;

    return `
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>${title} - Lost & Found Filkom</title>
            <style>
                body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; display: flex; }
                .sidebar { width: 250px; background-color: #ffffff; height: 100vh; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); padding: 20px; box-sizing: border-box; }
                .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); box-sizing: border-box; }
                .logo { text-align: center; margin-bottom: 30px; color: #1a4f8b; font-weight: bold; font-size: 24px; }
                .logo span { color: #ff7f00; }
                .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
                .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
                .btn-primary { background-color: #1a4f8b; }
                .btn-warning { background-color: #ff7f00; }
                input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
                .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
            </style>
        </head>
        <body>
            <div class="sidebar">
                <div class="logo">LOST & <span>FOUND</span></div>
                <p style="color: #666; font-size: 14px;">Menu</p>
                <ul style="list-style: none; padding: 0;">
                    ${menuHtml}
                </ul>
                ${profileHtml}
            </div>
            <div class="main-content">
                ${content}
            </div>
        </body>
        </html>
    `;
};


// ==============================================================================
// 3. CONTROLLER LOGIC (BACKEND LOGIC)
// ==============================================================================

const cekStatusLogin = (req, res, next) => {
    if (!isLoggedIn) {
        return res.send(`
            <h2>Akses Ditolak</h2>
            <p>Anda harus login terlebih dahulu.</p>
            <a href="/">Kembali ke halaman utama</a>
        `);
    }
    next();
};

app.get('/', (req, res) => {
    if (isLoggedIn) return res.redirect('/dashboard');

    const authHtml = `
        <div style="max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #1a4f8b; margin-bottom: 30px;">LOST & <span style="color: #ff7f00;">FOUND</span></div>
            <form action="/login-sso" method="POST" style="margin-bottom: 15px;">
                <input type="text" name="username" placeholder="NIM / Email SSO Brawijaya" required>
                <button type="submit" class="btn btn-warning" style="width: 100%;">Login dengan SSO</button>
            </form>
            <form action="/login-guest" method="POST">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login sebagai Guest</button>
            </form>
        </div>
    `;
    res.send(authHtml);
});

app.post('/login-sso', (req, res) => {
    currentUser = req.body.username;
    isLoggedIn = true;
    role = 'mahasiswa';
    res.redirect('/dashboard');
});

app.post('/login-guest', (req, res) => {
    currentUser = 'Guest';
    isLoggedIn = true;
    role = 'guest';
    res.redirect('/dashboard');
});

app.post('/logout', (req, res) => {
    isLoggedIn = false;
    currentUser = null;
    role = 'guest';
    res.redirect('/');
});

app.get('/dashboard', cekStatusLogin, async (req, res) => {
    try {
        const items = await ItemModel.getAll();
        
        const content = `
            <div class="card" style="background-color: #ff7f00; color: white; border-radius: 10px;">
                <h2>SELAMAT DATANG, ${currentUser.toUpperCase()}!</h2>
                <p>Lost & Found merupakan suatu perangkat lunak yang dirancang untuk memfasilitasi penemuan barang hilang di lingkungan Fakultas Ilmu Komputer Universitas Brawijaya.</p>
            </div>
            <div class="grid-container" style="margin-top: 20px;">
                <div class="card" style="text-align: center;">
                    <h3 style="margin: 0; color: #666;">Barang Tersedia</h3>
                    <h1 style="margin: 10px 0; font-size: 48px; color: #1a4f8b;">${items.filter(i => i.status === 'Tersedia').length}</h1>
                </div>
                <div class="card" style="text-align: center;">
                    <h3 style="margin: 0; color: #666;">Total Barang Temuan</h3>
                    <h1 style="margin: 10px 0; font-size: 48px; color: #ff7f00;">${items.length}</h1>
                </div>
            </div>
        `;
        res.send(renderLayout('Beranda', content, 'dashboard'));
    } catch (error) {
        console.error(error);
        res.status(500).send("Terjadi kesalahan pada Database.");
    }
});

app.get('/items', cekStatusLogin, async (req, res) => {
    try {
        const items = await ItemModel.getAll();
        
        const itemsHtml = items.map(item => `
            <div class="card">
                <span style="background: ${item.status === 'Tersedia' ? '#e2f5e9' : '#fff3cd'}; color: ${item.status === 'Tersedia' ? '#28a745' : '#ffc107'}; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; float: right;">${item.status}</span>
                <h3>${item.name}</h3>
                <p style="color: #666;">${item.desc}</p>
                <p>📍 ${item.location} <br> 📅 ${item.date}</p>
                ${item.status === 'Tersedia' && role === 'mahasiswa' ? `<button class="btn btn-warning" style="width: 100%;">Klaim Barang</button>` : `<button class="btn" style="background-color: #e0e0e0; color: #888; width: 100%;" disabled>Akses Terbatas</button>`}
            </div>
        `).join('');

        const content = `
            <h2>Barang Temuan</h2>
            <div class="card">
                <input type="text" placeholder="Cari Barang (nama, kategori, deskripsi)...">
            </div>
            <div class="grid-container">${itemsHtml || '<p>Belum ada data barang.</p>'}</div>
        `;
        res.send(renderLayout('Barang Temuan', content, 'items'));
    } catch (error) {
        console.error(error);
        res.status(500).send("Terjadi kesalahan pada Database.");
    }
});

app.get('/report', cekStatusLogin, (req, res) => {
    if (role !== 'mahasiswa') return res.redirect('/items');

    const content = `
        <h2>Form Laporan Barang Temuan</h2>
        <div class="card" style="max-width: 600px;">
            <form action="/report" method="POST">
                <label>Nama Barang</label>
                <input type="text" name="name" placeholder="Contoh: Dompet Kulit" required>
                
                <label>Deskripsi</label>
                <textarea name="desc" placeholder="Jelaskan barang yang ditemukan..." required></textarea>
                
                <label>Lokasi Ditemukan</label>
                <input type="text" name="location" placeholder="Contoh: Gedung F 3.2" required>
                
                <label>Tanggal Ditemukan</label>
                <input type="date" name="date" required>
                
                <label>Kategori</label>
                <select name="category">
                    <option value="Elektronik">Elektronik</option>
                    <option value="Aksesoris">Aksesoris</option>
                    <option value="Dokumen">Dokumen</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
                
                <button type="submit" class="btn btn-warning" style="margin-top: 15px; width: 100%;">Submit Laporan</button>
            </form>
        </div>
    `;
    res.send(renderLayout('Lapor Penemuan', content, 'report'));
});

app.post('/report', cekStatusLogin, async (req, res) => {
    if (role !== 'mahasiswa') return res.status(403).send('Unauthorized');
    
    try {
        const { name, desc, location, date, category } = req.body;
        await ItemModel.add({ name, desc, location, date, category, finder: currentUser });
        res.redirect('/my-reports');
    } catch (error) {
        console.error(error);
        res.status(500).send("Gagal menyimpan data ke database.");
    }
});

app.get('/my-reports', cekStatusLogin, async (req, res) => {
    if (role !== 'mahasiswa') return res.redirect('/dashboard');

    try {
        const myItems = await ItemModel.getByFinder(currentUser);
        
        const itemsHtml = myItems.map(item => `
            <div class="card">
                <span style="background: #e2f5e9; color: #28a745; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; float: right;">Dipublikasikan</span>
                <h3>${item.name}</h3>
                <p>📍 ${item.location} <br> 📅 ${item.date}</p>
            </div>
        `).join('');

        const content = `
            <h2>Laporan Saya</h2>
            <div class="grid-container" style="margin-bottom: 20px;">
                 <div class="card" style="text-align: center;"><h2 style="color: #1a4f8b;">${myItems.length}</h2><p>Total Laporan Anda</p></div>
            </div>
            <div class="grid-container">
                ${itemsHtml || '<p>Belum ada barang yang Anda laporkan.</p>'}
            </div>
        `;
        res.send(renderLayout('Laporan Saya', content, 'my-reports'));
    } catch (error) {
        console.error(error);
        res.status(500).send("Terjadi kesalahan pada Database.");
    }
});

app.get('/profile', cekStatusLogin, (req, res) => {
    const content = `
        <h2>Profil Saya</h2>
        <div class="card" style="max-width: 600px;">
            <p><strong>Status:</strong> ${role === 'mahasiswa' ? 'Mahasiswa UB' : 'Tamu / Guest'}</p>
            <p><strong>Identitas (NIM/Username):</strong> ${currentUser}</p>
            <br>
            <button class="btn btn-primary" disabled>Ganti Password (Coming Soon)</button>
        </div>
    `;
    res.send(renderLayout('Profil', content, 'profile'));
});

app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});