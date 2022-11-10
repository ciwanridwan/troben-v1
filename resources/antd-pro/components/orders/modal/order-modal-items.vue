<template>
  <div>
    <a-radio-group
      v-model="selectCalculatePrice"
      default-value="kg"
      style="width: 811px; margin-left: 25px"
      @change="localStorage"
    >
      <a-row type="flex" :gutter="[12, 12]">
        <a-col :span="12">
          <a-radio
            v-if="
              !isMotorBike &&
              isReguler &&
              (getStatus == 'estimated' || getStatus == 'revamp')
            "
            class="radio"
            value="kg"
            >Perhitungan Menggunakan Kilogram ( KG )</a-radio
          >
        </a-col>
        <a-col :span="12">
          <a-radio
            v-if="
              !isMotorBike &&
              isReguler &&
              (getStatus == 'estimated' || getStatus == 'revamp')
            "
            class="radio"
            value="cubic"
            >Perhitungan Menggunakan Kubikasi</a-radio
          >
        </a-col>
      </a-row>
    </a-radio-group>
    <a-row type="flex" :gutter="[12, 12]">
      <a-col v-for="(item, index) in value" :key="index" :span="24">
        <order-item-card
          v-model="value[index]"
          @change="onChange"
          :modifiable="modifiable"
          :editable="editable"
          :deletable="deletable"
          :package="package"
          :estPrice="package.estimation_prices[index]"
          :selectCalculate="selectCalculatePrice"
        />
      </a-col>
    </a-row>
  </div>
</template>
<script>
import OrderItemCard from "../order-item-card.vue";
import orderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  data() {
    return {
      selectCalculatePrice: "kg",
    };
  },
  props: {
    value: {
      type: Array,
      default: () => {
        return [];
      },
    },
    modifiable: {
      type: Boolean,
      default: true,
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
      default: () => {
        return {};
      },
    },
  },
  methods: {
    onChange() {
      this.$emit("change");
    },
    localStorage() {
      this.$emit("select", this.selectCalculatePrice);
      localStorage.setItem("calculateType", this.selectCalculatePrice);
    },
    totalAmount() {
      if (this.package?.canceled) {
        return this.package?.canceled?.pickup_price;
      }
      if (selectCalculatePrice == "cubic") {
      }
      return this.package?.total_amount + this.bankCharge - this.discount;
    },
  },
  components: { orderModalRowLayout, OrderItemCard },
  computed: {
    isMotorBike() {
      return this.package?.moto_bikes;
    },
    getStatus() {
      return this.package?.status;
    },
    isReguler() {
      return this.package?.service_code == "tps";
    },
  },
  mounted() {
    localStorage.setItem("calculateType", this.selectCalculatePrice);
  },
};
</script>
<style scoped>
.radio {
  padding: 6px;
  background: rgba(61, 136, 36, 0.15);
  border: 1px solid #3d8824;
  border-radius: 2px;
  width: -webkit-fill-available;
  height: 36px;
  box-sizing: border-box;
}
</style>
