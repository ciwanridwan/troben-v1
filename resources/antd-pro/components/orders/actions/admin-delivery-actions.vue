<template>
  <a-space>
    <component
      v-for="(actionComponent, index) in actionComponents"
      :key="index"
      :is="actionComponent.component"
      v-bind="setComponentProps(actionComponent.props)"
      @submit="onChange"
    ></component>
  </a-space>
</template>
<script>
import { actions } from "../../../data/order/admin/deliveryActions";
import { getComponentByTypeAndStatus } from "../../../functions/orders";
export default {
  props: ["delivery", "role"],
  data() {
    return {
      actions,
      actionComponents: null,
    };
  },
  computed: {
    status() {
      return this.delivery?.status;
    },
    type() {
      return this.delivery?.type;
    },
  },
  methods: {
    onChange() {
      this.$emit("change");
    },
    setComponentProps(componentProps) {
      let props = { ...componentProps, delivery: { ...this.delivery } };
      return props;
    },
    setActionComponent() {
      let action = getComponentByTypeAndStatus(this.actions, this.type, this.status);
      this.actionComponents = action?.components;
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.setActionComponent();
    });
  },
  watch: {
    delivery: {
      handler: function (value) {
        this.setActionComponent();
      },
      deep: true,
    },
  },
};
</script>
