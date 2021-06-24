<template>
  <a-space :direction="direction">
    <span>
      Ket Barang: <span class="trawl-text-bolder">{{ desc }}</span>
    </span>

    <a-space>
      <a-icon :component="PackageIcon" :style="iconStyle" />
      <span> Berat :</span>
      <span class="trawl-text-bolder"> {{ weight_borne_total }} Kg</span>
    </a-space>

    <a-space>
      <a-icon :component="WeightMachineIcon" :style="iconStyle" />
      <span> Jumlah Koli :</span>
      <span class="trawl-text-bolder"> {{ qty }} Kg</span>
    </a-space>
  </a-space>
</template>
<script>
import { PackageIcon, WeightMachineIcon } from "../icons";
export default {
  props: {
    item: {
      type: Object,
      default: () => {},
    },
    direction: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      PackageIcon,
      WeightMachineIcon,
      iconStyle: { "font-size": "1.5rem" },
    };
  },
  computed: {
    desc() {
      return this.item?.desc;
    },
    weight_borne_total() {
      return this.item?.weight_borne_total;
    },
    qty() {
      return this.item?.qty;
    },
  },
};
</script>
