<template>
  <order-modal-row-layout>
    <template slot="icon"> &nbsp; </template>
    <template slot="content">
      <a-row type="flex">
        <a-col :span="12">
          <order-delivery-estimation :package="package" :price="price"/>
        </a-col>
      </a-row>
    </template>
  </order-modal-row-layout>
</template>
<script>
import orderDeliveryEstimation from "../order-delivery-estimation.vue";
import OrderModalRowLayout from "../order-modal-row-layout.vue";
export default {
  props: ["package","price"],
  components: { orderDeliveryEstimation, OrderModalRowLayout },
};
</script>
