<template>
<div
    style="
      display: flex;
      padding-right: 33px;
      align-items: center;
      justify-content: space-between;
    "
  >
    <a-space align="center">
      <delivery-icon></delivery-icon>
      <span
        ><b>{{ service }}</b>
        <br />

        {{ notes }}
      </span>
    </a-space>

    <span style="font-weight: bold"> {{ estimation_price }}/Kg </span>
  </div>
</template>
<script>
import { DeliveryIcon } from "../icons";
import { services } from "../../data/services";
export default {
  components: { DeliveryIcon },
  props: ["price", "package"],
  data() {
    return {
      services,
    };
  },
  computed: {
    service() {
      return (
        this.services.find(
          (service) => service.code === this.price.service.code
        ).title || ""
      );
    },
    notes() {
      return this.price?.notes;
    },
	estimation_price() {
      return this.package?.tier_price;
    },
  },
};
</script>
