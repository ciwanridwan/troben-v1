<template>
  <div>
    <a-button @click="show" class="trawl-button-success">
      {{ triggerText }}
    </a-button>
    <a-modal v-model="visible" centered :width="840" @cancel="handleCancel">
      <template slot="closeIcon">
        <a-icon type="close" @click="handleCancel"></a-icon>
      </template>
      <span slot="title">
        <h3><b>Detail ID Order</b></h3>
      </span>

      <!-- Kode -->
      <a-row>
        <a-col :span="iconSize">
          <trawl-red-icon :class="['trawl-img-icon']"></trawl-red-icon>
        </a-col>
        <a-col :span="20">
          <h5>
            <b>{{ record.barcode }}</b>
          </h5>
          <span>{{ currentDate }}</span>
        </a-col>
      </a-row>

      <order-divider :iconSize="iconSize"></order-divider>

      <!-- Pengirim -->
      <a-row>
        <a-col :span="iconSize">
          <send-icon></send-icon>
        </a-col>
        <a-col :span="20">
          <address-component>
            <span slot="name">{{ record.sender_name }}</span>
            <span slot="phone">{{ record.sender_phone }}</span>
            <span slot="address">{{ record.sender_address }}</span>
          </address-component>
        </a-col>
      </a-row>

      <order-divider :iconSize="iconSize"></order-divider>

      <!-- Penerima -->
      <a-row>
        <a-col :span="iconSize">
          <receive-icon></receive-icon>
        </a-col>
        <a-col :span="20">
          <a-row>
            <a-col :span="16">
              <address-component :receiver="true">
                <span slot="name">{{ record.receiver_name }}</span>
                <span slot="phone">{{ record.receiver_phone }}</span>
                <span slot="address">{{ record.receiver_address }}</span>
              </address-component>
              <order-estimation v-if="this.record.charge_price_note.notes" :note="this.record.charge_price_note"></order-estimation>
            </a-col>
            <a-col :span="8">
              <a-empty description="" :class="['full-width']"></a-empty>
            </a-col>
          </a-row>
        </a-col>
      </a-row>

      <a-row type="flex">
        <a-col :span="iconSize"> </a-col>
        <a-col :span="24 - iconSize">
          <a-row :gutter="[10, 10]">
            <!-- card barang -->
            <a-col
              :span="12"
              v-for="(item, index) in record.items"
              :key="index"
            >
              <order-item-card
                :item="item"
                :record="record"
                :deleteOrderItem="deleteOrderItem"
              ></order-item-card>
            </a-col>
          </a-row>
        </a-col>
      </a-row>

      <order-divider :iconSize="iconSize"></order-divider>
      <!--
      <a-row type="flex">
        <a-col :span="iconSize"> </a-col>
        <a-col :span="24 - iconSize">
          <a-row type="flex" :gutter="[10, 10]">
            <a-col :span="12" v-for="index in 2" :key="index">
              <order-delivery-estimation></order-delivery-estimation>
            </a-col>
          </a-row>
        </a-col>
      </a-row>

      <order-divider :iconSize="iconSize"></order-divider> -->

      <a-row type="flex">
        <a-col :span="iconSize"></a-col>
        <a-col :span="24 - iconSize">
          <a-row type="flex">
            <a-col :span="12">
              <h3>Armada Penjemputan</h3>
              <h4>
                <a-space>
                  <car-icon />
                  <span>{{ record.transporter_type }}</span>
                </a-space>
              </h4>
            </a-col>
            <a-col :span="12">
              <a-row type="flex">
                <a-col :span="16">
                  Biaya Penjemputan
                </a-col>
                <a-col :span="8">
                  {{ currency(0) }}
                </a-col>
              </a-row>
              <a-divider></a-divider>
              <a-row type="flex">
                <a-col :span="16">
                  <b>Total Charge Weight</b>
                </a-col>
                <a-col :span="8">
                  {{ currency(0) }}
                </a-col>
              </a-row>
              <a-row type="flex">
                <a-col :span="16">
                  <b>Total Biaya</b>
                </a-col>
                <a-col :span="8">
                  {{ currency(0) }}
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
      <template v-if="sendButton" slot="footer">
        <a-row type="flex" justify="end">
          <a-col :span="8">
            <a-button block class="trawl-button-success" @click="handleOk">
              Kirim ke Pelanggan
            </a-button>
          </a-col>
        </a-row>
      </template>
    </a-modal>
  </div>
</template>
<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import editButton from "../../../../components/button/edit-button.vue";
import {
  InformationIcon,
  CarIcon,
  TrawlRedIcon,
  SendIcon,
  ReceiveIcon
} from "../../../../components/icons";
import DeliveryIcon from "../../../../components/icons/deliveryIcon.vue";
import OrderDeliveryEstimation from "../../../../components/orders/order-delivery-estimation.vue";
import AddressComponent from "../../../../components/orders/address-component.vue";
import OrderEstimation from "../../../../components/orders/order-estimation.vue";
import OrderDivider from "./order-divider.vue";
import OrderItemCard from "./order-item-card.vue";

export default {
  components: {
    editButton,
    DeleteButton,
    OrderItemCard,
    OrderDivider,
    InformationIcon,
    OrderDeliveryEstimation,
    CarIcon,
    TrawlRedIcon,
    SendIcon,
    ReceiveIcon,
    AddressComponent,
    DeliveryIcon,
    OrderEstimation
  },
  props: {
    // triggerText: {
    //   type: String,
    //   default: "Cek"
    // },
    record: {
      type: Object,
      default: () => {}
    },
    sendButton: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      visible: false,
      iconSize: 2
    };
  },
  methods: {
    handleCancel() {
      this.visible = false;
    },
    handleOk() {
      this.sendToCustomer();
      this.visible = false;
    },
    async sendToCustomer() {
      let response = await this.$http.patch(
        this.routeUri("partner.cashier.home.packageChecked", {
          package_hash: this.record.hash
        })
      );
    },
    async deleteOrderItem(item_hash, record_hash) {
      await this.$http
        .delete(
          this.routeUri("partner.cashier.home.deletePackageItem", {
            item_hash: item_hash,
            package_hash: record_hash
          })
        )
        .then(resp => {
          let index = _.findIndex(this.record.items, { hash: item_hash });
          this.record.items.splice(index, 1);
        });
    },
    show() {
      this.visible = true;
    }
  },
  computed: {
    triggerText() {
      switch (this.record.status) {
        case "estimated":
          return "Cek";
        case "revamp":
          return "Lihat";
        default:
          return "Cek";
      }
    }
  }
};
</script>
