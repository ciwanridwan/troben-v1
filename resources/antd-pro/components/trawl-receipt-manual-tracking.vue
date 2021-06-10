<template>
  <trawl-modal-split :visibleProp="visible">
    <template slot="trigger">
      <span class="trawl-text-underline trawl-text-success trawl-icon-clickable"
        >Ubah posisi</span
      >
    </template>
    <template slot="title">
      Ubah detail posisi
    </template>
    <template slot="left">
      <a-layout class="trawl-bg-transparent" :style="{ height: '100%' }">
        <a-layout-content class="trawl-bg-transparent">
          <a-space direction="vertical" size="large">
            <span class="trawl-text-bold"
              >No Resi : {{ record.code.content }}</span
            >

            <order-modal-row-layout :afterLine="false" :iconPadding="false">
              <template slot="icon">
                <send-icon class="trawl-icon-full-width" />
              </template>
              <template slot="content">
                <a-space direction="vertical" :size="1">
                  <span>Pengirim</span>
                  <span class="trawl-text-bold">{{ record.sender_name }}</span>
                  <p class="trawl-text-normal">
                    {{ getOriginAddress(record) }}
                  </p>
                  <span>
                    Kode Pos : 12210
                  </span>
                </a-space>
              </template>
            </order-modal-row-layout>
            <order-modal-row-layout :afterLine="false" :iconPadding="false">
              <template slot="icon">
                <receive-icon class="trawl-icon-full-width" />
              </template>
              <template slot="content">
                <a-space direction="vertical" :size="1">
                  <span>Penerima</span>
                  <span class="trawl-text-bold">{{
                    record.receiver_address
                  }}</span>
                  <p class="trawl-text-normal">
                    {{ getDestinationAddress(record) }}
                  </p>
                  <span>
                    Kode Pos : 12210
                  </span>
                </a-space>
              </template>
            </order-modal-row-layout>
          </a-space>
        </a-layout-content>
        <a-layout-footer class="trawl-bg-transparent" :style="{ padding: 0 }">
          <order-summary-card :record="record" />
        </a-layout-footer>
      </a-layout>
    </template>
    <template slot="rightHeader">
      <order-manual-tracking-input ref="trackingInput" />
    </template>
    <template slot="rightContent">
      <a-timeline>
        <a-timeline-item
          v-for="(log, index) in record.code.logs"
          :key="index"
          >{{ log.description }}</a-timeline-item
        >
      </a-timeline>
    </template>
    <template slot="rightFooter">
      <a-button block class="trawl-button-success" @click="storeLog"
        >Simpan</a-button
      >
    </template>
  </trawl-modal-split>
</template>
<script>
import { CarIcon } from "./icons";

import TrawlModalSplit from "./trawl-modal-split.vue";
import { getOriginAddress, getDestinationAddress } from "../functions/orders";
import OrderManualTrackingInput from "./orders/order-manual-tracking-input.vue";

export default {
  props: ["record", "afterStore"],
  data() {
    return {
      trackings: [],
      visible: true
    };
  },
  methods: {
    getOriginAddress,
    getDestinationAddress,
    async storeLog() {
      this.$refs.trackingInput.$refs.formModel.validate().then(() => {
        console.log(this.$refs.trackingInput.form);
        this.$http
          .post(
            this.routeUri("admin.home.receipt.log.store", {
              package_hash: this.record.hash
            }),
            this.$refs.trackingInput.form
          )
          .then(() => {
            this.$notification.success({
              message: "Berhasil menambahkan tracking!"
            });
            this.afterStore();
            this.$refs.trackingInput.$refs.formModel.resetFields();
          })
          .catch(error => this.onErrorResponse(error));
      });
    }
  },
  components: {
    TrawlModalSplit,
    CarIcon,
    OrderManualTrackingInput
  }
};
</script>
