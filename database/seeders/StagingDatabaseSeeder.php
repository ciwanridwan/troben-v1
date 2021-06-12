<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Packages\PackagesTableSeeder;
use Database\Seeders\Packages\CashierInChargeSeeder;
use Database\Seeders\Packages\AssignedPackagesSeeder;
use Database\Seeders\Packages\CustomerInChargeSeeder;
use Database\Seeders\Packages\InTransit\DriverAssignedSeeder;
use Database\Seeders\Packages\InTransit\Drivers\DriverLoadPackageSeeder;
use Database\Seeders\Packages\InTransit\PackageAssignedToManifestSeeder;
use Database\Seeders\Packages\InTransit\PartnerAssignedDriverToDeliverySeeder;
use Database\Seeders\Packages\InTransit\PartnerAssignedToDeliverySeeder;
use Database\Seeders\Packages\InTransit\RequestPartnerSeeder;
use Database\Seeders\Packages\WarehouseInChargeSeeder;
use Database\Seeders\Packages\PostPayment\PackedSeeder;
use Database\Seeders\Packages\PostPayment\ManifestSeeder;
use Database\Seeders\Packages\PostPayment\PostPaymentSeeder;

class StagingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        UsersTableSeeder::$COUNT = 3;

        $this->call([
            PaymentGatewaySeeder::class
        ]);

        $this->command->getOutput()->title('Common seeder');
        $this->call([
            UsersTableSeeder::class,
            GeoTableSimpleSeeder::class,
            CustomersTableSeeder::class,
            ServiceTableSeeder::class,
            PriceTableSimpleSeeder::class,
            ProductsTableSeeder::class,
        ]);



        PackagesTableSeeder::$CUSTOMER_PACKAGES = 4;
        $this->command->getOutput()->title('Pickup flow seeder');
        $this->call([
            PackagesTableSeeder::class,
            TransportersTableSeeder::class,
            AssignedPackagesSeeder::class,
            WarehouseInChargeSeeder::class,
            CashierInChargeSeeder::class,
            CustomerInChargeSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Post payment seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            ManifestSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Driver Pacakge seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            RequestPartnerSeeder::class,
            PartnerAssignedToDeliverySeeder::class,
            PartnerAssignedDriverToDeliverySeeder::class,
            DriverAssignedSeeder::class,
            DriverLoadPackageSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Partner Assigned Driver to Manifest seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            RequestPartnerSeeder::class,
            PartnerAssignedToDeliverySeeder::class,
            PartnerAssignedDriverToDeliverySeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Assigned Partner to Manifest seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            RequestPartnerSeeder::class,
            PartnerAssignedToDeliverySeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Transporter Request seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            RequestPartnerSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Assigned Driver to Manifest seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            DriverAssignedSeeder::class,
        ]);

        PostPaymentSeeder::$CUSTOMER_PACKAGES = 1;
        $this->command->getOutput()->title('Package Manifested Seeder');
        $this->call([
            PostPaymentSeeder::class,
            PackedSeeder::class,
            PackageAssignedToManifestSeeder::class,
        ]);

        $this->call(PartnerTableImport::class);
    }
}
