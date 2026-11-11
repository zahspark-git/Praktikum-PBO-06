<?php
// 1. Kelas Induk (Pegawai)
abstract class Pegawai {
    public function __construct(
        private string $id,
        private string $nama
    ) {}

    public function getId(): string { return $this->id; }
    public function getNama(): string { return $this->nama; }

    abstract public function hitungGaji(): float;
    abstract public function getStatus(): string;
}

// 2. Kelas Anak 1 (Pegawai Tetap)
class PegawaiTetap extends Pegawai {
    public function __construct(string $id, string $nama, private float $gajiPokok) {
        parent::__construct($id, $nama); // Panggil data dari induk
    }

    public function hitungGaji(): float {
        return $this->gajiPokok;
    }

    public function getStatus(): string {
        return "Pegawai Tetap";
    }
}

// 3. Kelas Anak 2 (Pegawai Kontrak)
class PegawaiKontrak extends Pegawai {
    public function __construct(string $id, string $nama, private float $upahPerJam, private int $jamKerja) {
        parent::__construct($id, $nama); // Panggil data dari induk
    }

    public function hitungGaji(): float {
        return $this->upahPerJam * $this->jamKerja;
    }

    public function getStatus(): string {
        return "Pegawai Kontrak";
    }
}

// 4. Uji Coba Objek & Tampilkan Hasil
$budi = new PegawaiTetap("P01", "Budi Santoso", 5000000);
$dewi = new PegawaiKontrak("P02", "Dewi Lestari", 35000, 160);

echo $budi->getNama() . " (" . $budi->getStatus() . ") - Gaji: Rp " . number_format($budi->hitungGaji(), 0, ',', '.') . "<br>";
echo $dewi->getNama() . " (" . $dewi->getStatus() . ") - Gaji: Rp " . number_format($dewi->hitungGaji(), 0, ',', '.') . "<br>";