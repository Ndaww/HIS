<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\EquipmentPreventiveType;
use App\Models\MasterEquipment;
use App\Models\MasterEquipmentType;
use App\Models\MasterPreventive;
use App\Models\MasterRoom;
use App\Models\Ticket;
use App\Models\TicketSubstitution;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
            Department::create(['name' => 'IT']),
            Department::create(['name' => 'HRD']),
            Department::create(['name' => 'Finance']),
        ]);

        // Buat 10 user
        User::create([
            'nik' => "admin",
            'password' => bcrypt(1234),
            'name' => "admin",
            'email' => "admin@example.com",
            'phone' => "087889643945",
            'department_id' => $departments->random()->id,
            'is_active' => true,
        ]);
        $users = collect();
        for ($i = 1; $i <= 10; $i++) {
            $users->push(User::create([
                'nik' => "User $i",
                'password' => bcrypt(1234),
                'name' => "User $i",
                'email' => "user$i@example.com",
                'phone' => "08123456789$i",
                'department_id' => $departments->random()->id,
                'is_active' => true,
            ]));
        }

        // Set kepala departemen (acak dari user)
        foreach ($departments as $department) {
            $department->head_id = $users->random()->id;
            $department->save();
        }

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
                'assigned_employee_id' => $users->random()->id,
                'department_id' => $dept->id,
                'status' => $statuses[array_rand($statuses)],
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => now()->subDays(rand(0, 10)),
                'updated_at' => now(),
            ]);
        }

        // Buat 10 eskalasi
        foreach ($tickets->take(10) as $ticket) {
            $from = $users->random();
            do {
                $to = $users->random();
            } while ($from->id === $to->id);

            TicketSubstitution::create([
                'ticket_id' => $ticket->id,
                'from_user_id' => $from->id,
                'to_user_id' => $to->id,
                'reason' => 'Pegawai sedang cuti',
            ]);
        }

        // Master Room
        foreach (range(1, 3) as $lantai) {
            foreach (range(1, 10) as $no) {
                MasterRoom::create([
                    'name' => 'Ruangan ' . str_pad($no, 2, '0', STR_PAD_LEFT),
                    'floor' => 'Lantai ' . $lantai,
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
        foreach ($typeMap as $etype => $typeId) {
            foreach (range(1, 10) as $i) {
                MasterEquipment::create([
                    'name' => $etype . ' ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'serial_number' => Str::upper(Str::random(3)) . '-' . rand(10000,99999),
                    'room_id' => $roomIds[array_rand($roomIds)],
                    'equipment_type_id' => $typeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
