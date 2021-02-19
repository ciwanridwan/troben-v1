<template>
  <trawl-table :columns="columns" :dataSource="customers"></trawl-table>
</template>
<script>
import trawlTable from "../../../../components/trawl-table.vue";
const columns = [
  {
    title: "No",
    key: ""
  },
  {
    title: "No HP",
    key: "phone"
  },
  {
    title: "Email",
    key: "email"
  },
  {
    title: "Jumlah Order",
    key: "orders.count"
  },
  {
    title: "Invoice",
    key: "orders.total_payment"
  },
  {
    title: "Action"
  }
];
export default {
  components: { trawlTable },
  data() {
    return {
      columns,
      customers: []
    };
  },
  created() {
    this.getCustomerData();
  },
  methods: {
    async getCustomerData() {
      let { data } = await this.$http.get(this.routeUri(this.getRoute()));
      this.customers.push(...data.data);
      console.log(this.customers);
    }
  }
};
</script>
