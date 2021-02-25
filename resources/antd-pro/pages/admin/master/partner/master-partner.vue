<template>
  <div>
    <content-layout :pagination="trawlbensPagination">
      <template slot="head-tools">
        <a-row type="flex" justify="end" :gutter="10">
          <a-col>
            <a-button>Tambah Mitra</a-button>
          </a-col>
          <a-col>
            <a-input-search
              v-model="filter.q"
              @search="getItems"
            ></a-input-search>
          </a-col>
        </a-row>
      </template>
      <template slot="content">
        <!-- table -->
        <a-table
          :columns="partnerColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
        >
          <span slot="number" slot-scope="number">{{ number }}</span>
          <span slot="type" slot-scope="type">{{ type }}</span>
          <span slot="code" slot-scope="record">{{ record.code }}</span>
          <span slot="name" slot-scope="record">{{ record.name }}</span>
          <span slot="contact_phone" slot-scope="record">{{
            record.contact_phone
          }}</span>
          <span slot="contact_email" slot-scope="record">{{
            record.contact_email
          }}</span>
          <span slot="action" slot-scope="record">
            <a-space>
              <delete-button @click="deleteItem(record)"></delete-button>
            </a-space>
          </span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>

<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import partnerColumns from "../../../../config/table/partner";
import ContentLayout from "../../../../layouts/content-layout.vue";

export default {
  name: "customer-list",
  components: {
    DeleteButton,
    ContentLayout
  },
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    partnerColumns
  }),
  methods: {
    deleteItem(record) {
      this.loading = true;
      let uri = this.routeUri(this.getRoute());
      let { hash } = record;
      uri = uri + "/" + hash;
      this.$http
        .delete(uri)
        .then(this.getItems())
        .catch(err => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    getItems() {
      this.loading = true;
      this.$http
        .get(this.routeUri(this.getRoute()), { params: this.filter })
        .then(res => this.onSuccessResponse(res.data))
        .catch(err => this.onErrorResponse(err))
        .finally(() => (this.loading = false));
    },
    onSuccessResponse(response) {
      this.items = response;
      let numbering = this.items.from;
      this.items.data.forEach((o, k) => {
        o.number = numbering++;
      });
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
