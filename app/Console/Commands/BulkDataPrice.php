<?php

namespace App\Console\Commands;

use App\Jobs\Partners\Prices\BulkCreateOrUpdateDooring;
use App\Jobs\Partners\Prices\BulkCreateOrUpdateTransit;
use App\Jobs\Partners\Prices\DeleteExistingDooring;
use App\Jobs\Partners\Prices\DeleteExistingTransit;
use App\Models\Geo\SubDistrict;
use App\Models\Partners\Partner;
use App\Models\Partners\Prices\Dooring;
use App\Models\Partners\Prices\PriceModel;
use App\Models\Partners\Prices\Transit;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Helper\ProgressBar;

class BulkDataPrice extends Command
{
    use DispatchesJobs;

    private const TYPE_DELIVERY = "delivery";
    private const TYPE_TRANSIT = "transit";
    private const TYPE_DOORING = "dooring";

    private string $type;

    /**
     * @var string $file_path
     */
    private string $file_path;

    private Collection $prices;
    private Collection $partner_list;

    private ProgressBar $progressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:bulk
                            {type : Price for update (possible values: delivery,transit,dooring)}
                            {--F|file= : File path location, the type must be csv or xls}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk data price (insert or update)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \League\Csv\Exception
     * @throws \Exception
     */
    public function handle()
    {
        $time_start = microtime(true);
        $this->type = $this->argument('type');
        if (!in_array($this->type,self::getAvailableType())) {
            $this->error('Wrong type argument');
            return;
        }

        $this->file_path = $this->option('file');
        if (!$this->file_path) {
            $this->error('Option file is required');
            return;
        }

        $this->prices = $this->loadFiles();
        if ($this->type === self::TYPE_DELIVERY) {
            # TODO: delivery logic update
            $this->info("Updating delivery prices");

        } elseif ($this->type === self::TYPE_TRANSIT) {
            $this->partner_list = Partner::query()->where('type',Partner::TYPE_TRANSPORTER)->get(['id','code']);
            $this->info('Updating transit price');
            $this->updatePartnerPrice();
            $this->progressBar->finish();;
        } elseif ($this->type === self::TYPE_DOORING) {
            $this->partner_list = Partner::query()->whereIn('type',[Partner::TYPE_TRANSPORTER,Partner::TYPE_BUSINESS])->get(['id','code']);
            $this->info("Updating dooring prices");
            $this->updatePartnerPrice();
        }

        $this->info('Update price finished.');
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        $this->info('Total Execution Time:'.($execution_time*1000).'Milliseconds');
    }

    /**
     * @return string[]
     */
    private static function getAvailableType(): array
    {
        return [
            self::TYPE_DELIVERY,
            self::TYPE_TRANSIT,
            self::TYPE_DOORING
        ];
    }

