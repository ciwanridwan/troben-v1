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
        <span
          v-if="!hasSlot('title')"
          class="trawl-order-modal-component--title"
        >
          Detail ID Order
        </span>
        <span v-else class="trawl-order-modal-component--title">
          <slot name="title"></slot>
        </span>
      </template>

      <order-modal-row-layout>
        <template slot="icon">
          <trawl-red-icon :style="{ width: '3rem', height: '3rem' }" />
        </template>
        <template slot="content">
          <a-space direction="vertical">
            <span class="trawl-text-bolder"> {{ record.code.content }} </span>
            <span>{{ record.created_at }}</span>
          </a-space>
        </template>
      </order-modal-row-layout>

      <order-modal-row-layout>
        <template slot="icon">
          <send-icon />
        </template>
        <template slot="content">
          <a-space direction="vertical" :size="1">
            <span>Pengirim</span>
            <span class="trawl-text-bolder">{{ record.sender_name }}</span>
            <span>{{ record.sender_phone }}</span>
            <p>{{ record.sender_address }}</p>
          </a-space>
        </template>
      </order-modal-row-layout>

      <order-modal-row-layout :afterLine="false">
        <template slot="icon">
          <receive-icon />
        </template>
        <template slot="content">
          <a-space direction="vertical" :size="1">
            <span>Penerima</span>
            <span class="trawl-text-bolder">{{ record.receiver_name }}</span>
            <span>{{ record.receiver_phone }}</span>
            <p>{{ record.receiver_address }}</p>
            <order-estimation />
          </a-space>
        </template>
        <template slot="addon">
          <a-empty :description="null" />
        </template>
      </order-modal-row-layout>

      <a-space direction="vertical" size="middle">
        <order-modal-row-layout>
          <template slot="icon">
            &nbsp;
          </template>
          <template slot="content">
            <a-row type="flex" :gutter="[12, 12]">
              <a-col
                v-for="(item, index) in record.items"
                :key="item.hash + '-' + index"
                :span="12"
              >
                <order-item-card :record="record" :item="item" />
              </a-col>
            </a-row>
          </template>
        </order-modal-row-layout>

        <order-modal-row-layout>
          <template slot="icon">
            &nbsp;
          </template>
          <template slot="content">
            <a-row type="flex">
              <a-col :span="12">
                <order-delivery-estimation :record="record" />
              </a-col>
            </a-row>
          </template>
        </order-modal-row-layout>

        <order-modal-row-layout :afterLine="false">
          <template slot="icon">
            &nbsp;
          </template>
          <template slot="content">
            <a-row type="flex">
              <a-col :span="12">
                <h3>Armada Penjemputan</h3>
                <a-space>
                  <car-icon />
                  <span class="trawl-text-bolder">{{
                    record.transporter_type
                  }}</span>
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
                    <span class="trawl-text-bolder">
                      Total Charge Weight
                    </span>
                  </a-col>
                  <a-col :span="12">
                    <span class="trawl-text-bolder">
                      {{ getTotalWeightBorne(record.items) }} Kg
                    </span>
                  </a-col>
                  <a-col :span="12">
                    <span class="trawl-text-bolder">
                      Total Biaya
                    </span>
                  </a-col>
                  <a-col :span="12">
                    <span class="trawl-text-bolder">
                      Rp. {{ currency(getSubTotalItems(record.items)) }}
                    </span>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </template>
        </order-modal-row-layout>
      </a-space>
    </a-modal>
  </div>
</template>
<script>
import orderModalRowLayout from "./order-modal-row-layout.vue";
import {
  TrawlRedIcon,
  SendIcon,
  ReceiveIcon,
  DeliveryIcon,
  CarIcon
} from "../icons";
import OrderEstimation from "./order-estimation.vue";
import OrderItemCard from "./order-item-card.vue";
import OrderDeliveryEstimation from "./order-delivery-estimation.vue";
import { getTotalWeightBorne, getSubTotalItems } from "../../functions/orders";
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
    CarIcon
  },
  props: ["record"],
  data() {
    return {
      visible: false
    };
  },
  methods: {
    getTotalWeightBorne,
    getSubTotalItems,
    hasSlot(slotName) {
      return !!this.$slots[slotName];
    },
    showModal() {
      this.visible = true;
    },
    hideModal() {
      this.visible = false;
    }
  }
};
</script>
