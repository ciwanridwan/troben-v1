<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <template slot="extra">
      <a-space>
        <order-modal-edit
          ref="editForm"
          :item="item"
          :updateOrderItem="updateOrderItem"
        ></order-modal-edit>
        <delete-button @click="deleteOrderItem()"></delete-button>
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
        <b>{{ item.weight_volume }} Kg</b>
      </a-col>

      <!-- perkiraan berat -->
      <a-col :span="16">
        Perkiraan Berat
      </a-col>
      <a-col :span="8">
        <b>{{ item.weight }} Kg</b>
      </a-col>

      <!-- estimasi berat -->
      <a-col :span="16">
        Charge Weight per barang
      </a-col>
      <a-col :span="8">
        <b>{{ item.weight_borne }} Kg</b>
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
        <b>{{ item.weight_borne_total }} Kg</b>
      </a-col>

      <!-- harga barang -->
      <a-col :span="16">
        Harga Barang
      </a-col>
      <a-col :span="8">
        <b>{{ currency(item.price) }}</b>
      </a-col>

      <!-- asuransi -->
      <a-col :span="16">
        Asuransi
      </a-col>
      <a-col :span="8">
        <b>{{ currency(getInsurancePrice(item)) }}</b>
      </a-col>
    </a-row>
  </a-card>
</template>
<script>
import orderModalEdit from "./order-modal-edit.vue";
import { getInsurancePrice } from "../../functions/orders";
export default {
  components: { orderModalEdit },
  props: ["item_hash", "record"],
  computed: {
    item() {
      return this.record.items.find(o => o.hash == this.item_hash);
    }
  },
  methods: {
    getInsurancePrice,
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
      let item_index = this.record.items.findIndex(
        o => o.hash == this.item_hash
      );
      this.$set(this.record.items, item_index, { ...data });
    },
    async deleteOrderItem() {
      let form = this.$refs.editForm.form;
      form.handling = form.packaging_type;
      await this.$http
        .delete(
          this.routeUri("partner.cashier.home.deletePackageItem", {
            item_hash: this.item.hash,
            package_hash: this.record.hash
          })
        )
        .then(() => {
          let item_index = this.record.items.findIndex(
            o => o.hash == this.item_hash
          );
          this.record.items.splice(item_index, 1);
          this.$set(this.record.items, this.record.items);
        });
    }
  }
};
</script>
