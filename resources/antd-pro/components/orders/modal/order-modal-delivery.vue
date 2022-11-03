<template>
  <a-row type="flex" style="margin-left: 25px">
    <a-col :span="12">
      <h3 v-if="!isWalkin">Walk-in Order</h3>
      <h3 v-if="isWalkin">Armada Penjemputan</h3>
      <a-space v-if="isWalkin">
        <div>
          <a-icon class="icon" :component="PickupBox" />
        </div>
        <div class="box">
          <span class="trawl-text-bolder">{{ transporter_type }}</span
          ><br />
          <span>Dimensi Max • 200 x 130 x 120</span><br />
          <span>Berat Max • 700 kg</span>
        </div>
      </a-space>
    </a-col>
    <a-col :span="12">
      <a-row type="flex">
        <a-col
          v-if="getStatus == 'estimated' || getStatus == 'revamp'"
          :span="24"
        >
          <a-checkbox @change="onChange">
            Berikan Diskon penjemputan atau pengiriman
          </a-checkbox>
        </a-col>

        <!--discount sebelum dikirim ke customer -->
        <a-row style="margin: 10px 0">
          <a-col
            v-if="
              checkedDiscount &&
              (getStatus == 'estimated' || getStatus == 'revamp')
            "
          >
            <a-radio-group v-model="discountType" default-value="service">
              <a-radio value="service">Pengiriman</a-radio>
              <a-radio value="pickup">Penjemputan</a-radio>
            </a-radio-group>
          </a-col>
        </a-row>
        <a-col
          v-if="
            checkedDiscount &&
            (getStatus == 'estimated' || getStatus == 'revamp')
          "
          :span="12"
        >
          <a-input
            type="number"
            v-model="discount"
            @change="localStorage"
            prefix="Rp"
          />
        </a-col>
        <a-col v-if="getPaymentStatus != 'draft'" :span="16">
          <span> Biaya Admin</span>
        </a-col>
        <a-col v-if="getPaymentStatus != 'draft'" :span="8">
          <span> {{ currency(bankCharge) }} </span>
        </a-col>
        <!--discount sebelum dikirim ke customer -->
        <a-col
          v-if="getStatus != 'estimated' && getStatus != 'revamp'"
          :span="16"
        >
          <span>Diskon Pengiriman</span>
        </a-col>
        <a-col
          v-if="getStatus != 'estimated' && getStatus != 'revamp'"
          :span="8"
        >
          {{ currency(serviceDiscount) }}
        </a-col>
      </a-row>
      <a-divider />

      <a-row type="flex">
        <a-col :span="16">
          <span class="trawl-text-bolder"> Total Charge Weight </span>
        </a-col>
        <a-col :span="8">
          <span class="trawl-text-bolder">
            {{ isMotorBike ? "-" : `${totalWeight} Kg` }}
          </span>
        </a-col>
        <a-col :span="16">
          <span class="trawl-text-bolder"> Biaya Penjemputan </span>
        </a-col>
        <a-col :span="8">
          <span class="trawl-text-bolder">
            {{ currency(getPickupFee) }}
          </span>
        </a-col>
        <a-divider />
        <a-col :span="16">
          <span class="trawl-text-bolder"> Total Biaya </span>
        </a-col>
        <a-col :span="8">
          <span class="trawl-text-bolder">
            {{ currency(totalAmount) }}
          </span>
        </a-col>
      </a-row>
    </a-col>
  </a-row>
</template>

<script>
import { PickupBox } from "../../icons";
import orderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  data() {
    return {
      PickupBox,
      checkedDiscount: false,
      discount: 0,
      discountType: "service",
    };
  },
  props: {
    package: {
      type: Object,
      default: () => {},
    },
  },
  components: { orderModalRowLayout },
  computed: {
    transporter_type() {
      return this.package?.transporter_type;
    },
    totalAmount() {
      return this.package?.total_amount + this.bankCharge - this.discount;
    },
    packageStatus() {
      return this.package?.status;
    },
    totalWeight() {
      return this.package?.total_weight;
    },
    bankCharge() {
      return this.package?.payments[0]
        ? this.package?.payments[0].payment_admin_charges
        : 0;
    },
    isWalkin() {
      return this.package?.transporter_type;
    },
    getStatus() {
      return this.package?.status;
    },
    serviceDiscount() {
      return this.package?.discount_service_price;
    },
    getPaymentStatus() {
      return this.package?.payment_status;
    },
    isMotorBike() {
      return this.package?.moto_bikes;
    },
    getPickupFee() {
      var pickupPrice = this.package?.prices;
      if (!this.package?.transporter_type) {
        this.pickup = 0;
        return this.pickup;
      }
      pickupPrice.forEach((pickupFee) => {
        if (pickupFee.type === "delivery") {
          this.pickup = pickupFee.amount;
        }
      });
      return this.pickup;
    },
  },
  methods: {
    onChange() {
      this.checkedDiscount = !this.checkedDiscount;
    },
    localStorage() {
      localStorage.setItem("getDiscount", this.discount);
      localStorage.setItem("type", this.discountType);
    },
  },
};
</script>
<style>
.box {
  background: rgba(61, 136, 36, 0.15);
  border: 1px solid #efefef;
  border-radius: 10px;
  box-sizing: border-box;
  padding: 13px;
  width: 219px;
  height: 102px;
}
.icon {
  font-size: 6rem;
  border: 1px solid #efefef;
  border-radius: 10px;
  box-sizing: border-box;
}
</style>
