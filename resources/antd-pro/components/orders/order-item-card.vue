<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <template slot="extra" v-if="!isMotorBike">
      <a-space>
        <order-modal-edit
          v-if="modifiable ? editable : false"
          ref="editForm"
          v-model="item"
          @submit="onEdit"
        ></order-modal-edit>
        <delete-button
          v-if="modifiable ? deletable : false"
          @click="onDelete"
        ></delete-button>
      </a-space>
    </template>
    <template slot="title">
      <h4>{{ name }}</h4>
      <div v-if="!isMotorBike">
        {{ length }} x {{ width }} x {{ height }} cm
      </div>
    </template>

    <!-- detail -->
    <a-row v-if="!isMotorBike">
      <!-- packing -->
      <a-col :span="16"> Detail Packing </a-col>
      <a-col :span="8">
        <b>{{ packing }}</b>
      </a-col>

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

    <a-row v-if="isMotorBike">
      <!-- cc motor -->
      <a-col :span="16"> CC Motor </a-col>
      <a-col :span="8">
        <b>{{ BikeCC }}</b>
      </a-col>

      <!-- merk motor -->
      <a-col :span="16"> Merk Motor </a-col>
      <a-col :span="8">
        <b>{{ BikeMerk }}</b>
      </a-col>

      <!-- type motor -->
      <a-col :span="16"> type </a-col>
      <a-col :span="8">
        <b>{{ BikeType }}</b>
      </a-col>

      <!-- tahun motor -->
      <a-col :span="16"> years </a-col>
      <a-col :span="8">
        <b>{{ BikeYears }}</b>
      </a-col>
    </a-row>

    <!-- summary -->
    <a-row style="margin-top: 24px" v-if="!isMotorBike">
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
      <!--      <a-col :span="16"> Asuransi </a-col>-->
      <!--      <a-col :span="8">-->
      <!--        <b>{{currency(getInsurancePrice(prices))  }}</b>-->
      <!--      </a-col>-->
    </a-row>
  </a-card>
</template>
<script>
import orderModalEdit from "./order-modal-edit.vue";
import { getInsurancePrice } from "../../functions/orders";
export default {
  components: { orderModalEdit },
  props: {
    value: {
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
    package: {
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
    prices() {
      return this.item?.prices;
    },
    packing() {
      let packing = "tanpa packing.";
      if (this.item?.handling) {
        let count = this.item?.handling.length;
        packing = "";
        this.item?.handling.forEach((h, i) => {
          packing += `${h.type === "wood" ? "kayu" : h.type}${
            count - 1 === i ? "." : ","
          } `;
        });
      }
      return packing;
    },
    isMotorBike() {
      return this.package?.moto_bikes;
    },
    BikeCC() {
      return this.package?.moto_bikes?.cc;
    },
    BikeMerk() {
      return this.package?.moto_bikes?.merk;
    },
    BikeType() {
      return this.package?.moto_bikes?.type;
    },
    BikeYears() {
      return this.package?.moto_bikes?.years;
    },
  },
  methods: {
    getInsurancePrice,
    onEdit(value) {
      if (!value.handling) {
        value.handling = [];
      }
      this.$http
        .patch(
          this.routeUri("partner.cashier.home.updatePackageItem", {
            package_hash: this.package?.hash,
            item_hash: value?.hash,
          }),
          value
        )
        .then(() => {
          this.$notification.success({
            message: "Berhasil mengubah item",
          });
          this.$emit("change");
          this.$emit("input", this.item);
        });
    },
    onDelete() {
      this.$http
        .delete(
          this.routeUri("partner.cashier.home.deletePackageItem", {
            package_hash: this.package?.hash,
            item_hash: this.item?.hash,
          })
        )
        .then(() => {
          this.$notification.success({
            message: "Berhasil Menghapus item",
          });
          this.$emit("change");
          this.$emit("input", this.item);
        });
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
