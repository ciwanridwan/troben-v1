<template>
  <a-table
    :columns="customerColumns"
    :data-source="items.data"
    :pagination="trawlbensPagination"
    @change="handleTableChanged"
    :loading="loading"
  >
    <span slot="number" slot-scope="record">1</span>
    <span slot="name" slot-scope="name">{{ name }}</span>
    <span slot="phone" slot-scope="record">{{ record.phone }}</span>
    <span slot="email" slot-scope="record">{{ record.email }}</span>
    <span slot="action" slot-scope="record">
      <a-space>
        <detail-button></detail-button>
        <edit-button></edit-button>
        <delete-button></delete-button>
      </a-space>
    </span>
  </a-table>
</template>

<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import DetailButton from "../../../../components/button/detail-button.vue";
import EditButton from "../../../../components/button/edit-button.vue";
import customerColumns from "../../../../config/table/customer";

export default {
  name: "customer-list",
  components: {
    DetailButton,
    DeleteButton,
    EditButton
  },
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
  data: () => ({
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    customerColumns
  }),
  methods: {
    getItems() {
      this.loading = true;
      this.$http
        .get(this.routeUri("admin.master.customer"), { params: this.filter })
        .then(res => this.onSuccessResponse(res.data))
        .catch(err => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    onSuccessResponse(response) {
      this.items = response;
    },
    onErrorResponse(error) {
      this.$notification.error({
        message: error.response.data.message
      });
    },
    handleTableChanged(pagination) {
      this.filter.page = pagination.current;
      this.filter.per_page = pagination.pageSize;

      this.getItems();
    }
  }
};
</script>

<style scoped></style>
