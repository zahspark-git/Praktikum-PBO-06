<?php
declare(strict_types=1);

require_once __DIR__ . '/mahasiswareguler.php';
require_once __DIR__ . '/mahasiswabeasiswa.php';

final class DataMahasiswa
{
    /** @var array<string, Mahasiswa> */
    private array $data = [];

    public function __construct(array $awal = [])
    {
        foreach ($awal as $item) {
            if ($item instanceof Mahasiswa) {
                $this->data[$item->getNim()] = $item;
            }
        }
    }

    public function tambah(Mahasiswa $m): bool
    {
        if (isset($this->data[$m->getNim()])) {
            return false;
        }
        $this->data[$m->getNim()] = $m;
        return true;
    }

    public function cari(string $nim): ?Mahasiswa
    {
        return $this->data[$nim] ?? null;
    }

    public function semua(): array
    {
        return $this->data;
    }

    public function ubah(string $nimLama, Mahasiswa $m): bool
    {
        if (!isset($this->data[$nimLama]) || $m->getNim() !== $nimLama) {
            return false;
        }
        $this->data[$nimLama] = $m;
        return true;
    }

    public function hapus(string $nim): bool
    {
        if (!isset($this->data[$nim])) {
            return false;
        }
        unset($this->data[$nim]);
        return true;
    }
}