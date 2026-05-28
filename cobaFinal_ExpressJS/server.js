// app.js (Server Express.js)
const express = require('express');
const path = require('path');
const mysql = require('mysql2/promise');

const app = express();
const PORT = 3000;

// Setup EJS sebagai Template Engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Middleware untuk file statis (CSS, JS, Gambar)
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Konfigurasi Database
const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'lostandfound_DB'
});

// Mock Session (Simulasi Login)
// Ubah isLoggedIn menjadi 'false' untuk melihat mode Guest (Tamu)
const mockSession = {
    isLoggedIn: true, 
    user: { id: 1, nama: 'Makoto Yuki', role: 'user' }
};

// Middleware Pembatasan Akses (Role-Based Access Control)
const requireAuth = (req, res, next) => {
    if (!mockSession.isLoggedIn) {
        return res.status(403).render('unauthorized', { 
            title: 'Akses Ditolak', 
            path: req.path,
            session: mockSession
        });
    }
    next();
};

// Middleware global untuk menyisipkan session ke semua view
app.use((req, res, next) => {
    res.locals.session = mockSession;
    next();
});

// ==========================================
// ROUTING EXPRESS.JS
// ==========================================

// 1. Menu: Beranda (Bisa diakses Guest & User)
app.get('/', async (req, res) => {
    // Menghitung statistik untuk dashboard
    const [rows] = await db.query(`
        SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu 
        FROM barang
    `);
    const stats = rows[0];

    res.render('beranda', { 
        title: 'Beranda - Lost & Found', 
        path: 'beranda',
        stats 
    });
});

// 2. Menu: Barang Temuan (Bisa diakses Guest & User, tapi Guest tidak bisa klaim)
app.get('/barang-temuan', async (req, res) => {
    const [barang] = await db.query("SELECT * FROM barang ORDER BY created_at DESC");
    res.render('barang-temuan', { 
        title: 'Barang Temuan', 
        path: 'barang-temuan',
        barang
    });
});

// 3. Menu: Lapor Penemuan (Hanya User Login)
app.get('/lapor-penemuan', requireAuth, (req, res) => {
    res.render('lapor-penemuan', { 
        title: 'Lapor Penemuan', 
        path: 'lapor-penemuan' 
    });
});

// 4. Menu: Laporan Saya (Hanya User Login)
app.get('/laporan-saya', requireAuth, async (req, res) => {
    // Pada aplikasi nyata, query ini difilter berdasarkan ID User yang login (WHERE user_id = ?)
    const [barangSaya] = await db.query("SELECT * FROM barang ORDER BY created_at DESC");
    
    let stats = { dipublikasikan: 0, menunggu: 0, total: barangSaya.length };
    barangSaya.forEach(b => {
        if (b.status === 'dipublikasikan') stats.dipublikasikan++;
        if (b.status === 'menunggu') stats.menunggu++;
    });

    res.render('laporan-saya', { 
        title: 'Laporan Saya', 
        path: 'laporan-saya',
        barang: barangSaya,
        stats
    });
});

// 5. Menu: Profil (Hanya User Login)
app.get('/profil', requireAuth, (req, res) => {
    res.render('profil', { 
        title: 'Profil Saya', 
        path: 'profil' 
    });
});

app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});