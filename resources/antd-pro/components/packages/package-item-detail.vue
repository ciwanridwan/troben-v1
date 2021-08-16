<template>
  <a-space :direction="direction">
    <span :style="textStyle">
      Ket Barang: <span class="trawl-text-bolder">{{ desc }}</span>
    </span>

    <a-space>
      <a-icon :component="PackageIcon" :style="iconStyle" />
      <span :style="textStyle"> Berat :</span>
      <span class="trawl-text-bolder" :style="textStyle"> {{ weight_borne_total }} Kg</span>
    </a-space>

    <a-space>
      <a-icon :component="WeightMachineIcon" :style="iconStyle" />
      <span :style="textStyle"> Jumlah Koli :</span>
      <span class="trawl-text-bolder" :style="textStyle"> {{ qty }} Kg</span>
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
      iconStyle: { "font-size": ".55rem" },
      textStyle: { "font-size": ".55rem" },
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
