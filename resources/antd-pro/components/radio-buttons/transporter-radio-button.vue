<template>
  <trawl-radio-button :radioValue="radioValue" :style="{ width: '100%' }" :align="'left'">
    <a-card :class="[checked ? 'transporter-radio-button--checked' : null]">
      <a-row type="flex" :gutter="12" align="middle">
        <a-col :span="4">
          <a-icon type="picture" :style="{ 'font-size': '2rem' }" />
        </a-col>
        <a-col :span="18">
          <a-space direction="vertical">
            <span class="trawl-text-bolder"> {{ driver_name }} </span>
            <span>{{ transporter_type }} - {{ transporter_registration_number }}</span>
          </a-space>
        </a-col>
        <a-col :span="2" class="trawl-text-center">
          <a-icon
            :component="checked ? RadioCheckedIcon : RadioUncheckedIcon"
            :style="{ 'font-size': '1.5rem' }"
          ></a-icon>
        </a-col>
      </a-row>
    </a-card>
  </trawl-radio-button>
</template>
<script>
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
import TrawlRadioButton from "./trawl-radio-button.vue";
import { RadioCheckedIcon, RadioUncheckedIcon } from "../icons";
export default {
  components: { orderModalRowLayout, TrawlRadioButton },
  props: {
    currentChecked: null,
    transporter: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      RadioCheckedIcon,
      RadioUncheckedIcon,
    };
  },
  computed: {
    radioValue() {
      return this.transporter?.driver?.pivot?.hash;
    },
    checked() {
      return this.currentChecked ? this.currentChecked == this.radioValue : null;
    },
    driver_name() {
      return this.transporter?.driver?.name;
    },
    transporter_type() {
      return this.transporter?.type;
    },
    transporter_registration_number() {
      return this.transporter?.registration_number;
    },
  },
};
</script>
