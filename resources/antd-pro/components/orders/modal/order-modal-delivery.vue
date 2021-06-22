<template>
  <order-modal-row-layout :afterLine="false">
    <template slot="icon"> &nbsp; </template>
    <template slot="content">
      <a-row type="flex">
        <a-col :span="12">
          <h3>Armada Penjemputan</h3>
          <a-space>
            <a-icon :component="CarIcon" :style="{ 'font-size': '2rem' }" />
            <span class="trawl-text-bolder">{{ transporter_type }}</span>
          </a-space>
        </a-col>
        <a-col :span="12">
          <a-row type="flex">
            <a-col :span="12">
              <span>Biaya Penjemputan</span>
            </a-col>
            <a-col :span="12"> Rp. {{ currency(0) }} </a-col>
          </a-row>
          <a-divider />
          <a-row type="flex">
            <a-col :span="12">
              <span class="trawl-text-bolder"> Total Charge Weight </span>
            </a-col>
            <a-col :span="12">
              <span class="trawl-text-bolder"> {{ totalWeight }} Kg </span>
            </a-col>
            <a-col :span="12">
              <span class="trawl-text-bolder"> Total Biaya </span>
            </a-col>
            <a-col :span="12">
              <span class="trawl-text-bolder">
                {{ currency(totalAmount) }}
              </span>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </template>
  </order-modal-row-layout>
</template>

<script>
import { CarIcon } from "../../icons";
import orderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  data() {
    return {
      CarIcon,
    };
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  components: { orderModalRowLayout },
  computed: {
    transporter_type() {
      return this.package?.transporter_type;
    },
    totalAmount() {
      return this.package?.total_amount;
    },
    totalWeight() {
      return this.package?.total_weight;
    },
  },
};
</script>
