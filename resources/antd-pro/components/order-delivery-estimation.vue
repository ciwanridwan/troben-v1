<template>
  <div>
    <h3>Estimasi Biaya Pengiriman</h3>
    <a-card>
      <a-card>
        <a-row type="flex">
          <a-col :span="4">
            <information-icon></information-icon>
          </a-col>
          <a-col :span="18">
            <h4>
              <b>
                BOGOR
                <a-icon type="arrow-right" />
                YOGYAKARTA
              </b>
            </h4>
            <h5>
              Harga perkilo yaitu Rp. 2500/kg
            </h5>
          </a-col>
        </a-row>
      </a-card>

      <a-row :style="'padding:24px 0px'">
        <a-col>
          <order-estimation></order-estimation>
        </a-col>
      </a-row>

      <a-row type="flex">
        <a-col :span="leftColumn">
          <b>Deskripsi</b>
        </a-col>
        <a-col :span="rightColumn">
          <b>Jumlah</b>
        </a-col>
      </a-row>

      <!-- items -->
      <template v-for="index in 2">
        <a-row :key="index">
          <a-col :span="leftColumn">
            Buku
          </a-col>
          <a-col :span="rightColumn">
            1 Koli
          </a-col>

          <a-col :span="leftColumn">
            Total Charge Weight
          </a-col>
          <a-col :span="rightColumn">
            60 Kg
          </a-col>

          <a-col :span="leftColumn">
            Biaya Jasa Kirim
          </a-col>
          <a-col :span="rightColumn">
            {{ currency(50000) }}
          </a-col>

          <a-col :span="leftColumn">
            Asuransi
          </a-col>
          <a-col :span="rightColumn">
            {{ currency(2000) }}
          </a-col>
        </a-row>
        <a-divider :key="index"></a-divider>
      </template>

      <!-- sub total biaya -->
      <a-row type="flex">
        <a-col :span="leftColumn">
          Sub total biaya
        </a-col>
        <a-col :span="rightColumn">
          {{ currency(140000) }}
        </a-col>
      </a-row>
    </a-card>
  </div>
</template>
<script>
import informationIcon from "./icons/informationIcon.vue";
import deliveryIcon from "./icons/deliveryIcon";
import OrderEstimation from "./orders/order-estimation.vue";
export default {
  props: {
    items: {
      type: Array,
      default: []
    },
    leftColumn: {
      type: Number,
      default: 16
    },
    rightColumn: {
      type: Number,
      default: 8
    }
  },
  components: { informationIcon, deliveryIcon, OrderEstimation }
};
</script>
