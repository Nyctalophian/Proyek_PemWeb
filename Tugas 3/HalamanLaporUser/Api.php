<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "lostandfound_DB";

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi gagal: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
    $method = 'PUT';
}

$action = $_GET['action'] ?? '';

function handleUpload($uploadDir) {
    if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload gagal, kode error: ' . $_FILES['gambar']['error']]);
        exit;
    }

    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ext  = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['gambar']['tmp_name']);

    if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
        http_response_code(400);
        echo json_encode(['error' => 'Format gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.']);
        exit;
    }

    if ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Ukuran gambar maks 5 MB.']);
        exit;
    }

    $namaFile = uniqid('img_', true) . '.' . $ext;
    move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . $namaFile);
    return $namaFile;
}

function deleteOldImage($uploadDir, $namaGambar) {
    if ($namaGambar) {
        $path = $uploadDir . basename($namaGambar);
        if (file_exists($path)) unlink($path);
    }
}

switch ($method) {

    case 'GET':
        if ($action === 'stats') {
            $dipublikasikan = $conn->query("SELECT COUNT(*) FROM barang WHERE status='dipublikasikan'")->fetchColumn();
            $menunggu       = $conn->query("SELECT COUNT(*) FROM barang WHERE status='menunggu'")->fetchColumn();
            $total          = $conn->query("SELECT COUNT(*) FROM barang")->fetchColumn();
            echo json_encode([
                'dipublikasikan' => (int)$dipublikasikan,
                'menunggu'       => (int)$menunggu,
                'total'          => (int)$total,
            ]);

        } elseif ($action === 'detail') {
            $id   = intval($_GET['id'] ?? 0);
            $stmt = $conn->prepare("SELECT * FROM barang WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row  = $stmt->fetch();
            echo json_encode($row ?: ['error' => 'Data tidak ditemukan']);

        } else {
            $search   = '%' . ($_GET['search']   ?? '') . '%';
            $kategori = $_GET['kategori'] ?? '';
            $status   = $_GET['status']   ?? '';

            $sql    = "SELECT * FROM barang WHERE (nama LIKE :search OR deskripsi LIKE :search OR lokasi LIKE :search)";
            $params = [':search' => $search];
            if ($kategori) { $sql .= " AND kategori = :kategori"; $params[':kategori'] = $kategori; }
            if ($status)   { $sql .= " AND status   = :status";   $params[':status']   = $status;   }
            $sql .= " ORDER BY created_at DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = $_POST;

        $required = ['nama', 'deskripsi', 'lokasi', 'kategori', 'tanggal_ditemukan'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' wajib diisi"]);
                exit;
            }
        }

        $namaGambar = handleUpload($uploadDir);

        $stmt = $conn->prepare(
            "INSERT INTO barang (nama, deskripsi, lokasi, kategori, status, tanggal_ditemukan, gambar)
             VALUES (:nama, :deskripsi, :lokasi, :kategori, :status, :tanggal, :gambar)"
        );
        $stmt->execute([
            ':nama'      => htmlspecialchars($data['nama']),
            ':deskripsi' => htmlspecialchars($data['deskripsi']),
            ':lokasi'    => htmlspecialchars($data['lokasi']),
            ':kategori'  => htmlspecialchars($data['kategori']),
            ':status'    => 'menunggu',
            ':tanggal'   => $data['tanggal_ditemukan'],
            ':gambar'    => $namaGambar,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => (int)$conn->lastInsertId(),
            'message' => 'Barang berhasil dilaporkan!',
        ]);
        break;

    case 'PUT':
        $data = $_POST;
        $id   = intval($data['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID tidak valid']);
            exit;
        }

        $stmtOld = $conn->prepare("SELECT gambar FROM barang WHERE id = :id");
        $stmtOld->execute([':id' => $id]);
        $old = $stmtOld->fetch();

        $namaGambar = handleUpload($uploadDir);

        if ($namaGambar) {
            deleteOldImage($uploadDir, $old['gambar'] ?? null);
        } else {
            $namaGambar = $old['gambar'] ?? null;
        }

        $stmt = $conn->prepare(
            "UPDATE barang SET nama=:nama, deskripsi=:deskripsi, lokasi=:lokasi,
             kategori=:kategori, status=:status, tanggal_ditemukan=:tanggal, gambar=:gambar
             WHERE id=:id"
        );
        $stmt->execute([
            ':nama'      => htmlspecialchars($data['nama']),
            ':deskripsi' => htmlspecialchars($data['deskripsi']),
            ':lokasi'    => htmlspecialchars($data['lokasi']),
            ':kategori'  => htmlspecialchars($data['kategori']),
            ':status'    => $data['status'],
            ':tanggal'   => $data['tanggal_ditemukan'],
            ':gambar'    => $namaGambar,
            ':id'        => $id,
        ]);
        echo json_encode([
            'success' => true,
            'rows'    => $stmt->rowCount(),
            'message' => 'Data berhasil diperbarui!',
        ]);
        break;

    case 'DELETE':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID tidak valid']);
            exit;
        }

        $stmtImg = $conn->prepare("SELECT gambar FROM barang WHERE id = :id");
        $stmtImg->execute([':id' => $id]);
        $row = $stmtImg->fetch();
        deleteOldImage($uploadDir, $row['gambar'] ?? null);

        $stmt = $conn->prepare("DELETE FROM barang WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode([
            'success' => true,
            'rows'    => $stmt->rowCount(),
            'message' => 'Data berhasil dihapus!',
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method tidak diizinkan']);
}

$conn = null;