    /**
     * @return Collection
     * @throws \League\Csv\Exception
     */
    public function loadFiles(): Collection
    {
        $collection = new Collection();

        $csv = Reader::createFromPath($this->file_path);
        $csv->setHeaderOffset(0);

        foreach ((new Statement())->process($csv) as $item) {

            $collection->add($item);
        }

        return $collection;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    private function updatePartnerPrice(): void
    {
        $this->prices = $this->prices->groupBy(['mitra_code']);
        $this->progressBar = $this->getOutput()->createProgressBar($this->prices->count());

        if ($this->type === self::TYPE_TRANSIT) $this->updatePartnerTransitPrices();
        elseif ($this->type === self::TYPE_DOORING) $this->updatePartnerDooringPrices();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    private function updatePartnerTransitPrices()
    {
        foreach ($this->prices as $key => $data_by_partner) {
            $this->warn("Updating transit price for $key\n");

            $availables = new Collection();
            $notAvailables = new Collection();
            $partner_id = $this->partner_list->where('code', $key)->first()->id;

            foreach ($data_by_partner as $data) {
                $tempData = Arr::except($data, ['mitra_code', 'origin_regency_id', 'destination_regency_id', 'route']);

                $data['partner_id'] = $partner_id;
                $data['shipment_type'] = $this->getConstShipmentTypeByRoute($data['route']);

                foreach ($tempData as $key => $value) {
                    $item = array_merge(
                        Arr::only($data, ['partner_id', 'origin_regency_id', 'destination_regency_id', 'shipment_type']),
                        [
                            'type' => $this->getConstTypeByColumn($key),
                            'value' => $value
                        ]
                    );
                    if ($value == '-') $notAvailables->add($item);
                    else $availables->add($item);
                }
            }

            $job = new BulkCreateOrUpdateTransit($availables->toArray());
            $this->dispatch($job);

            foreach ($notAvailables as $notAvailable) {
                $transit = Transit::where('partner_id',$notAvailable['partner_id'])
                    ->where('origin_regency_id',$notAvailable['origin_regency_id'])
                    ->where('destination_regency_id',$notAvailable['destination_regency_id'])
                    ->where('type',$notAvailable['type'])
                    ->where('shipment_type',$notAvailable['shipment_type'])
                    ->first();

                if ($transit) {
                    $deleteJob = new DeleteExistingTransit(Arr::except($notAvailable,'value'));
                    $this->dispatch($deleteJob);
                }
            }

            $this->progressBar->advance();
            $this->newLine(2);
        }
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    private function updatePartnerDooringPrices()
    {
        foreach ($this->prices as $key => $data_by_partner) {
            $this->warn("Updating dooring price for $key\n");

            $partner_id = $this->partner_list->where('code', $key)->first()->id;

            $data_per_origin = $data_by_partner->groupBy('origin_regency_id');
            foreach ($data_per_origin as $data_partner) {
                foreach ($data_partner as $data) {
                    $this->newLine();
                    $this->info($key.' destination '.$data['destination_district_id']);
                    $availables = new Collection();
                    $notAvailables = new Collection();

                    $tempData = Arr::except($data, ['mitra_code', 'origin_regency_id', 'destination_district_id']);

                    $data['partner_id'] = $partner_id;
                    $subDistricts = SubDistrict::query()->where('district_id',$data['destination_district_id'])->get(['id']);
                    $subBar = $this->getOutput()->createProgressBar($subDistricts->count());
                    foreach ($subDistricts as $subDistrict) {
                        foreach ($tempData as $keyTemp => $value) {
                            $item = array_merge(
                                Arr::only($data, ['partner_id', 'origin_regency_id']),
                                [
                                    'destination_sub_district_id' => $subDistrict->id,
                                    'type' => $this->getConstTypeByColumn($keyTemp),
                                    'value' => $value
                                ]
                            );
                            if ($value == '-') $notAvailables->add($item);
                            else $availables->add($item);
                        }
                        $subBar->advance();
                    }
                    $subBar->finish();
                    $job = new BulkCreateOrUpdateDooring($availables->toArray());
                    $this->dispatch($job);
                    foreach ($notAvailables as $notAvailable) {
                        $transit = Dooring::where('partner_id',$notAvailable['partner_id'])
                            ->where('origin_regency_id',$notAvailable['origin_regency_id'])
                            ->where('destination_sub_district_id',$notAvailable['destination_sub_district_id'])
                            ->where('type',$notAvailable['type'])
                            ->first();

                        if ($transit) {
                            $deleteJob = new DeleteExistingDooring(Arr::except($notAvailable,'value'));
                            $this->dispatch($deleteJob);
                        }
                    }
                }
            }


            $this->progressBar->advance();
            $this->newLine(2);
        }
    }

    /**
     * @param string $column
     * @return int
     * @throws \Exception
     */
    private function getConstTypeByColumn(string $column): int
    {
        if ($column == 'sla') {
            return PriceModel::TYPE_SLA;
        } elseif ($column == 'flat') {
            return PriceModel::TYPE_FLAT;
        } elseif ($column == 'tier_1') {
            return PriceModel::TYPE_TIER_1;
        } elseif ($column == 'tier_2') {
            return PriceModel::TYPE_TIER_2;
        } elseif ($column == 'tier_3') {
            return PriceModel::TYPE_TIER_3;
        } elseif ($column == 'tier_4') {
            return PriceModel::TYPE_TIER_4;
        } elseif ($column == 'tier_5') {
            return PriceModel::TYPE_TIER_5;
        } elseif ($column == 'tier_6') {
            return PriceModel::TYPE_TIER_6;
        } elseif ($column == 'tier_7') {
            return PriceModel::TYPE_TIER_7;
        } elseif ($column == 'tier_8') {
            return PriceModel::TYPE_TIER_8;
        } else {
            throw new \Exception("Wrong column definition: $column, data: ");
        }
    }

    /**
     * @param string $route
     * @return int
     * @throws \Exception
     */
    private function getConstShipmentTypeByRoute(string $route): int
    {
        if ($route == 'L') {
            return Transit::SHIPMENT_LAND;
        } elseif ($route == 'S') {
            return Transit::SHIPEMNT_SEA;
        } elseif ($route == 'A') {
            return Transit::SHIPMENT_AIRWAY;
        } else {
            throw new \Exception("Wrong SLA value: $route");
        }
    }
}
