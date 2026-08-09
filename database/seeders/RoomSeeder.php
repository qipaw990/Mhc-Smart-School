<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\School;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        $rooms = [
            ['code' => 'R101', 'name' => 'Ruang Teori X RPL 1', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 1'],
            ['code' => 'R102', 'name' => 'Ruang Teori X RPL 2', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 1'],
            ['code' => 'R103', 'name' => 'Ruang Teori X RPL 3', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 1'],
            ['code' => 'R201', 'name' => 'Ruang Teori XI RPL 1', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 2'],
            ['code' => 'R202', 'name' => 'Ruang Teori XI RPL 2', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 2'],
            ['code' => 'R203', 'name' => 'Ruang Teori XI RPL 3', 'type' => 'classroom', 'capacity' => 36, 'location' => 'Gedung A Lantai 2'],
            ['code' => 'LAB-RPL-1', 'name' => 'Laboratorium Komputer RPL 1', 'type' => 'lab', 'capacity' => 40, 'location' => 'Gedung B Lantai 1'],
            ['code' => 'LAB-RPL-2', 'name' => 'Laboratorium Komputer RPL 2', 'type' => 'lab', 'capacity' => 40, 'location' => 'Gedung B Lantai 1'],
            ['code' => 'BENGKEL-TBSM', 'name' => 'Bengkel Otomotif TBSM', 'type' => 'workshop', 'capacity' => 40, 'location' => 'Gedung C Lt Dasar'],
            ['code' => 'LAB-AKL', 'name' => 'Laboratorium Perbankan & Akuntansi', 'type' => 'lab', 'capacity' => 36, 'location' => 'Gedung B Lantai 2'],
        ];

        foreach ($rooms as $r) {
            Room::create(array_merge($r, ['school_id' => $school->id, 'status' => 'active']));
        }
    }
}
