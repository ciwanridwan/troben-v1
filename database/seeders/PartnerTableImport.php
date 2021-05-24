<?php

namespace Database\Seeders;

use App\Models\User;
use League\Csv\Reader;
use League\Csv\Statement;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Model;
use App\Models\Partners\Pivot\UserablePivot;

class PartnerTableImport extends Seeder
{
    protected $typeMapper = [
        'WAREHOUSE' => Partner::TYPE_POOL,
        'BISNIS' => Partner::TYPE_BUSINESS,
        'TRANSPORTER' => Partner::TYPE_TRANSPORTER,
        'SPACE' => Partner::TYPE_SPACE,
    ];

    /**
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public function loadFiles($filePath): Collection
    {
        $collection = new Collection();
        $csv = Reader::createFromPath($filePath);
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $path_file = __DIR__ . '/data/partner.csv';
        $partner = $this->loadFiles($path_file);

        $users = new Collection();


        $this->command->info("\n\nImport Partner Data from " . $path_file);
        foreach ($partner as $item) {
            DB::transaction(function () use ($item, $users) {

                $p = new Partner();
                $p->fill([
                    'name' => $item['name'],
                    'address' => $item['address'],
                    'type' => $this->typeMapper[$item['type']],
                    'code' => $item['code'],
                ]);
                $p->save();

                $u = new User();
                $u->fill([
                    'name' => $item['name'],
                    'username' => $item['username'],
                    'email' => $item['email'],
                    'phone' => $item['phonenumber'],
                    'password' => $item['password'],
                    'email_verified_at' => new \DateTime,
                    'verified_at' => new \DateTime
                ]);
                $u->save();

                $users->push($u);


                $pivot = new UserablePivot();
                $pivot->fill([
                    'user_id' => $u->id,
                    'userable_type' => Model::getActualClassNameForMorph(get_class($p)),
                    'userable_id' => $p->getKey(),
                    'role' => 'owner',
                ]);
                $pivot->save();



                $this->validatePartner($p, $item);
            });
        }

        $this->command->table(
            ['email', 'username', 'password', 'partner code', 'partner type', 'role'],
            $users->map(function (User $user, $index) use ($partner) {

                return [
                    $user->email,
                    $user->username,
                    $partner[$index]['password'],
                    $user->partners->pluck('code')->implode(', '),
                    $user->partners->pluck('type')->implode(', '),
                    $user->partners->pluck('pivot.role')->implode(', '),
                ];
            })
        );
    }

    /**
     * Validate partner type for creating warehouse and transporter.
     *
     * @param Partner $partner
     * @param array $original
     *
     * @return void
     */
    protected function validatePartner(Partner $partner, $original = []): void
    {
        if (in_array($partner->type, ['pool', 'space', 'business'])) {
            $w = new Warehouse();
            $w->partner_id = $partner->id;
            // $w->code = $original['code']; //data test
            // $w->name = $original['name']; //data test
            $w->is_pool = $partner->type == 'pool';
            $w->is_counter = in_array($partner->type, ['business', 'space']);
            $w->save();
        }

        if (in_array($partner->type, ['business', 'transporter'])) {
            $t = new Transporter();
            $t->partner_id = $partner->id;
            $t->registration_name = $original['name']; //data test
            $t->registration_number = 'stnk'; //data test
            $t->type = 'bike'; //data test
            $t->save();
        }
    }
}
