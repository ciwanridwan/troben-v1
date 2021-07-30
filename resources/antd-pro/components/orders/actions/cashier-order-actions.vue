<template>
  <div>
    <component
      v-for="(actionComponent, index) in actionComponents"
      :key="index"
      :is="actionComponent.component"
      v-bind="setComponentProps(actionComponent.props)"
      :modifiable="modifiable"
      @submit="onChange"
      @change="onChange"
    ></component>
  </div>
</template>
<script>
import { actions } from "../../../data/order/cashier/orderActions";
import { getComponentByStatusAndPaymentStatus } from "../../../functions/orders";
import {STATUS_ESTIMATED, STATUS_REVAMP} from "../../../data/packageStatus";
export default {
  props: ["package", "role"],
  data() {
    return {
      actions,
      actionComponents: null,
      modifiable: true,
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
    setModifiable(){
      let availableStatus = [
        STATUS_ESTIMATED,
        STATUS_REVAMP
      ]
      this.modifiable = availableStatus.includes(this.package.status);
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
    this.setModifiable();
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
