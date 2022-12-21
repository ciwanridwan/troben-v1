<?php

use App\Models\Partners\Performances\Delivery;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldTypeToPartnerDeliveryPerformancesTable extends Migration
{
    public const TYPE_MB_WAREHOUSE_PACKING = 'mb_warehouse_packing';
    public const TYPE_MB_DRIVER_TO_TRANSIT = 'mb_driver_to_transit';
    public const TYPE_MTAK_OWNER_TO_DRIVER = 'mtak_owner_to_driver';
    public const TYPE_MTAK_DRIVER_TO_WAREHOUSE = 'mtak_driver_to_warehouse';
    public const TYPE_MPW_WAREHOUSE_GOOD_RECEIVE = 'mpw_warehouse_good_receive';
    public const TYPE_MPW_WAREHOUSE_REQUEST_TRANSPORTER = 'mpw_warehouse_request_transporter';
    public const TYPE_DRIVER_DOORING = 'driver_dooring';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_delivery_performances', function (Blueprint $table) {
            $table->enum('type', $this->getTypes())->nullable();
        });

        Schema::table('partner_package_performances', function (Blueprint $table) {
            $table->enum('type', $this->getTypes())->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_delivery_performances', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('partner_package_performances', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Set types for determine sla
     */
    public function getTypes(): array
    {
        return [
            self::TYPE_MB_WAREHOUSE_PACKING,
            self::TYPE_MB_DRIVER_TO_TRANSIT,
            self::TYPE_MTAK_OWNER_TO_DRIVER,
            self::TYPE_MTAK_DRIVER_TO_WAREHOUSE,
            self::TYPE_MPW_WAREHOUSE_GOOD_RECEIVE,
            self::TYPE_MPW_WAREHOUSE_REQUEST_TRANSPORTER,
            self::TYPE_DRIVER_DOORING
        ];
    }
}
