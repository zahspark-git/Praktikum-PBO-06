<?php
declare(strict_types=1);

abstract class Mahasiswa
{
    public function __construct(
        private string $nim,
        private string $nama,
        private string $prodi,
        private int $semester
    ) {
        if (trim($nim) === '' || trim($nama) === '' || trim($prodi) === '') {
            throw new InvalidArgumentException('NIM, Nama, dan Prodi wajib diisi.');
        }
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus berada di rentang 1–14.');
        }
        $this->nim = trim($nim);
        $this->nama = trim($nama);
        $this->prodi = trim($prodi);
    }

    public function getNim(): string { return $this->nim; }
    public function getNama(): string { return $this->nama; }
    public function getProdi(): string { return $this->prodi; }
    public function getSemester(): int { return $this->semester; }

    abstract public function hitungBiaya(): float;
    abstract public function getJenis(): string;
    abstract public function getUkt(): float;
    abstract public function getPotonganPersen(): float;
}