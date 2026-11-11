<?php
declare(strict_types=1);

require_once __DIR__ . '/mahasiswa.php';

final class MahasiswaReguler extends Mahasiswa
{
    public function __construct(
        string $nim,
        string $nama,
        string $prodi,
        int $semester,
        private float $ukt
    ) {
        parent::__construct($nim, $nama, $prodi, $semester);
        if ($ukt < 0) {
            throw new InvalidArgumentException('Nominal UKT tidak boleh bernilai negatif.');
        }
    }

    public function getUkt(): float { return $this->ukt; }
    public function getPotonganPersen(): float { return 0.0; }
    public function hitungBiaya(): float { return $this->ukt; }
    public function getJenis(): string { return 'Reguler'; }
}