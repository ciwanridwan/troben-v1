<template>
  <div>
    <a-button @click="visible = true" class="trawl-button-success">
      {{ triggerText }}
    </a-button>
    <a-modal v-model="visible" centered :width="840">
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
          <h5>Pengirim</h5>
          <h5>
            <b>{{ record.sender_name }}</b>
          </h5>
          <p>
            jalan jalan jalan jalan jalan
          </p>
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
              <h5>Pengirim</h5>
              <h5>
                <b>{{ record.sender_name }}</b>
              </h5>
              <p>
                jalan jalan jalan jalan jalan
              </p>
              <p>
                <a-icon type="shopping-cart" />
                <span>Darat ( Estimasi pengiriman 1-3 hari)</span>
              </p>
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
            <a-col :span="12" v-for="index in 2" :key="index">
              <order-item-card></order-item-card>
            </a-col>
          </a-row>
        </a-col>
      </a-row>

      <order-divider :iconSize="iconSize"></order-divider>

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

      <order-divider :iconSize="iconSize"></order-divider>

      <a-row type="flex">
        <a-col :span="iconSize"></a-col>
        <a-col :span="24 - iconSize">
          <a-row type="flex">
            <a-col :span="12">
              <h3>Armada Penjemputan</h3>
              <h4>
                <a-space>
                  <car-icon />
                  <span>GrandMax / L300</span>
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
            <a-button block class="trawl-button-success">
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
import OrderDeliveryEstimation from "../../../../components/order-delivery-estimation.vue";
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
    ReceiveIcon
  },
  props: {
    triggerText: {
      type: String,
      default: "Cek"
    },
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
  }
};
</script>
