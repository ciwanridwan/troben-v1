<template>
  <order-modal-row-layout :afterLine="false">
    <template slot="icon"> &nbsp; </template>
    <template slot="content">
      <a-row type="flex">
        <a-col :span="12">
          <h3 v-if="!isWalkin">Walk-in Order</h3>
          <h3 v-if="isWalkin">Armada Penjemputan</h3>
          <a-space v-if="isWalkin">
            <a-icon :component="CarIcon" :style="{ 'font-size': '2rem' }" />
            <span class="trawl-text-bolder">{{ transporter_type }}</span>
          </a-space>
        </a-col>
        <a-col :span="12">
          <a-row type="flex">
            <a-col v-if="getStatus == 'estimated' || getStatus == 'revamp'" :span="24">
              <a-checkbox @change="onChange"> Berikan Discount </a-checkbox>
            </a-col>

            <!--discount sebelum dikirim ke customer -->
            <a-col v-if="checkedDiscount && (getStatus == 'estimated' || getStatus == 'revamp')" :span="12">
              <span>Diskon Pengiriman</span>
            </a-col>
            <a-col v-if="checkedDiscount && (getStatus == 'estimated' || getStatus == 'revamp')" :span="12">
              <a-input type="number" v-model="discount" @change="localStorage" prefix="Rp" />
            </a-col>
            <a-col :span="12">
              <span> Biaya Penjemputan</span>
            </a-col>
            <a-col :span="12">
              <a-input type="number" v-model="pickupFee" @change="localStoragePickupFee" prefix="Rp" />
            </a-col>
            <a-col v-if="getPaymentStatus != 'draft'" :span="16">
              <span> Biaya Admin</span>
            </a-col>
            <a-col v-if="getPaymentStatus != 'draft'" :span="8">
              <span> {{ currency(bankCharge) }} </span>
            </a-col>
            <!--discount sebelum dikirim ke customer -->
            <a-col v-if="getStatus != 'estimated' && getStatus != 'revamp'" :span="16">
              <span>Diskon Pengiriman</span>
            </a-col>
            <a-col v-if="getStatus != 'estimated' && getStatus != 'revamp'" :span="8">
              {{ currency(serviceDiscount) }}
            </a-col>
          </a-row>
          <a-divider />
          <a-row type="flex">
            <a-col :span="16">
              <span class="trawl-text-bolder"> Total Charge Weight </span>
            </a-col>
            <a-col :span="8">
              <span class="trawl-text-bolder"> {{ totalWeight }} Kg </span>
            </a-col>
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
  </order-modal-row-layout>
</template>

<script>
import { CarIcon } from "../../icons";
import orderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  data() {
    return {
      CarIcon,
      checkedDiscount: false,
      discount: 0,
    };
  },
  props: {
    package: {
      type: Object,
      default: () => {}
    }
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
    }
  },
  methods: {
    onChange() {
      this.checkedDiscount = !this.checkedDiscount;
    },
    localStorage() {
      localStorage.setItem("getDiscount", this.discount);
    },
    localStoragePickupFee() {
      localStoragePickupFee.setItem("getPickupFee", this.pickupFee);
    }
  }
};
</script>
