<template>
  <a-card :class="['borderless-header', 'normal-title']">
    <!-- <template slot="extra" v-if="!isMotorBike">
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
    </template> -->
    <template slot="title">
      <a-row>
        <a-col :span="12">
          <a-space>
            <h4>{{ name }}</h4>
            <order-modal-edit
              v-if="!isMotorBike && modifiable ? editable : false"
              ref="editForm"
              v-model="item"
              @submit="onEdit"
            ></order-modal-edit>
            <delete-button
              v-if="!isMotorBike && modifiable ? deletable : false"
              @click="onDelete"
            ></delete-button>
          </a-space>
        </a-col>
        <a-col :span="12"><h4>Rincian Biaya Pengiriman</h4></a-col>
      </a-row>
    </template>

    <!-- detail -->
    <a-row v-if="!isMotorBike">
      <a-col :span="12">
        <a-row>
          <a-col :span="9">Keterangan Barang</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ name }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Berat Barang</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ weight }} kg</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Dimensi Barang</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ length }} x {{ width }} x {{ height }} cm</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Berat Volume</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ weight_volume }} kg</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Jumlah Koli</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ qty }} Koli</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Jenis Packing</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ packing }}</b></a-col
          >
        </a-row>
      </a-col>
      <a-col :span="12">
        <a-row v-for="(item, index) in handlingPrice" :key="index">
          <a-col :span="12">Biaya Packing {{ item.type }}</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(item.price) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Total Packing</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(totalHandlingPrice) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Asuransi</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(insurancePrice) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Biaya Tambahan</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(extraCharge) }}</b></a-col
          >
        </a-row>

        <!-- <a-row>
          <a-col :span="12">Biaya Pengiriman</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(servicePrice) }}</b></a-col
          >
        </a-row> -->

        <a-divider />

        <a-row>
          <a-col :span="12">Sub Total Biaya</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(subTotalAmount) }}</b></a-col
          >
        </a-row>
      </a-col>
    </a-row>

    <a-row v-if="isMotorBike">
      <a-col :span="12">
        <a-row>
          <a-col :span="9">CC Motor</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ BikeCC }}</b></a-col
          >
        </a-row>

        <!-- merk motor -->
        <a-row>
          <a-col :span="9">Merk Motor</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ BikeMerk }}</b></a-col
          >
        </a-row>

        <!-- type motor -->
        <a-row>
          <a-col :span="9">Type Motor</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ BikeType }}</b></a-col
          >
        </a-row>

        <!-- tahun motor -->
        <a-row>
          <a-col :span="9">Keluaran Tahun</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ BikeYears }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="9">Jenis Pengiriman</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"><b>Reguler</b></a-col>
        </a-row>
      </a-col>
      <a-col :span="12">
        <a-row>
          <a-col :span="12">Biaya Packing Kayu</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(handlingPrice) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Asuransi</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(insurancePrice) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Biaya Kirim</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(servicePrice) }}</b></a-col
          >
        </a-row>

        <a-row>
          <a-col :span="12">Biaya Penjemputan</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(getPickupFee) }}</b></a-col
          >
        </a-row>
        <a-divider />

        <a-row>
          <a-col :span="12">Sub Total Biaya</a-col>
          <a-col :span="4">:</a-col>
          <a-col :span="8"
            ><b>{{ currency(subTotalAmount) }}</b></a-col
          >
        </a-row>
      </a-col>
    </a-row>

    <!-- summary -->
    <!-- <a-row style="margin-top: 24px" v-if="!isMotorBike"> -->
    <!-- total berat -->
    <!-- <a-col :span="16"> Total Charge Weight </a-col>
      <a-col :span="8">
        <b>{{ weight_borne_total }} Kg</b>
      </a-col> -->

    <!-- harga barang -->
    <!-- <a-col :span="16"> Harga Barang </a-col>
      <a-col :span="8">
        <b>{{ currency(price) }}</b>
      </a-col> -->

    <!-- asuransi -->
    <!--      <a-col :span="16"> Asuransi </a-col>-->
    <!--      <a-col :span="8">-->
    <!--        <b>{{currency(getInsurancePrice(prices))  }}</b>-->
    <!--      </a-col>-->
    <!-- </a-row> -->
    <br />
    <a-row>
      <a-col :span="12">
        <h4>Foto Barang</h4>
      </a-col>
    </a-row>
    <a-empty style="width: 100px" v-if="package.attachments[0] == null" />
    <div
      v-else
      style="
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 1rem;
      "
    >
      <enlargeable-image
        style="width: 50px !important"
        v-for="(data, index) in URIImage"
        :key="index"
        :src="data.uri"
      />
    </div>
  </a-card>
