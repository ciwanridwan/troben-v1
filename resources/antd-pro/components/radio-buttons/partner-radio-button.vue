<template>
  <trawl-radio-button :radioValue="radioValue" :style="{ width: '100%' }" :align="'left'">
    <a-card :class="[checked ? 'trawl-card-radio-button--checked' : null]">
      <a-row type="flex" :gutter="12" align="middle">
        <a-col :span="20">
          <h3>{{ partner_code }}</h3>
        </a-col>
        <a-col :span="4">
          <a-icon
            :component="checked ? RadioCheckedIcon : RadioUncheckedIcon"
            :style="{ 'font-size': '1.5rem' }"
          ></a-icon>
        </a-col>
        <a-col :span="24">
          <a-space direction="vertical">
            <h3 class="trawl-text-bolder">{{ partner_name }}</h3>
            <a-space align="start">
              <a-icon :component="HomeIcon" :style="{ 'font-size': '1.5rem' }" />
              <p>
                {{ partner_address }}
              </p>
            </a-space>
          </a-space>
        </a-col>
      </a-row>
    </a-card>
  </trawl-radio-button>
</template>
<script>
import orderModalRowLayout from "../orders/order-modal-row-layout.vue";
import TrawlRadioButton from "./trawl-radio-button.vue";
import { RadioCheckedIcon, RadioUncheckedIcon, HomeIcon } from "../icons";
export default {
  components: { orderModalRowLayout, TrawlRadioButton },
  props: {
    currentChecked: null,
    partner: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      RadioCheckedIcon,
      RadioUncheckedIcon,
      HomeIcon,
    };
  },
  computed: {
    radioValue() {
      return this.partner?.hash;
    },
    checked() {
      return this.currentChecked ? this.currentChecked == this.radioValue : null;
    },
    partner_code() {
      return this.partner?.code;
    },
    partner_address() {
      return this.partner?.address;
    },
    partner_name() {
      return this.partner?.name;
    },
    partner_type() {
      return this.partner?.type;
    },
  },
};
</script>
