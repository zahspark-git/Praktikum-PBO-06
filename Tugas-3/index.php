<?php
declare(strict_types=1);

require_once __DIR__ . '/datamahasiswa.php';

session_start();

$repo = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$error = '';
$success = '';
$selected = null;

function h(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $aksi = (string) ($_POST['aksi'] ?? '');

        if ($aksi === 'hapus') {
            $nim = trim((string) ($_POST['nim'] ?? ''));
            if (!$repo->hapus($nim)) {
                throw new InvalidArgumentException('Mahasiswa tidak ditemukan.');
            }
            $success = 'Data mahasiswa berhasil dihapus.';
        } elseif ($aksi === 'simpan') {
            $nim = trim((string) ($_POST['nim'] ?? ''));
            $nama = trim((string) ($_POST['nama'] ?? ''));
            $prodi = trim((string) ($_POST['prodi'] ?? ''));
            $semester = filter_var($_POST['semester'] ?? null, FILTER_VALIDATE_INT);
            $ukt = filter_var($_POST['ukt'] ?? null, FILTER_VALIDATE_FLOAT);
            $potongan = filter_var($_POST['potongan'] ?? '0', FILTER_VALIDATE_FLOAT);

            if ($semester === false || $ukt === false || $potongan === false) {
                throw new InvalidArgumentException('Semester, UKT, dan Potongan harus berupa angka.');
            }

            $jenis = (string) ($_POST['jenis'] ?? '');
            if ($jenis === 'reguler') {
                $m = new MahasiswaReguler($nim, $nama, $prodi, (int) $semester, (float) $ukt);
            } elseif ($jenis === 'beasiswa') {
                $m = new MahasiswaBeasiswa($nim, $nama, $prodi, (int) $semester, (float) $ukt, (float) $potongan);
            } else {
                throw new InvalidArgumentException('Jenis mahasiswa tidak valid.');
            }

            $mode = (string) ($_POST['mode'] ?? 'tambah');
            if ($mode === 'ubah') {
                $nimLama = trim((string) ($_POST['nim_lama'] ?? ''));
                if (!$repo->ubah($nimLama, $m)) {
                    throw new InvalidArgumentException('NIM tidak boleh diubah saat edit.');
                }
                $success = 'Data mahasiswa berhasil diperbarui.';
            } else {
                if (!$repo->tambah($m)) {
                    throw new InvalidArgumentException('NIM ' . $nim . ' sudah terdaftar.');
                }
                $success = 'Data mahasiswa berhasil ditambahkan.';
            }
        }

        $_SESSION['mahasiswa'] = $repo->semua();
        $_SESSION['flash'] = $success;
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

if (isset($_SESSION['flash'])) {
    $success = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if (isset($_GET['edit'])) {
    $selected = $repo->cari((string) $_GET['edit']);
}

$q = trim((string) ($_GET['q'] ?? ''));
$rows = array_filter($repo->semua(), static function (Mahasiswa $m) use ($q): bool {
    return $q === '' || stripos($m->getNim() . ' ' . $m->getNama() . ' ' . $m->getProdi(), $q) !== false;
});
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>CRUD Mahasiswa — Pewarisan</title>
    <style>
        body { font-family: sans-serif; max-width: 1050px; margin: 25px auto; padding: 0 15px; background: #f8fafc; color: #1e293b; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,.08); border: 1px solid #e2e8f0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button, .btn { padding: 8px 14px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-muted { background: #64748b; }
        .btn-danger { background: #dc2626; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f1f5f9; font-weight: bold; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 12px; background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h2>Pengelolaan Data Mahasiswa</h2>

    <?php if ($success): ?><div class="alert"><?= h($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

    <div class="card">
        <h3><?= $selected ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa Baru' ?></h3>
        <form method="post">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="mode" value="<?= $selected ? 'ubah' : 'tambah' ?>">
            <input type="hidden" name="nim_lama" value="<?= h($selected?->getNim() ?? '') ?>">

            <div class="grid">
                <div>
                    <label>NIM</label>
                    <input name="nim" required value="<?= h($selected?->getNim() ?? '') ?>" <?= $selected ? 'readonly style="background:#f1f5f9;"' : '' ?>>
                </div>
                <div>
                    <label>Nama Mahasiswa</label>
                    <input name="nama" required value="<?= h($selected?->getNama() ?? '') ?>">
                </div>
                <div>
                    <label>Program Studi</label>
                    <input name="prodi" required value="<?= h($selected?->getProdi() ?? '') ?>">
                </div>
                <div>
                    <label>Semester (1–14)</label>
                    <input name="semester" type="number" min="1" max="14" required value="<?= h((string)($selected?->getSemester() ?? 1)) ?>">
                </div>
                <div>
                    <label>Jenis Mahasiswa</label>
                    <select name="jenis" id="jenis">
                        <option value="reguler" <?= ($selected && $selected->getJenis() === 'Reguler') ? 'selected' : '' ?>>Reguler</option>
                        <option value="beasiswa" <?= ($selected && $selected->getJenis() === 'Beasiswa') ? 'selected' : '' ?>>Beasiswa</option>
                    </select>
                </div>
                <div>
                    <label>UKT Dasar (Rp)</label>
                    <input name="ukt" type="number" min="0" required value="<?= h((string)($selected?->getUkt() ?? 4000000)) ?>">
                </div>
                <div id="box-potongan">
                    <label>Potongan Beasiswa (%)</label>
                    <input name="potongan" id="potongan" type="number" min="0" max="100" value="<?= h((string)($selected?->getPotonganPersen() ?? 50)) ?>">
                </div>
            </div>

            <p style="margin-top: 15px;">
                <button type="submit"><?= $selected ? 'Simpan Perubahan' : 'Tambah Mahasiswa' ?></button>
                <?php if ($selected): ?>
                    <a class="btn btn-muted" href="index.php">Batal</a>
                <?php endif; ?>
            </p>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Mahasiswa (<?= count($rows) ?>)</h3>
        <form method="get" style="display:flex;gap:8px;margin-bottom:12px;">
            <input name="q" placeholder="Cari NIM, Nama, atau Prodi..." value="<?= h($q) ?>" style="max-width:300px">
            <button type="submit">Cari</button>
            <?php if ($q !== ''): ?>
                <a class="btn btn-muted" href="index.php">Reset</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prodi</th>
                    <th>Semester</th>
                    <th>Jenis</th>
                    <th>UKT Dasar</th>
                    <th>Potongan</th>
                    <th>Biaya Akhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $m): ?>
                <tr>
                    <td><?= h($m->getNim()) ?></td>
                    <td><?= h($m->getNama()) ?></td>
                    <td><?= h($m->getProdi()) ?></td>
                    <td><?= $m->getSemester() ?></td>
                    <td><strong><?= h($m->getJenis()) ?></strong></td>
                    <td><?= rupiah($m->getUkt()) ?></td>
                    <td><?= number_format($m->getPotonganPersen(), 0) ?>%</td>
                    <td><strong><?= rupiah($m->hitungBiaya()) ?></strong></td>
                    <td>
                        <a class="btn btn-muted" href="?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="nim" value="<?= h($m->getNim()) ?>">
                            <button class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;color:#64748b;padding:16px;">Belum ada data mahasiswa.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        const selJenis = document.getElementById('jenis');
        const boxPotongan = document.getElementById('box-potongan');
        const inputPotongan = document.getElementById('potongan');

        function sinkronJenis() {
            if (selJenis.value === 'beasiswa') {
                boxPotongan.style.display = 'block';
                inputPotongan.required = true;
            } else {
                boxPotongan.style.display = 'none';
                inputPotongan.required = false;
                inputPotongan.value = '0';
            }
        }

        selJenis.addEventListener('change', sinkronJenis);
        sinkronJenis();
    </script>
</body>
</html>