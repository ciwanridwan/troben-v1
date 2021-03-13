<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <template slot="extra">
      <a-space>
        <order-modal-edit></order-modal-edit>
        <delete-button></delete-button>
      </a-space>
    </template>
    <template slot="title">
      <h4>Buku</h4>
      30 x 30 x 30 cm
    </template>

    <!-- detail -->
    <a-row>
      <!-- berat -->
      <a-col :span="16">
        Berat volume
      </a-col>
      <a-col :span="8">
        <b>2 Kg</b>
      </a-col>

      <!-- perkiraan berat -->
      <a-col :span="16">
        Berat volume
      </a-col>
      <a-col :span="8">
        <b>20 Kg</b>
      </a-col>

      <!-- estimasi berat -->
      <a-col :span="16">
        Charge Weight per barang
      </a-col>
      <a-col :span="8">
        <b>20 Kg</b>
      </a-col>

      <!-- jumlah paket -->
      <a-col :span="16">
        Jumlah paket
      </a-col>
      <a-col :span="8">
        <b>20 Koli</b>
      </a-col>
    </a-row>

    <!-- summary -->
    <a-row style="margin-top:24px">
      <!-- total berat -->
      <a-col :span="16">
        Total Charge Weight
      </a-col>
      <a-col :span="8">
        <b>60 Kg</b>
      </a-col>

      <!-- harga barang -->
      <a-col :span="16">
        Harga Barang
      </a-col>
      <a-col :span="8">
        <b>{{ currency(1000000) }}</b>
      </a-col>

      <!-- asuransi -->
      <a-col :span="16">
        Asuransi
      </a-col>
      <a-col :span="8">
        <b>{{ currency(2000) }}</b>
      </a-col>
    </a-row>
  </a-card>
</template>
<script>
import orderModalEdit from "./order-modal-edit.vue";
export default {
  components: { orderModalEdit },
  props: ["item"]
};
</script>
