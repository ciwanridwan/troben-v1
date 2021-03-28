<?php

namespace Database\Seeders;

use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Seeder;

class TransportersTableSeeder extends Seeder
{
    const COMPOSES = [
        Partner::TYPE_BUSINESS => [
            Transporter::TYPE_BIKE,
            Transporter::TYPE_BIKE,
            Transporter::TYPE_PICKUP,
            Transporter::TYPE_PICKUP_BOX,
            Transporter::TYPE_MPV,
            Transporter::TYPE_WINGBOX,
        ],
        Partner::TYPE_TRANSPORTER => [
            Transporter::TYPE_BIKE,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->prepareSeederDependency();

        Partner::query()->with([
            'users' => fn(MorphToMany $query) => $query->wherePivot('role', UserablePivot::ROLE_DRIVER)
        ])
            ->get()
            ->each(fn(Partner $partner) => $this->createTransporters($partner, $partner->users));

        $this->command->table([
            'number',
            'type',
            'owned_by_partner',
            'driver(s) username',
        ], Transporter::query()->get()->map(fn (Transporter $transporter) => [
            $transporter->registration_number,
            $transporter->type,
            $transporter->partner->name,
            $transporter->drivers->map->username->implode(', '),
        ]));
    }

    public function createTransporters(Partner $partner, Collection $drivers)
    {
        $transporters = collect(self::COMPOSES[$partner->type] ?? [])->map(fn($transporterType) => Transporter::factory()->state([
            'partner_id' => $partner->id,
            'type' => $transporterType
        ])->create());

        $transporters->each(function (Transporter $transporter, $index) use ($drivers) {
            $reasonableKeyForDriver = $index % $drivers->count();
            $driver = $drivers->get($reasonableKeyForDriver);

            $transporter->users()->attach($driver, [
                'role' => UserablePivot::ROLE_DRIVER
            ]);
        });
    }

    private function prepareSeederDependency()
    {
        if (User::query()->count() === 0) {
            $this->call(UsersTableSeeder::class);
        }
    }
}
