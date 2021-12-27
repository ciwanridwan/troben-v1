<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerPerformancesTable extends Migration
{
    private array $newTables = [
        'partner_package_performances' => [
            'field' => 'package_id',
            'table' => 'packages'
        ],
        'partner_delivery_performances' => [
            'field' => 'delivery_id',
            'table' => 'deliveries'
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->newTables as $key => $newTable) {
            Schema::create($key, function (Blueprint $table) use ($newTable) {
                $table->unsignedBigInteger('partner_id');
                $table->unsignedBigInteger($newTable['field']);
                $table->unsignedSmallInteger('level')
                    ->default(1)
                    ->comment($this->levelComment());
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('deadline');
                $table->unsignedSmallInteger('status')
                    ->default(\App\Models\Partners\Performances\PerformanceModel::STATUS_ON_PROCESS)
                    ->comment($this->statusComment());
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();

                $table->foreign('partner_id')
                    ->references('id')
                    ->on('partners');

                $table->foreign($newTable['field'])
                    ->references('id')
                    ->on($newTable['table']);

                $table->primary(['partner_id',$newTable['field'],'level']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->newTables as $key => $newTable) {
            Schema::dropIfExists($key);
        }
    }

    private function levelComment(): string
    {
        return "
            1: alert 1
            2: alert 2
            3: last alert (shown for quality control dashboard)
            maximum level is 3.
        ";
    }

    /**
     * Reference on status const @\App\Models\Partners\Performances\PerformanceModel
     * @return string
     */
    private function statusComment(): string
    {
        return "
            1: on process
            5: reached
            10: failed
        ";
    }
}
