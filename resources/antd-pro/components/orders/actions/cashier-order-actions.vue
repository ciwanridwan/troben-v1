<template>
  <div>
    <component
      v-for="(actionComponent, index) in actionComponents"
      :key="index"
      :is="actionComponent.component"
      v-bind="setComponentProps(actionComponent.props)"
      @submit="onChange"
      @change="onChange"
    ></component>
  </div>
</template>
<script>
import { actions } from "../../../data/order/cashier/orderActions";
import { getComponentByStatusAndPaymentStatus } from "../../../functions/orders";
export default {
  props: ["package", "role"],
  data() {
    return {
      actions,
      actionComponents: null,
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
    setComponentProps(componentProps) {
      let props = { ...componentProps, package: { ...this.package } };
      return props;
    },
    setActionComponent() {
      let action = getComponentByStatusAndPaymentStatus(
        this.actions,
        this.status,
        this.payment_status
      );
      this.actionComponents = action?.components;
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.setActionComponent();
    });
  },
  watch: {
    package: {
      handler: function (value) {
        this.setActionComponent();
      },
      deep: true,
    },
  },
};
</script>
