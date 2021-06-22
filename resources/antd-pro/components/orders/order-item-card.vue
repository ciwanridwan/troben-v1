<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <template slot="extra">
      <a-space>
        <order-modal-edit
          ref="editForm"
          v-model="item"
          @submit="onEdit"
        ></order-modal-edit>
        <delete-button @click="onDelete"></delete-button>
      </a-space>
    </template>
    <template slot="title">
      <h4>{{ name }}</h4>
      {{ length }} x {{ width }} x {{ height }} cm
    </template>

    <!-- detail -->
    <a-row>
      <!-- berat -->
      <a-col :span="16"> Berat volume </a-col>
      <a-col :span="8">
        <b>{{ weight_volume }} Kg</b>
      </a-col>

      <!-- perkiraan berat -->
      <a-col :span="16"> Perkiraan Berat </a-col>
      <a-col :span="8">
        <b>{{ weight }} Kg</b>
      </a-col>

      <!-- estimasi berat -->
      <a-col :span="16"> Charge Weight per barang </a-col>
      <a-col :span="8">
        <b>{{ weight_borne }} Kg</b>
      </a-col>

      <!-- jumlah paket -->
      <a-col :span="16"> Jumlah paket </a-col>
      <a-col :span="8">
        <b>{{ qty }} Koli</b>
      </a-col>
    </a-row>

    <!-- summary -->
    <a-row style="margin-top: 24px">
      <!-- total berat -->
      <a-col :span="16"> Total Charge Weight </a-col>
      <a-col :span="8">
        <b>{{ weight_borne_total }} Kg</b>
      </a-col>

      <!-- harga barang -->
      <a-col :span="16"> Harga Barang </a-col>
      <a-col :span="8">
        <b>{{ currency(price) }}</b>
      </a-col>

      <!-- asuransi -->
      <a-col :span="16"> Asuransi </a-col>
      <a-col :span="8">
        <b>{{ currency(1000) }}</b>
      </a-col>
    </a-row>
  </a-card>
</template>
<script>
import orderModalEdit from "./order-modal-edit.vue";
export default {
  components: { orderModalEdit },
  props: {
    value: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      item: {},
    };
  },
  computed: {
    name() {
      return this.item?.name;
    },
    length() {
      return this.item?.length;
    },

    width() {
      return this.item?.width;
    },
    height() {
      return this.item?.height;
    },
    weight() {
      return this.item?.weight;
    },
    weight_volume() {
      return this.item?.weight_volume;
    },
    weight_borne() {
      return this.item?.weight_borne;
    },
    qty() {
      return this.item?.qty;
    },
    weight_borne_total() {
      return this.item?.weight_borne_total;
    },
    price() {
      return this.item?.price;
    },
  },
  methods: {
    onEdit(value) {
      this.$http
        .patch(
          this.routeUri("partner.cashier.home.updatePackageItem", {
            package_hash: value?.package?.hash,
            item_hash: value?.hash,
          }),
          value
        )
        .then(() => {
          this.$notification.success({
            message: "Berhasil mengubah item",
          });
        });
      this.$emit("change");
      this.$emit("input", this.item);
    },
    onDelete() {
      this.$http
        .delete(
          this.routeUri("partner.cashier.home.deletePackageItem", {
            package_hash: this.item?.package?.hash,
            item_hash: this.item?.hash,
          })
        )
        .then(() => {
          this.$notification.success({
            message: "Berhasil Menghapus item",
          });
        });
      this.$emit("change");
      this.$emit("input", this.item);
    },
  },
  watch: {
    value: {
      handler: function (value) {
        this.item = value;
      },
      deep: true,
    },
  },
  mounted() {
    this.item = this.value;
  },
};
</script>
