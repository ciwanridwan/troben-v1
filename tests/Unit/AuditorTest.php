<?php

namespace Tests\Unit;

use Tests\TestCase;
use RuntimeException;
use App\Auditor\Auditor;
use App\Auditor\Factory;

class AuditorTest extends TestCase
{
    public function test_on_service_provider_registration()
    {
        $auditor = app('trawlbens.auditor');
        $this->assertInstanceOf(Factory::class, $auditor);

        $auditor = app(Factory::class);
        $this->assertInstanceOf(Factory::class, $auditor);

        $auditor = app(Auditor::class);
        $this->assertInstanceOf(Factory::class, $auditor);
    }

    public function test_on_factory()
    {
        $factory = app('trawlbens.auditor');

        $auditor = $factory->make();
        $this->assertInstanceOf(Auditor::class, $auditor);
        $this->assertInstanceOf(Factory::class, $auditor->factory());
        $this->assertEquals($factory, $auditor->factory());
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
}
