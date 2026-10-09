<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\District;
use App\Models\Pincode;

class PincodesTableSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure State Haryana is there
        $haryana = State::firstOrCreate(['id' => 1], ['name' => 'Haryana', 'status' => '1']);
        
        // 2. Add other States
        $delhi = State::firstOrCreate(['name' => 'Delhi'], ['status' => '1']);
        $maharashtra = State::firstOrCreate(['name' => 'Maharashtra'], ['status' => '1']);
        $up = State::firstOrCreate(['name' => 'Uttar Pradesh'], ['status' => '1']);

        // 3. Ensure District Rohtak is there
        $rohtak = District::firstOrCreate(['id' => 1], ['name' => 'Rohtak', 'state_id' => $haryana->id, 'status' => '1']);

        // 4. Add other Districts for Haryana
        $gurugram = District::firstOrCreate(['name' => 'Gurugram', 'state_id' => $haryana->id], ['status' => '1']);
        $faridabad = District::firstOrCreate(['name' => 'Faridabad', 'state_id' => $haryana->id], ['status' => '1']);

        // Districts for Delhi
        $newDelhi = District::firstOrCreate(['name' => 'New Delhi', 'state_id' => $delhi->id], ['status' => '1']);
        $southDelhi = District::firstOrCreate(['name' => 'South Delhi', 'state_id' => $delhi->id], ['status' => '1']);

        // Districts for Maharashtra
        $mumbai = District::firstOrCreate(['name' => 'Mumbai', 'state_id' => $maharashtra->id], ['status' => '1']);
        $pune = District::firstOrCreate(['name' => 'Pune', 'state_id' => $maharashtra->id], ['status' => '1']);

        // Districts for UP
        $noida = District::firstOrCreate(['name' => 'Noida', 'state_id' => $up->id], ['status' => '1']);

        // 5. Add Pincodes
        // Haryana - Rohtak
        $rohtakPins = ['124001', '124002', '124021', '124513'];
        foreach ($rohtakPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $haryana->id, 'district_id' => $rohtak->id, 'status' => '1']);
        }

        // Haryana - Gurugram
        $gurugramPins = ['122001', '122002', '122018', '122022'];
        foreach ($gurugramPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $haryana->id, 'district_id' => $gurugram->id, 'status' => '1']);
        }

        // Haryana - Faridabad
        $faridabadPins = ['121001', '121002', '121005'];
        foreach ($faridabadPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $haryana->id, 'district_id' => $faridabad->id, 'status' => '1']);
        }

        // Delhi - New Delhi
        $newDelhiPins = ['110001', '110002', '110003', '110005'];
        foreach ($newDelhiPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $delhi->id, 'district_id' => $newDelhi->id, 'status' => '1']);
        }

        // Delhi - South Delhi
        $southDelhiPins = ['110019', '110020', '110024'];
        foreach ($southDelhiPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $delhi->id, 'district_id' => $southDelhi->id, 'status' => '1']);
        }

        // Maharashtra - Mumbai
        $mumbaiPins = ['400001', '400002', '400008', '400011', '400020'];
        foreach ($mumbaiPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $maharashtra->id, 'district_id' => $mumbai->id, 'status' => '1']);
        }

        // Maharashtra - Pune
        $punePins = ['411001', '411002', '411004', '411014'];
        foreach ($punePins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $maharashtra->id, 'district_id' => $pune->id, 'status' => '1']);
        }

        // UP - Noida
        $noidaPins = ['201301', '201303', '201304'];
        foreach ($noidaPins as $pin) {
            Pincode::firstOrCreate(['pincode' => $pin], ['state_id' => $up->id, 'district_id' => $noida->id, 'status' => '1']);
        }
    }
}
