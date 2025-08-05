<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EquipmentPreventiveType;
use App\Models\MasterEquipment;
use App\Models\MasterEquipmentType;
use App\Models\MasterPatient;
use App\Models\MasterPreventive;
use App\Models\MasterRoom;
use App\Models\Ticket;
use App\Models\TicketSubstitution;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Buat Department
        $departments = collect([
            Department::create(['name' => 'IT','head_id' => 2]),
            Department::create(['name' => 'HRD', 'head_id' => 3]),
            Department::create(['name' => 'Finance', 'head_id' => 4]),
            Department::create(['name' => 'Maintenance', 'head_id' => 5]),
            Department::create(['name' => 'Perawat', 'head_id' => 6]),
            Department::create(['name' => 'GA', 'head_id' => 7]),
        ]);

        // Buat 10 user
        User::create([
            'nik' => "admin",
            'password' => bcrypt(1234),
            'name' => "admin",
            'email' => "admin@example.com",
            'phone' => "87889643945",
            'department_id' => 1,
            'is_active' => true,
        ]);

        User::create(['nik' => "000001",'password' => bcrypt(1234),'name' => "Atasan IT",'email' => "atasanit@example.com",'phone' => "87889643945",'department_id' => 1,'is_active' => true,]);
        User::create(['nik' => "000002",'password' => bcrypt(1234),'name' => "Atasan HRD",'email' => "atasanhrd@example.com",'phone' => "87889643945",'department_id' => 2,'is_active' => true,]);
        User::create(['nik' => "000003",'password' => bcrypt(1234),'name' => "Atasan Finance",'email' => "atasanfinance@example.com",'phone' => "87889643945",'department_id' => 3,'is_active' => true,]);
        User::create(['nik' => "000004",'password' => bcrypt(1234),'name' => "Atasan Maintenance",'email' => "atasanmaintenance@example.com",'phone' => "87889643945",'department_id' => 4,'is_active' => true,]);
        User::create(['nik' => "000005",'password' => bcrypt(1234),'name' => "Atasan Perawat",'email' => "atasanperawat@example.com",'phone' => "87889643945",'department_id' => 5,'is_active' => true,]);
        User::create(['nik' => "000006",'password' => bcrypt(1234),'name' => "Atasan GA",'email' => "atasanga@example.com",'phone' => "87889643945",'department_id' => 6,'is_active' => true,]);
        $users = collect();
        for ($i = 1; $i <= 10; $i++) {
            $users->push(User::create([
                'nik' => "User $i",
                'password' => bcrypt(1234),
                'name' => "User $i",
                'email' => "user$i@example.com",
                'phone' => "87889643945",
                'department_id' => $departments->random()->id,
                'is_active' => true,
            ]));
        }

        // Set kepala departemen (acak dari user)
        // foreach ($departments as $department) {
        //     $department->head_id = $users->random()->id;
        //     $department->save();
        // }

        // Buat 30 ticket
        $statuses = ['open', 'in_progress', 'pending', 'solved', 'closed'];
        $priorities = ['low', 'medium', 'high'];

        $users = User::all();
        $departments = Department::all();

        $bulan = now()->format('n');
        $bulanRomawi = [1=>'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$bulan];
        $tahun = now()->format('Y');


        $tickets = collect();
        for ($i = 1; $i <= 30; $i++) {
            $dept = $departments->random();
            $departmentName = strtoupper($dept->short_name ?? $dept->name);
            $formattedNumber = str_pad($i, 4, '0', STR_PAD_LEFT);
            $ticketNumber = "TIK-{$formattedNumber}/{$departmentName}/{$bulanRomawi}/{$tahun}";

            Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => "Masalah #$i",
                'description' => "Deskripsi masalah nomor $i",
                'requester_id' => $users->random()->id,
                // 'assigned_employee_id' => $users->random()->id,
                'department_id' => $dept->id,
                'status' => 'open',
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => now()->subDays(rand(0, 10)),
                'updated_at' => now(),
            ]);
        }

        // Buat 10 eskalasi
        // foreach ($tickets->take(10) as $ticket) {
        //     $from = $users->random();
        //     do {
        //         $to = $users->random();
        //     } while ($from->id === $to->id);

        //     TicketSubstitution::create([
        //         'ticket_id' => $ticket->id,
        //         'from_user_id' => $from->id,
        //         'to_user_id' => $to->id,
        //         'reason' => 'Pegawai sedang cuti',
        //     ]);
        // }

        // Master Room
        $class = ['VVIP', 'VIP', 'KELAS 1', 'KELAS 2','KELAS 3'];
        foreach (range(2, 3) as $lantai) {
            foreach (range(1, 5) as $no) {
                MasterRoom::create([
                    'name' => 'Ruangan ' . str_pad($no, 2, '0', STR_PAD_LEFT),
                    'floor' => 'Lantai ' . $lantai,
                    'class' => $class[array_rand($class)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (range(1, 1) as $lantai) {
            foreach (range(1, 5) as $no) {
                MasterRoom::create([
                    'name' => 'Ruangan ' . str_pad($no, 2, '0', STR_PAD_LEFT),
                    'floor' => 'Lantai ' . $lantai,
                    'class' => $class[array_rand($class)],
                    'status' => 'preventive',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Master Tipe Equipment
        $equipmentTypes = ['AC', 'Genset', 'Infus Pump'];
        foreach ($equipmentTypes as $type) {
            MasterEquipmentType::create([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Mater Tipe Preventive
        // Preventive Types
        $preventives = [
            ['name' => 'Cek Freon', 'for' => 'AC'],
            ['name' => 'Ganti Filter', 'for' => 'AC'],
            ['name' => 'Cek Oli', 'for' => 'Genset'],
            ['name' => 'Uji Beban', 'for' => 'Genset'],
            ['name' => 'Kalibrasi', 'for' => 'Infus Pump'],
            ['name' => 'Tes Alarm', 'for' => 'Infus Pump'],
        ];

        foreach ($preventives as $preventive) {
            MasterPreventive::create([
                'name' => $preventive['name'],
                'description' => 'Untuk ' . $preventive['for'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Master Equipment Preventive Pivot
        $typeMap = MasterEquipmentType::pluck('id', 'name');
        $preventiveMap = MasterPreventive::pluck('id', 'name');

        $pivotMap = [
            'AC' => ['Cek Freon', 'Ganti Filter'],
            'Genset' => ['Cek Oli', 'Uji Beban'],
            'Infus Pump' => ['Kalibrasi', 'Tes Alarm']
        ];

        foreach ($pivotMap as $etype => $actions) {
            foreach ($actions as $act) {
                EquipmentPreventiveType::create([
                    'equipment_type_id' => $typeMap[$etype],
                    'preventive_type_id' => $preventiveMap[$act],
                ]);
            }
        }

        // Mater Equipment
        // Master Equipments (10 per jenis)
        $roomIds = MasterRoom::pluck('id')->toArray();
        foreach ($roomIds as $roomId) {
            foreach ($typeMap as $etype => $typeId) {
                foreach (range(1, 3) as $i) {
                    MasterEquipment::create([
                        'name' => $etype . ' ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'serial_number' => Str::upper(Str::random(3)) . '-' . rand(10000,99999),
                        'room_id' => $roomId,
                        'equipment_type_id' => $typeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // pasien
        $genders = ['L', 'P'];

        for ($i = 0; $i < 10; $i++) {
            MasterPatient::create([
                'name' => 'Pasien ' . Str::random(5),
                'gender' => $genders[array_rand($genders)],
                'birth_date' => Carbon::now()->subYears(rand(18, 60))->subDays(rand(1, 365))->format('Y-m-d'),
                'no_ktp' => '3204' . rand(1000000000, 9999999999),
                'no_bpjs' => rand(0, 1) ? 'BPJS' . rand(1000000000, 9999999999) : null,
                'address' => rand(0, 1) ? 'Jalan ' . Str::random(10) : null,
                'phone' => rand(0, 1) ? '08' . rand(1000000000, 9999999999) : null,
            ]);
        }
    }
}
