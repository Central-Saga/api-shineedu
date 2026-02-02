<?php

namespace Database\Seeders;

use App\Modules\HR\Domain\Models\Employee as Karyawan;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                "user" => [
                    "user_name" => "I Gusti Made Ayu Anggun Tiara Pratini",
                    "user_email" => "ayuanggun233@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087755738270",
                    "alamat" => "BTN Senapahan",
                    "tanggal_lahir" => "2001-03-13",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Putu ayu krisna wulandari",
                    "user_email" => "ayukrisnawulandari283@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085829029265",
                    "alamat" => "Br. Dinas pitra, ds. Pitra, kec.penebel",
                    "tanggal_lahir" => "2000-10-19",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ade Ninik Ismayani",
                    "user_email" => "adeninikismayani3@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "083119585654",
                    "alamat" => "Banjar Dinas Tua, Desa Tua, Kecamatan Marga, Tabanan, Bali",
                    "tanggal_lahir" => "2000-05-15",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Putu Cendani Jelita Ruparti",
                    "user_email" => "Cendanijelita@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087836013246",
                    "alamat" => "Br.Dukuh kec. Penebel Kab.Tabanan",
                    "tanggal_lahir" => "2002-11-30",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Wayan Linda Maharani",
                    "user_email" => "nwlindamaharani04@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085792989032",
                    "alamat" => "Jalan Patimura no 10",
                    "tanggal_lahir" => "2001-04-04",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI WAYAN WAHYU ASTARI",
                    "user_email" => "niwayanwahyuastai@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085928877958",
                    "alamat" => "Banjar Dinas Kuwum Tegallingah, desa kuwum, kecamatan Marga",
                    "tanggal_lahir" => "1999-03-03",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Luh Kade Surya Dwianggreni",
                    "user_email" => "dwianggreniii2@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085738358146",
                    "alamat" => "Banjar Dinas Belumbang Kaja, Desa Belumbang, Kec. Kerambitan, Kab. Tabanan",
                    "tanggal_lahir" => "2002-12-10",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI WAYAN YATI LESMINA DEWI",
                    "user_email" => "yatilesminadewi@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "081262755268",
                    "alamat" => "BR. DINAS PENATAHAN KAJA, DESA PENATAHAN, PENEBEL, TABANAN, BALI",
                    "tanggal_lahir" => "1992-02-21",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Wayan Widia Wulandari K, S.S",
                    "user_email" => "widiawulandarikarta@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087862040820",
                    "alamat" => "Br Puseh Desa Kediri Kecamatan Kediri Kabupaten Tabanan Bali",
                    "tanggal_lahir" => "2002-03-28",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Luh Putu Ari Permata Dewi, S.S.",
                    "user_email" => "arypermata24@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "083117270349",
                    "alamat" => "Br. Mengening, Desa Nyitdah, Kediri, Tabanan",
                    "tanggal_lahir" => "2001-01-24",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "PUTRI INTAN SUMADEWI",
                    "user_email" => "Kimputri23@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "082340137389",
                    "alamat" => "Br. Dinas buruan kaja",
                    "tanggal_lahir" => "1999-10-23",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI LUH GEDE RITA PINAYANTI",
                    "user_email" => "pinayantirita@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "081529595170",
                    "alamat" => "Br Pangkung, Pejaten, Kediri",
                    "tanggal_lahir" => "1999-08-06",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Made Ayu Sri Widhi Antari",
                    "user_email" => "mawarsrisri6@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085936654888",
                    "alamat" => "Br. Dinas Megati Kelod, Desa Megati, Kec. Selemadeg Timur, Kab. Tabanan, Bali.",
                    "tanggal_lahir" => "2006-11-21",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "MELINDA",
                    "user_email" => "melindalouis508@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085782667888",
                    "alamat" => "Perum permata hijaul",
                    "tanggal_lahir" => "1988-03-11",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Putu Anjar Astriani Dewi",
                    "user_email" => "putuanjar07@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "0881037090707",
                    "alamat" => "Jl. Raya Alas Kedaton, Perum Griya Loka No.4, Br. Dalem Kerti, Ds. Kukuh, Kec. Marga, Tabanan",
                    "tanggal_lahir" => "2002-09-18",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Dwi Jayanti",
                    "user_email" => "dwijayanti9195@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "0895700575222",
                    "alamat" => "Jalan Ciung Wanara, Banjar Dinas Banjar Anyar, Kediri, Tabanan",
                    "tanggal_lahir" => "1999-10-23",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Putu Tania Erika Putri",
                    "user_email" => "erikaaputri099@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085934569006",
                    "alamat" => "Br. Dangin Jelinjing, Desa Belalang",
                    "tanggal_lahir" => "2005-09-12",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI MADE RAHMITA PUTRI",
                    "user_email" => "rahmitaputri633@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085967018341",
                    "alamat" => "BANJAR DINAS KUWUM TEGALLINGGAH",
                    "tanggal_lahir" => "2003-12-18",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI KOMANG PUTRI ANTARI",
                    "user_email" => "putriantari524@gmail.con",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "088703091325",
                    "alamat" => "Pandak Bandung Kediri Tabanan",
                    "tanggal_lahir" => "1998-04-02",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "I Gusti Ayu Putri Aswikawati",
                    "user_email" => "putriaswika@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085858146698",
                    "alamat" => "Jalan Cempaka Putih Dauh Peken Tabanan",
                    "tanggal_lahir" => "2003-05-21",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Luh Putu Puan Maharani",
                    "user_email" => "puanmaharani709@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "081803645900",
                    "alamat" => "Wongaya Gede, Penebel, Tabanan",
                    "tanggal_lahir" => "2005-01-06",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "I PUTU ADI NITA ADNYANA",
                    "user_email" => "hantuchova15@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "+62 812-3936-2191",
                    "alamat" => "Jalan akasia 9.no.20",
                    "tanggal_lahir" => "1993-09-12",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "ANIS SULIYANI",
                    "user_email" => "anissuliya@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087843328390",
                    "alamat" => "Jln. Blambangan No.9 Delod Peken Kota Tabanan",
                    "tanggal_lahir" => "2000-07-12",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Kadek Mena Rahayu",
                    "user_email" => "menarahayu22@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087703566844",
                    "alamat" => "Pandak Bandung",
                    "tanggal_lahir" => "2005-04-22",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Ni Putu Desy Prashanti",
                    "user_email" => "desyprashanti@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "0881037736115",
                    "alamat" => "Banjar Dinas Pengembungan",
                    "tanggal_lahir" => "2001-12-24",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "I Putu Wira Budhi Guna Ariyasa",
                    "user_email" => "wirbud1134@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "081337547273",
                    "alamat" => "Jl. Timbul, No. 28, Delod Peken, Tabanan",
                    "tanggal_lahir" => "2003-02-19",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "NI PUTU INTAN PUSPITA",
                    "user_email" => "piiintanpuspita@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Staff"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "087762200951",
                    "alamat" => "Jl. Gunung Tangkuban Perahu Perum Tegal Indah Permai blok 7 no 5",
                    "tanggal_lahir" => "2003-09-28",
                    "divisi" => "Non-Coding",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "Muhammad Nasir",
                    "user_email" => "muhammadnasir130816@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "085337842418",
                    "alamat" => "Kediri, Tabanan, Bali",
                    "tanggal_lahir" => "1999-07-27",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ],
            [
                "user" => [
                    "user_name" => "KADEK MAWAR SOPIANI",
                    "user_email" => "mawarsopiana07@gmail.com",
                    "user_password" => "password123",
                    "user_role" => "Teacher"
                ],
                "employee" => [
                    "kode_karyawan" => null,
                    "kategori_karyawan" => "tetap",
                    "subtipe_kontrak" => null,
                    "tipe_gaji" => "bulanan",
                    "gaji_pokok" => null,
                    "bank_nama" => null,
                    "bank_no_rekening" => null,
                    "nomor_hp" => "083117525579",
                    "alamat" => "Jalan Rama Delod Peken Tabanan",
                    "tanggal_lahir" => "2000-09-26",
                    "divisi" => "Teaching",
                    "status" => "aktif"
                ]
            ]
        ];


        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);

        foreach ($data as $item) {
            $userData = $item['user'];
            $employeeData = $item['employee'];

            // 1. Create/Find User
            $user = User::firstOrCreate(
                ['email' => $userData['user_email']],
                [
                    'name' => strtoupper($userData['user_name']), // All caps as seen in other systems often
                    'password' => Hash::make($userData['user_password'] ?? 'shineedu123'),
                    'status' => 'Aktif',
                    'email_verified_at' => now(),
                ]
            );

            // 2. Assign Role
            if (!empty($userData['user_role'])) {
                $user->assignRole($userData['user_role']);
            }

            // 3. Generate Kode Karyawan (DDMMYY + 4 digit random)
            $dob = $employeeData['tanggal_lahir'] ? date('dmy', strtotime($employeeData['tanggal_lahir'])) : date('dmy');
            $kodeKaryawan = $dob . rand(1000, 9999);

            // 4. Create Karyawan
            Karyawan::create([
                'user_id' => $user->id,
                'kode_karyawan' => $kodeKaryawan,
                'kategori_karyawan' => $employeeData['kategori_karyawan'],
                'subtipe_kontrak' => $employeeData['subtipe_kontrak'],
                'tipe_gaji' => $employeeData['tipe_gaji'],
                'gaji_pokok' => $employeeData['gaji_pokok'] ?? 0,
                'bank_nama' => $employeeData['bank_nama'],
                'bank_no_rekening' => $employeeData['bank_no_rekening'],
                'nomor_hp' => $employeeData['nomor_hp'],
                'alamat' => $employeeData['alamat'],
                'tanggal_lahir' => $employeeData['tanggal_lahir'],
                'divisi' => $employeeData['divisi'],
                'status' => $employeeData['status'],
            ]);
        }
    }
}
