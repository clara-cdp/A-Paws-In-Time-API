<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\Item;
use App\Models\Interaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);


        $chapters = ['chapter_1.json', 'chapter_2.json', 'chapter_3.json', 'chapter_4.json', 'chapter_5.json'];


        foreach ($chapters as $file) {
            $this->seedChapter($file);
        }

        $this->command->info('All chapters seeded successfully!');
    }


    private function seedChapter(string $fileName): void
    {
        $path = database_path("data/{$fileName}");

        if (!file_exists($path)) {
            $this->command->error("File not found: $path");
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        // --- SEED ROOMS ---
        foreach (($data['rooms'] ?? []) as $room) {
            Room::updateOrCreate(
                ['name' => $room['name']],
                [
                    'description' => $room['description'],
                    'image_url'   => $room['image_url'],
                    'room_type'   => $room['room_type'] ?? 'all',
                ]
            );
        }

        // --- SEED ITEMS ---
        foreach (($data['items'] ?? []) as $item) {
            $roomId = !empty($item['room']) ? Room::where('name', $item['room'])->value('id') : null;

            Item::withoutGlobalScopes()->updateOrCreate(
                ['name_id' => $item['name_id']],
                [
                    'description' => $item['description'],
                    'image_url'   => $item['image_url'] ?? null,
                    'is_portable' => $item['is_portable'] ?? false,
                    'is_visible'  => $item['is_visible'] ?? true,
                    'room_id'     => $roomId,
                ]
            );
        }

        // --- SEED INTERACTIONS ---
        foreach (($data['interactions'] ?? []) as $interaction) {
            $targetItemId = Item::withoutGlobalScopes()->where('name_id', $interaction['target_item'])->value('id');

            if ($targetItemId) {
                Interaction::updateOrCreate([
                    'verb_trigger'  => $interaction['verb_trigger'],
                    'item_id'       => $targetItemId,
                    'step_required' => $interaction['step_required'] ?? 0,
                ], [
                    'next_step'        => $interaction['next_step'] ?? 0,
                    'reward'           => $interaction['reward'] ?? null,
                    'required_item_id' => isset($interaction['required_item']) ? Item::withoutGlobalScopes()->where('name_id', $interaction['required_item'])->value('id') : null,
                    'unlocked_item_id' => isset($interaction['unlocked_item']) ? Item::withoutGlobalScopes()->where('name_id', $interaction['unlocked_item'])->value('id') : null,
                    'target_room_id'   => isset($interaction['target_room']) ? Room::where('name', $interaction['target_room'])->value('id') : null,
                ]);
            }
        }
    }
    
}
