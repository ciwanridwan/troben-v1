<template>
  <content-layout>
    <template slot="content">
      <order-receiver-form-step-1 />
    </template>
  </content-layout>
</template>
<script>
import OrderReceiverFormStep1 from "../../../../components/orders/walkin/order-receiver-forms/form-step-1.vue";
import contentLayout from "../../../../layouts/content-layout.vue";
export default {
  name: "customer-service-walkin",
  components: { contentLayout, OrderReceiverFormStep1 }
};
</script>
