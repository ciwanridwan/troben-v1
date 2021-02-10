<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use RuntimeException;
use App\Auditor\Auditor;
use App\Auditor\Factory;
use App\Auditor\AuditModel;
use App\Models\Customers\Customer;
use App\Auditor\AuditorServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_on_service_provider_registration()
    {
        $auditor = app('trawlbens.auditor');
        $this->assertInstanceOf(Factory::class, $auditor);

        $auditor = app(Factory::class);
        $this->assertInstanceOf(Factory::class, $auditor);

        $auditor = app(Auditor::class);
        $this->assertInstanceOf(Factory::class, $auditor);


        $provider = new AuditorServiceProvider(app());
        $this->assertIsArray($provider->provides());
        $this->assertContains('trawlbens.auditor', $provider->provides());
        $this->assertContains('command.auditor', $provider->provides());
    }

    public function test_on_factory()
    {
        $factory = app('trawlbens.auditor');

        $auditor = $factory->make();
        $this->assertInstanceOf(Auditor::class, $auditor);
        $this->assertInstanceOf(Factory::class, $auditor->factory());
        $this->assertEquals($factory, $auditor->factory());

        $this->assertInstanceOf(AuditModel::class, $factory->newAuditModel());
        $this->assertInstanceOf(Builder::class, $factory->query());

        $this->assertEquals(AuditModel::class, config('auditor.model'));
    }

    public function test_on_auditor_instance()
    {
        $factory = app('trawlbens.auditor');
        $auditor = $factory->make();

        $this->assertCount(4, $auditor::getAuditType());
        config()->set('auditor.audit_type.something', 'something');
        $this->assertCount(5, $auditor::getAuditType());

        $this->expectException(RuntimeException::class);
        $auditor->type('Something weird...');
    }

    public function test_on_audit_operation()
    {
        $user = User::factory(1)->create()->first();
        $customer = Customer::factory(1)->create()->first();

        // on action create
        /** @var \App\Auditor\Auditor $auditor */
        $auditor = app('trawlbens.auditor')->make();
        $auditor->log('customer create', $customer, $user, Auditor::AUDIT_TYPE_CREATE, true);

        // on action update
        $customer->fill([
            'name' => 'changing this',
        ]);
        $customer->save();

        $auditor = app('trawlbens.auditor')->make();
        $auditor->log('customer update', $customer, $user, Auditor::AUDIT_TYPE_UPDATE, true);

        // assert database integrity
        $this->assertDatabaseCount('audits', 2);
        $this->assertEquals(2, $customer->audits()->count());

        // assert model
        $model = AuditModel::query()->first();
        $this->assertEquals($model->performer->getKey(), $user->getKey());
        $this->assertEquals($model->auditable->getKey(), $customer->getKey());

        // assert on command
        $this->artisan('auditor:prune --days=-90')
            ->expectsQuestion('Are you sure?', 'yes')
            ->expectsOutput('Audit logs has been pruned!')
            ->assertExitCode(0);

        $this->assertEquals(0, $customer->audits()->count());
    }
}
