<template>
  <div>
    <h3 class="trawl-text-bolder">Ringkasan Order</h3>
    <a-timeline>
      <a-timeline-item>
        <a-space direction="vertical" :size="1" :style="{ width: '100%' }">
          <span class="trawl-text-muted"> Penjemputan Dari </span>
          <p class="trawl-text-bold trawl-text-normal">{{ sender.address }}</p>
          <span></span>
          <p class="trawl-text-normal">
            {{ sender.name }} | {{ sender.phone }}
          </p>
          <trawl-divider />
        </a-space>
      </a-timeline-item>
      <a-timeline-item>
        <a-icon slot="dot" :component="PinCircleIcon" />

        <a-space direction="vertical" :size="1" :style="{ width: '100%' }">
          <a-space>
            <span class="trawl-text-muted"> Pengiriman Ke </span>
            <a-icon
              :component="EditIcon"
              class="trawl-click"
              @click="receiverEdit"
            />
          </a-space>

          <p class="trawl-text-bold trawl-text-normal">
            {{ receiver.address }}
          </p>
          <span></span>
          <p class="trawl-text-normal">
            {{ receiver.name }} | {{ receiver.phone }}
          </p>
          <trawl-divider />
        </a-space>
      </a-timeline-item>
    </a-timeline>
    <a-space direction="vertical" :style="{ width: '100%' }">
      <a-row v-if="!bike" type="flex">
        <a-col :offset="1" :span="10">
          <a-space>
            <span class="trawl-text-bolder">Daftar Barang</span>
            <a-icon
              :component="EditIcon"
              class="trawl-click"
              @click="itemEdit"
            />
          </a-space>
          <a-skeleton v-if="loading" active />
          <a-row v-else type="flex" v-for="(item, index) in items" :key="index">
            <a-col :span="12">
              <span>{{ index + 1 }}. {{ item.desc }}</span>
            </a-col>
            <a-col :span="12">
              <span class="trawl-text-bold"
                >{{ item.weight_borne_total }} Kg</span
              >
            </a-col>
          </a-row>
        </a-col>
      </a-row>
      <h3 class="trawl-text-bolder">Total Tarif Pengiriman</h3>
      <span class="trawl-text-bolder"
        >{{
          tier
            ? `${currency(tier)} / Kg`
            : bike
            ? `${currency(bike)}`
            : `${notAvailable}`
        }}
      </span>
      <small class="trawl-text-muted"
        >Tidak termasuk packing, asuransi, dan PPN.</small
      >
      <small v-if="bike" class="trawl-text-muted">{{ note }}</small>
    </a-space>
  </div>
</template>
<script>
import { NoteIcon, PinCircleIcon, EditIcon } from "../../../icons";
import TrawlDivider from "../../../trawl-divider.vue";
import OrderModalRowLayout from "../../order-modal-row-layout.vue";
export default {
  components: { TrawlDivider, OrderModalRowLayout },
  props: {
    data: {
      type: Object,
      default: () => {},
    },
    itemEdit: {
      type: Function,
      default: () => {},
    },
    receiverEdit: {
      type: Function,
      default: () => {},
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      NoteIcon,
      PinCircleIcon,
      EditIcon,
      form: {
        sender_address: null,
        sender_name: null,
        receiver_name: null,
        receiver_address: null,
      },
      rules: {
        sender_address: [{ required: true }],
        sender_name: [{ required: true }],
        receiver_name: [{ required: true }],
        receiver_address: [{ required: true }],
      },
    };
  },

  computed: {
    sender() {
      return {
        name: this.data?.sender_name,
        address: this.data?.sender_address,
        phone: this.data?.sender_phone,
      };
    },
    receiver() {
      return {
        name: this.data?.receiver_name,
        address: this.data?.receiver_address,
        phone: this.data?.receiver_phone,
      };
    },
    items() {
      return this.data?.items;
    },
    tier() {
      return this.data?.result?.tier;
    },
    notAvailable() {
      return this.data?.message
        ? this.data?.message
        : "Data tidak tersedia harap hubungi CS";
    },
    bike() {
      return this.data?.total_amount;
    },
    note() {
      return this.data?.notes;
    },
  },
};
</script>
