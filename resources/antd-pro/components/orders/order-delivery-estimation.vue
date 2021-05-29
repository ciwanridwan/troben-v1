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
                {{ record.origin_regency.name }}
                <a-icon type="arrow-right" />
                {{ record.destination_regency.name }}
              </b>
            </h4>
            <h5>Harga perkilo yaitu Rp. {{ tierPrice }}/kg</h5>
          </a-col>
        </a-row>
      </a-card>

      <a-row :style="'padding:24px 0px'">
        <a-col>
          <order-estimation></order-estimation>
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
        <a-row :key="index">
          <a-col :span="leftColumn">
            {{ item.name }}
          </a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ item.qty }} Koli
          </a-col>

          <a-col :span="leftColumn">
            Total Charge Weight
          </a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ item.weight_borne }} Kg
          </a-col>

          <a-col :span="leftColumn"> Biaya Jasa Kirim x {{ item.qty }} </a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ currency(getServicePrice(item)) }}
          </a-col>

          <a-col :span="24">
            <template v-for="(handling, index) in item.handling">
              <a-row :key="index" type="flex">
                <a-col :span="leftColumn">
                  Biaya Packing {{ handlings[handling.type] }} x
                  {{ item.qty }}
                </a-col>
                <a-col :span="rightColumn" class="trawl-text-right">
                  {{ currency(handling.price) }}
                </a-col>
              </a-row>
            </template>
          </a-col>

          <a-col :span="leftColumn"> Asuransi x {{ item.qty }} </a-col>
          <a-col :span="rightColumn" class="trawl-text-right">
            {{ currency(getInsurancePrice(item)) }}
          </a-col>
        </a-row>
        <a-divider :key="index + '-divider'"></a-divider>
      </template>

      <!-- sub total biaya -->
      <a-row type="flex">
        <a-col :span="leftColumn">
          Sub total biaya
        </a-col>
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
    record: {
      type: Object,
      default: () => {}
    },
    leftColumn: {
      type: Number,
      default: 16
    },
    rightColumn: {
      type: Number,
      default: 8
    }
  },
  data() {
    return {
      handlings
    };
  },
  computed: {
    items() {
      return this.record?.items;
    },
    tierPrice() {
      return this.getTierPrice(this.items);
    },
    subTotalPrice() {
      return this.getSubTotalItems(this.items);
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
