<template>
  <a-layout-content>
    <a-table :data-source="orders.data"> </a-table>
  </a-layout-content>
</template>
<script>
import { orders } from "../../../mock";

export default {
  data: () => {
    return {
      orders
    };
  },
  created() {
    this.$http
      .get("geo")
      .then(resp => {
        console.log(resp);
      })
      .catch(e => {
        console.log(e.response);
      });
    // console.log(this.orders);
    // console.log(this.orders);
  }
};
</script>
