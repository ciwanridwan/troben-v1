<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <template slot="extra">
      <a-space>
        <order-modal-edit
          ref="editForm"
          :item="item"
          :updateOrderItem="updateOrderItem"
        ></order-modal-edit>
        <delete-button
          @click="deleteOrderItem(item.hash, record.hash)"
        ></delete-button>
      </a-space>
    </template>
    <template slot="title">
      <h4>{{ item.name }}</h4>
      {{ item.length }} x {{ item.width }} x {{ item.height }} cm
    </template>

    <!-- detail -->
    <a-row>
      <!-- berat -->
      <a-col :span="16">
        Berat volume
      </a-col>
      <a-col :span="8">
        <b>{{ item.weight }} Kg</b>
      </a-col>

      <!-- perkiraan berat -->
      <a-col :span="16">
        Berat volume
      </a-col>
      <a-col :span="8">
        <b>{{ item.weight }} Kg</b>
      </a-col>

      <!-- estimasi berat -->
      <a-col :span="16">
        Charge Weight per barang
      </a-col>
      <a-col :span="8">
        <b>{{ item.weight }} Kg</b>
      </a-col>

      <!-- jumlah paket -->
      <a-col :span="16">
        Jumlah paket
      </a-col>
      <a-col :span="8">
        <b>{{ item.qty }} Koli</b>
      </a-col>
    </a-row>

    <!-- summary -->
    <a-row style="margin-top:24px">
      <!-- total berat -->
      <a-col :span="16">
        Total Charge Weight
      </a-col>
      <a-col :span="8">
        <b>{{ totalChargeWeight }} Kg</b>
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
  props: ["item", "record", "deleteOrderItem"],
  computed: {
    totalChargeWeight() {
      return this.item.weight * this.item.qty;
    }
  },
  methods: {
    async updateOrderItem() {
      let form = this.$refs.editForm.form;
      form.handling = form.packaging_type;
      const response = await this.$http.patch(
        this.routeUri("partner.cashier.home.updatePackageItem", {
          item_hash: this.item.hash,
          package_hash: this.record.hash
        }),
        form
      );
      const { data } = response.data;

      Object.keys(this.item).forEach(key => {
        this.item[key] = data[key];
      });
    }
  }
};
</script>
