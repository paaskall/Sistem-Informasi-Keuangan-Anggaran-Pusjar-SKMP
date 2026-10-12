<?php

namespace Database\Seeders;

use App\Models\JenisPengadaan;
use App\Models\KategoriKendala;
use App\Models\KomponenBelanja;
use App\Models\Program;
use App\Models\Status;
use App\Models\SubKegiatan;
use App\Models\Tahapan;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUnits();
        $this->seedJenisPengadaan();
        $this->seedTahapan();
        $this->seedStatus();
        $this->seedKategoriKendala();
        $this->seedPrograms();
        $this->seedSubKegiatans();
        $this->seedKomponenBelanjas();
    }

    private function seedUnits(): void
    {
        foreach (['Bagian Keuangan', 'Bagian Umum', 'Bagian Perencanaan'] as $nama) {
            Unit::updateOrCreate(['nama' => $nama]);
        }
    }

    private function seedJenisPengadaan(): void
    {
        foreach (['Kontrak', 'Barang', 'Jasa'] as $nama) {
            JenisPengadaan::updateOrCreate(['nama' => $nama]);
        }
    }

    private function seedTahapan(): void
    {
        foreach (['Penunjukan', 'Tender', 'Evaluasi', 'Penetapan'] as $nama) {
            Tahapan::updateOrCreate(['nama' => $nama]);
        }
    }

    private function seedStatus(): void
    {
        foreach (['Draft', 'Proses', 'Selesai', 'Batal'] as $nama) {
            Status::updateOrCreate(['nama' => $nama]);
        }
    }

    private function seedKategoriKendala(): void
    {
        foreach (['Administrasi', 'Teknis', 'Anggaran', 'SDM'] as $nama) {
            KategoriKendala::updateOrCreate(['nama' => $nama]);
        }
    }

    private function seedPrograms(): void
    {
        $programs = [
            ['kode' => '7916.ADI.001', 'nama' => 'Seleksi dan Uji Kompetensi Jabatan Fungsional Bidang Pengembangan Kapasitas dan Pembelajaran ASN'],
            ['kode' => '7916.FAC.001', 'nama' => 'Pelatihan Struktural Kepemimpinan'],
            ['kode' => '7916.FAC.002', 'nama' => 'Pelatihan Dasar CPNS'],
            ['kode' => '7916.FAC.004', 'nama' => 'Pelatihan Teknis dan Fungsional'],
            ['kode' => '7916.CAN.001', 'nama' => 'Sarana Bidang Teknologi Informasi dan Komunikasi'],
            ['kode' => '7916.EBA.962', 'nama' => 'Layanan Umum'],
            ['kode' => '7916.EBA.994', 'nama' => 'Layanan Perkantoran'],
            ['kode' => '7918.EBB.951', 'nama' => 'Layanan Sarana Internal'],
            ['kode' => '7918.EBB.971', 'nama' => 'Layanan Prasarana Internal'],
            ['kode' => '7919.EBC.954', 'nama' => 'Layanan Manajemen SDM'],
        ];

        foreach ($programs as $data) {
            Program::updateOrCreate(['kode' => $data['kode']], $data);
        }
    }

    private function seedSubKegiatans(): void
    {
        $progFAC001 = Program::where('kode', '7916.FAC.001')->first();
        $progEBA962 = Program::where('kode', '7916.EBA.962')->first();
        $progEBA994 = Program::where('kode', '7916.EBA.994')->first();
        $progEBB951 = Program::where('kode', '7918.EBB.951')->first();
        $progEBB971 = Program::where('kode', '7918.EBB.971')->first();

        $subKegiatans = [
            ['program_id' => $progFAC001->id, 'kode' => '052', 'nama' => 'Pelaksanaan Pelatihan Kepemimpinan Nasional Tk. II'],
            ['program_id' => $progFAC001->id, 'kode' => '053', 'nama' => 'Pelaksanaan Pelatihan Kepemimpinan Administrator'],
            ['program_id' => $progFAC001->id, 'kode' => '054', 'nama' => 'Pelaksanaan Pelatihan Kepemimpinan Pengawas'],

            ['program_id' => $progEBA962->id, 'kode' => '053', 'nama' => 'Pelaksanaan Pengelolaan PNBP'],
            ['program_id' => $progEBA962->id, 'kode' => '054', 'nama' => 'Pelaksanaan Pemeliharaan PNBP'],

            ['program_id' => $progEBA994->id, 'kode' => '001', 'nama' => 'Gaji dan Tunjangan'],
            ['program_id' => $progEBA994->id, 'kode' => '002', 'nama' => 'Operasional dan Pemeliharaan Perkantoran'],

            ['program_id' => $progEBB951->id, 'kode' => '051', 'nama' => 'Pengadaan Peralatan Fasilitas Perkantoran'],

            ['program_id' => $progEBB971->id, 'kode' => '051', 'nama' => 'Pembangunan/Rehab/Renovasi Gedung dan Bangunan'],
        ];

        foreach ($subKegiatans as $data) {
            SubKegiatan::updateOrCreate(
                ['program_id' => $data['program_id'], 'kode' => $data['kode']],
                $data
            );
        }
    }

    private function seedKomponenBelanjas(): void
    {
        $komponens = [
            ['kode' => 'B.BAHAN', 'nama' => 'Belanja Bahan'],
            ['kode' => 'B.HONOR', 'nama' => 'Belanja Honor Output Kegiatan'],
            ['kode' => 'B.NON.OP', 'nama' => 'Belanja Barang Non Operasional Lainnya'],
            ['kode' => 'B.SEWA', 'nama' => 'Belanja Sewa'],
            ['kode' => 'B.JASA.PRO', 'nama' => 'Belanja Jasa Profesi'],
            ['kode' => 'B.PD', 'nama' => 'Belanja Perjalanan Dinas Biasa'],
            ['kode' => 'B.MODAL', 'nama' => 'Belanja Modal Peralatan dan Mesin'],
            ['kode' => 'B.PML.GDG', 'nama' => 'Belanja Biaya Pemeliharaan Gedung dan Bangunan'],
            ['kode' => 'B.PML.ALAT', 'nama' => 'Belanja Pemeliharaan Peralatan dan Mesin'],
            ['kode' => 'B.PERS.GDG', 'nama' => 'Belanja Barang Persediaan Pemeliharaan Gedung dan Bangunan'],
            ['kode' => 'B.PERS.ALAT', 'nama' => 'Belanja Barang Persediaan Pemeliharaan Peralatan dan Mesin'],
            ['kode' => 'B.EKSTRA', 'nama' => 'Belanja Peralatan dan Mesin - Ekstrakomptabel'],
            ['kode' => 'B.PPNPN', 'nama' => 'Jasa PPNPN'],
            ['kode' => 'B.PERAWATAN.GDG', 'nama' => 'Perawatan Gedung Kantor'],
            ['kode' => 'B.PERAWATAN.KND', 'nama' => 'Perawatan Kendaraan Bermotor'],
            ['kode' => 'B.PERAWATAN.SRN', 'nama' => 'Perawatan Sarana Gedung'],
            ['kode' => 'B.LANGGANAN', 'nama' => 'Langganan Daya dan Jasa'],
            ['kode' => 'B.OPERASIONAL', 'nama' => 'Operasional Perkantoran dan Pimpinan'],
            ['kode' => 'B.PENGELOLAAN', 'nama' => 'Pengelolaan Barang dan Jasa'],
            ['kode' => 'B.PEKERJAAN', 'nama' => 'Pekerjaan Konstruksi'],
            ['kode' => 'B.POS', 'nama' => 'Belanja Pengiriman Surat Dinas Pos Pusat'],
        ];

        foreach ($komponens as $data) {
            KomponenBelanja::updateOrCreate(['kode' => $data['kode']], $data);
        }
    }
}