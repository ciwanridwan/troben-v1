<template>
  <a-space align="center">
    <delivery-icon></delivery-icon>
    <span
      ><b>{{ service }}</b>
      <br />
      {{ notes }}
    </span>
  </a-space>
</template>
<script>
import { DeliveryIcon } from "../icons";
import { services } from "../../data/services";
export default {
  components: { DeliveryIcon },
  props: ["price"],
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
  },
};
</script>
