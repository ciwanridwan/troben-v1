<template>
  <a-radio-group v-model="service_code">
    <trawl-radio-button
      v-for="(service, index) in listOfService"
      :key="index"
      :radioValue="service.code"
    >
      <template slot="icon">
        <a-icon :component="service.icon"></a-icon>
      </template>
      {{ service.title }}
    </trawl-radio-button>
  </a-radio-group>
</template>
<script>
import { services } from "../../data/services";
import trawlRadioButton from "./trawl-radio-button.vue";
export default {
  components: { trawlRadioButton },
  props: {
    availableServices: {
      type: Array,
      default: () => {},
    },
    value: null,
  },
  data() {
    return {
      services,
      service_code: null,
    };
  },
  computed: {
    listOfService() {
      let services = [];
      for (const service of this.availableServices) {
        services.push(service.code);
      }
      return this.services.filter((o) => services.indexOf(o.code) > -1);
    },
  },
  watch: {
    service_code: function (value) {
      this.$emit("input", value);
    },
  },
};
</script>
