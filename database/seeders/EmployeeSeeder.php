<?php

namespace Database\Seeders;

use App\Jobs\Partners\CreateNewEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Partners\Partner;
use App\Models\Partners\Warehouse;
use Illuminate\Support\Collection;
use App\Models\Partners\Transporter;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;

class EmployeeSeeder extends Seeder
{
    use DispatchesJobs;
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws \League\Csv\Exception
     */
    public function run()
    {
        $partners = Partner::all();
        $partnerTotal = $partners->count();

        $this->command->info('=> Prepare create employee for each partner ');

        $count = 1;
        $partners->each(function (Partner $partner) use ($partnerTotal, &$count) {
            $this->command->warn('('.$count++.'/'.$partnerTotal.')');
            $this->command->info("\nCREATE EMPLOYEE FOR PARTNER [".$partner->code.'] '.$partner->name);
            $employees = new Collection();

            foreach (UserablePivot::getAvailableRoles() as  $role) {
                if ($role === UserablePivot::ROLE_OWNER) {
                    continue;
                }

                $user = User::factory()->makeOne();
                $input = $user->getAttributes();
                $input['password'] = 'password';
                $input['password_confirmation'] = 'password';
                $input['role'] = [$role];

                try {
                    $job = new CreateNewEmployee($partner, $input);

                    $this->dispatch($job);

                    $employees->push($job->employee);
                } catch (ValidationException $e) {
                }
            }
            $this->command->table(
                ['email', 'username', 'password', 'partner code', 'partner type', 'role'],
                $employees->map(function (User $user) {
                    return [
                        $user->email,
                        $user->username,
                        'password',
                        $user->partners->pluck('code')->implode(', '),
                        $user->partners->pluck('type')->implode(', '),
                        $user->partners->pluck('pivot.role')->implode(', '),
                    ];
                })
            );
        });
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
