<?php
declare(strict_types=1);

require_once __DIR__ . '/mahasiswa.php';

final class MahasiswaBeasiswa extends Mahasiswa
{
    public function __construct(
        string $nim,
        string $nama,
        string $prodi,
        int $semester,
        private float $ukt,
        private float $potonganPersen
    ) {
        parent::__construct($nim, $nama, $prodi, $semester);
        if ($ukt < 0) {
            throw new InvalidArgumentException('Nominal UKT tidak boleh bernilai negatif.');
        }
        if ($potonganPersen < 0 || $potonganPersen > 100) {
            throw new InvalidArgumentException('Potongan beasiswa harus berada di rentang 0–100%.');
        }
    }

    public function getUkt(): float { return $this->ukt; }
    public function getPotonganPersen(): float { return $this->potonganPersen; }
    public function hitungBiaya(): float {
        return $this->ukt * (1 - ($this->potonganPersen / 100));
    }
    public function getJenis(): string { return 'Beasiswa'; }
}