<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketSubstitution;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
