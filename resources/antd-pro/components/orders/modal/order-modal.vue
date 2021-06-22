<template>
  <div class="trawl-order-modal-component">
    <span
      v-if="hasSlot('trigger')"
      class="trawl-order-modal-component--trigger"
      @click="showModal"
    >
      <slot name="trigger"></slot>
    </span>
    <a-modal v-model="visible" width="65%" centered :footer="null">
      <template slot="closeIcon"
        ><a-icon type="close" @click="hideModal"></a-icon
      ></template>
      <template slot="title">
        <span v-if="!hasSlot('title')" class="trawl-order-modal-component--title">
          Detail ID Order
        </span>
        <span v-else class="trawl-order-modal-component--title">
          <slot name="title"></slot>
        </span>
      </template>

      <order-modal-address :package="record" />

      <a-space direction="vertical" size="middle">
        <order-modal-items @change="onItemChange" v-model="record.items" />

        <order-modal-estimations :package="record" />

        <order-modal-delivery :package="record" />
      </a-space>
    </a-modal>
  </div>
</template>
<script>
import orderModalRowLayout from "../order-modal-row-layout.vue";
import { TrawlRedIcon, SendIcon, ReceiveIcon, DeliveryIcon, CarIcon } from "../../icons";
import OrderEstimation from "../order-estimation.vue";
import OrderItemCard from "../order-item-card.vue";
import OrderDeliveryEstimation from "../order-delivery-estimation.vue";
import {
  getTotalWeightBorne,
  getSubTotalItems,
  getOriginAddress,
  getDestinationAddress,
} from "../../../functions/orders";
import OrderModalEstimations from "./order-modal-estimations.vue";
export default {
  components: {
    orderModalRowLayout,
    TrawlRedIcon,
    SendIcon,
    ReceiveIcon,
    DeliveryIcon,
    OrderEstimation,
    OrderDeliveryEstimation,
    OrderItemCard,
    CarIcon,
    OrderModalEstimations,
  },
  props: ["record"],
  data() {
    return {
      visible: false,
    };
  },
  methods: {
    getTotalWeightBorne,
    getSubTotalItems,
    getOriginAddress,
    getDestinationAddress,
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
    showModal() {
      this.visible = true;
    },
    hideModal() {
      this.visible = false;
    },
    onItemChange() {
      this.$emit("change");
    },
  },
};
</script>
