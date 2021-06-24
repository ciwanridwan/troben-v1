<template>
  <div>
    <component
      v-if="actionComponent"
      :is="actionComponent"
      v-bind="actionComponentProps"
      @submit="onChange"
    ></component>
  </div>
</template>
<script>
import { getComponentByStatusAndPaymentStatus } from "../../../functions/orders/cashier/orderActions";
export default {
  props: ["package", "role"],
  data() {
    return {
      actionComponent: null,
      actionComponentProps: {},
    };
  },
  computed: {
    status() {
      return this.package?.status;
    },
    payment_status() {
      return this.package?.payment_status;
    },
  },
  methods: {
    onChange() {
      this.$emit("change");
    },
    setActionComponent() {
      let action = getComponentByStatusAndPaymentStatus(this.status, this.payment_status);
      this.actionComponent = action?.component;
      let props = { ...action?.props, package: this.package };
      this.actionComponentProps = props;
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.setActionComponent();
    });
  },
};
</script>
