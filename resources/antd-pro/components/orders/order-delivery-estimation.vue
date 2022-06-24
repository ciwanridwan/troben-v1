<template>
  <div>
    <h3>Estimasi Biaya Pengiriman</h3>
    <a-card>
      <a-card>
        <a-row type="flex">
          <a-col :span="4">
            <information-icon></information-icon>
          </a-col>
          <a-col :span="18">
            <h4>
              <b>
                {{ origin_regency_name }}
                <a-icon type="arrow-right" />
                {{ destination_regency_name }}
              </b>
            </h4>
            <h5>Harga perkilo yaitu Rp. {{ tierPrice }}/kg</h5>
          </a-col>
        </a-row>
      </a-card>

      <a-row :style="'padding:24px 0px'">
        <a-col>
          <order-estimation v-if="this.price" :price="this.price"></order-estimation>
        </a-col>
      </a-row>

      <a-row type="flex">
        <a-col :span="leftColumn">
          <b>Deskripsi</b>
        </a-col>
        <a-col :span="rightColumn">
          <b>Jumlah</b>
        </a-col>
      </a-row>

      <!-- items -->
      <template v-for="(item, index) in items">
        <a-row type="flex" :key="index">
          <a-col :span="leftColumn">
            {{ item.name }}
          </a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ item.qty }} Koli
          </a-col>

          <a-col :span="leftColumn"> Charge Weight x {{ item.qty }}</a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ item.weight_borne_total }} Kg
          </a-col>

          <a-col :span="24">
            <template v-for="(handling, index) in item.handling">
              <a-row :key="index" type="flex">
                <a-col :span="leftColumn">
                  Biaya Packing {{ handlings[handling.type] }} x
                  {{ item.qty }}
                </a-col>
                <a-col :span="rightColumn" class="trawl-text-right">
                  {{ currency(handling.price * item.qty) }}
                </a-col>
              </a-row>
            </template>
            <a-row v-if="item.is_insured" type="flex">
              <a-col :span="leftColumn"> Asuransi x {{ item.qty }} </a-col>
              <a-col :span="rightColumn" class="trawl-text-right">
                {{ currency(getInsurancePrice(item.prices)) }}
              </a-col>
            </a-row>
          </a-col>
        </a-row>
        <a-divider :key="index + '-divider'"></a-divider>
      </template>

      <!-- sub total biaya -->
      <a-row type="flex">
        <a-col :span="leftColumn"> Biaya Kirim </a-col>
        <a-col :span="rightColumn" class="trawl-text-right">
          {{ currency(total_weight * tierPrice) }}
        </a-col>
        <a-col :span="leftColumn"> Biaya Penjemputan </a-col>
        <a-col :span="rightColumn" class="trawl-text-right">
          {{ currency(getPickupFee) }}
        </a-col>

        <a-divider />
        <a-col :span="leftColumn"> Sub total biaya </a-col>
        <a-col :span="rightColumn" class="trawl-text-right">
          {{ currency(subTotalPrice) }}
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>
<script>
import informationIcon from "../icons/informationIcon.vue";
import deliveryIcon from "../icons/deliveryIcon";
import OrderEstimation from "./order-estimation.vue";
import { handlings } from "../../data/handlings";
import {
  getHandlingPrice,
  getInsurancePrice,
  getServicePrice,
  getTierPrice,
  getSubTotalItems,
  getHandlings
} from "../../functions/orders";

export default {
  components: { informationIcon, deliveryIcon, OrderEstimation },
  props: {
    package: {
      type: Object,
      default: () => { }
    },
    leftColumn: {
      type: Number,
      default: 15
    },
    rightColumn: {
      type: Number,
      default: 9
    },
    price: {
      type: Object,
      default: () => null
    }
  },
  data() {
    return {
      handlings,
      isBankCharge: true,
      pickup : 0
    };
  },
  computed: {
    origin_regency_name() {
      return this.package?.origin_regency?.name;
    },
    destination_regency_name() {
      return this.package?.destination_regency?.name;
    },
    items() {
      return this.package?.items;
    },
    tierPrice() {
      return this.package?.tier_price;
    },
    servicePrice() {
      return this.package?.service_price;
    },
    subTotalPrice() {
      if (this.packageStatus != 'draft') {
        return this.package?.total_amount + this.serviceDiscount;
      } else {
        return this.package?.total_amount;
      }
    },
    bankCharge() {
      if (this.package?.payments.length === 0) {
        this.isBankCharge = false;
        return;
      } else {
        this.isBankCharge = true;
        return this.package?.payments[0].payment_admin_charges;
      }
    },
    packageStatus() {
      return this.package?.status;
    },
    serviceDiscount() {
      return this.package?.discount_service_price;
    },
    total_weight() {
      return this.package?.total_weight;
    },
    getPaymentStatus() {
      return this.package?.payment_status;
    },
    getPickupFee() {
      var pickupPrice = this.package?.prices;
      pickupPrice.forEach(pickupFee => {
        if (pickupFee.type === 'delivery') {
          this.pickup = pickupFee.amount;
        }
      });
      return this.pickup;
    }
  },
  methods: {
    getHandlingPrice,
    getInsurancePrice,
    getServicePrice,
    getTierPrice,
    getSubTotalItems,
    getHandlings
  }
};
</script>
