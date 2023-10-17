<template>
  <div>
    <a-popconfirm @confirm="confirm" ok-text="Kunjungi Halaman Admin">
      <template #title>
        Untuk saat ini silahkan gunakan Admin versi Terbaru.
      </template>
      <a-button
        type="success"
        class="trawl-button-success"
        v-if="hasSlot('trigger')"
      >
        <span>Cek</span>
      </a-button>
      <template v-else>
        <a-icon :component="GpsIcon" />
        <span class="trawl-text-underline"> Invoice </span>
      </template>
    </a-popconfirm>
  </div>

  <!-- <div class="trawl-order-modal-component">
    <span class="trawl-order-modal-component--trigger" @click="showModal">
      <slot v-if="hasSlot('trigger')" name="trigger"></slot>
      <a-space v-else class="trawl-text-success-darken trawl-click">
        <a-icon :component="GpsIcon" />
        <span class="trawl-text-underline"> Invoice </span>
      </a-space>
    </span>
    <a-modal v-model="visible" width="65%" centered :footer="footer">
      <template slot="closeIcon">
        <a-icon type="close" @click="hideModal"></a-icon>
      </template>

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

      <order-modal-address :package="package" :price="price" />

      <order-modal-items
        @select="onSelectChange"
        @change="onItemChange"
        v-model="package.items"
        :modifiable="modifiable"
        :editable="editable"
        :deletable="deletable"
        :package="package"
      />

      <order-modal-delivery :package="package" :select="select" />

      <template v-if="hasSlot('footer')" slot="footer">
        <slot name="footer"></slot>
      </template>
    </a-modal>
  </div> -->
</template>
<script>
import orderModalRowLayout from "../order-modal-row-layout.vue";
import {
  TrawlRedIcon,
  SendIcon,
  ReceiveIcon,
  DeliveryIcon,
  CarIcon,
  GpsIcon,
} from "../../icons";
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
import OrderModalItems from "./order-modal-items.vue";
export default {
  components: {
    orderModalRowLayout,
    OrderEstimation,
    OrderDeliveryEstimation,
    OrderItemCard,
    OrderModalEstimations,
    OrderModalItems,
  },
  props: {
    value: null,
    package: {
      type: Object,
      default: () => {},
    },
    modifiable: {
      type: Boolean,
    },
    editable: {
      type: Boolean,
      default: true,
    },
    deletable: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      visible: false,
      GpsIcon,
      TrawlRedIcon,
      SendIcon,
      ReceiveIcon,
      DeliveryIcon,
      CarIcon,
      footer: undefined,
      price: null,
      select: "kg",
    };
  },
  methods: {
    confirm() {
      window.open("https://admin.trawlbens.com/", "_blank");
    },
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
    onSelectChange(data) {
      // console.log("data", data);
      this.select = data;
    },
    setFooter() {
      this.footer = !!this.$slots.footer ? undefined : null;
    },
    getSinglePrice(origin_regency_id, destination_id) {
      let uriName =
        this.roles().length === 0
          ? "admin.master.pricing.district.show"
          : "partner.cashier.home.price";
      this.$http
        .get(
          this.routeUri(uriName) +
            `?origin_regency_id=${origin_regency_id}&destination_id=${destination_id}`
        )
        .then((res) => this.onSuccessResponse(res.data))
        .catch((error) => {
          console.log(error);
        });
    },
    onSuccessResponse(res) {
      this.price = res.data;
    },
  },
  watch: {
    visible: function (value) {
      this.$emit("input", value);
    },
    value: function (value) {
      this.visible = value;
      this.$emit("input", value);
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.getSinglePrice(
        this.package.origin_regency.id,
        this.package.destination_sub_district.id
      );
      this.setFooter();
    });
  },
};
</script>