</template>
<script>
import orderModalEdit from "./order-modal-edit.vue";
import EnlargeableImage from "@diracleo/vue-enlargeable-image";
import { getInsurancePrice } from "../../functions/orders";
export default {
  components: { orderModalEdit, EnlargeableImage },
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
    estPrice: {
      type: Object,
      default: () => {},
    },
    selectCalculate: {
      type: String,
      default: "kg",
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
      let volume = this.length * this.width * this.height;
      if (this.selectCalculate == "kg" && this.package?.service_code == "tps") {
        return volume / 4000;
      }
      if (
        this.selectCalculate == "cubic" &&
        this.package?.service_code == "tps"
      ) {
        return volume / 1000000;
      }
      if (this.selectCalculate == "kg" && this.package?.service_code == "tpx") {
        return volume / 6000;
      }
      // return this.item?.weight_volume;
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
    getPickupFee() {
      var pickupPrice = this.package?.prices;
      pickupPrice.forEach((pickupFee) => {
        if (pickupFee.type === "delivery") {
          this.pickup = pickupFee.amount;
        }
      });
      return this.pickup;
    },
    handlingPrice() {
      // return this.selectCalculate == "kg"
      //   ? this.estPrice?.handling_fee
      //   : this.cubicHandlingPrice;
      let result = [];
      if (this.estPrice?.handling_fee) {
        result = this.estPrice?.handling_fee;
      } else {
        result = [
          {
            type: "",
            price: 0,
          },
        ];
      }
      return result;
    },
    totalHandlingPrice() {
      let total_price = 0;
      this.handlingPrice.forEach((el) => {
        total_price += el.price;
      });
      return total_price;
    },
    insurancePrice() {
      // return this.selectCalculate == "kg"
      //   ? this.estPrice?.insurance_fee.toString().split(".")[0]
      //   : this.cubicInsurancePrice;
      return this.estPrice?.insurance_fee.toString().split(".")[0];
    },
    servicePrice() {
      return this.selectCalculate == "kg"
        ? this.estPrice?.service_fee
        : this.cubicServicePrice;
    },
    extraCharge() {
      // let extraCharge = this.package?.prices;
      // extraCharge.forEach((el) => {
      //   if (el.description == "additional" && el.type == "service") {
      //     this.charge = el.amount;
      //   }
      // });
      // return this.charge;
      return this.estPrice?.additional_fee;
    },
    subTotalAmount() {
      // return this.selectCalculate == "kg"
      //   ? this.estPrice?.sub_total_amount.toString().split(".")[0]
      //   : this.cubicSubTotalAmount;
      return this.estPrice?.sub_total_amount.toString().split(".")[0];
    },
    cubicHandlingPrice() {
      return this.package?.estimation_cubic_prices?.handling_fee;
    },
    cubicInsurancePrice() {
      return this.package?.estimation_cubic_prices?.insurance_fee;
    },
    cubicServicePrice() {
      return this.package?.estimation_cubic_prices?.service_fee;
    },
    cubicSubTotalAmount() {
      return this.package?.estimation_cubic_prices?.sub_total_amount
        .toString()
        .split(".")[0];
    },
    URIImage() {
      if (this.package?.attachments[0] == null) {
        return null;
      } else {
        return this.package?.attachments;
      }
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
    // console.log("item", this.item);
    // console.log("package", this.package);
    // console.log("estPrice", this.estPrice);
    // console.log(">>", this.estPrice?.insurance_fee.toString().split(".")[0]);
  },
};
</script>
