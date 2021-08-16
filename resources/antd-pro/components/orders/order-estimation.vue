<template>
  <a-space align="center">
    <delivery-icon></delivery-icon>
    <span><b>{{ service }}</b> ( {{ notes }} )</span>
  </a-space>
</template>
<script>
import { DeliveryIcon } from "../icons";
import { services } from "../../data/services";
export default {
  components: { DeliveryIcon },
  props: ['note'],
  data () {
    return {
      services,
    }
  },
  computed: {
    service() {
      return (this.services.find(service => service.code === this.note.service_code)).title || ''
    },
    notes() {
      return this.note.notes;
    }
  }
};
</script>
