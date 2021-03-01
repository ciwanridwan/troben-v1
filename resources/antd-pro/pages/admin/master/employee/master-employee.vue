<template>
  <div>
    <content-layout :pagination="trawlbensPagination">
      <template slot="head-tools">
        <a-row type="flex" justify="end">
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
          :columns="employeeColumns"
          :data-source="items.data"
          :pagination="trawlbensPagination"
          @change="handleTableChanged"
          :loading="loading"
          :class="['trawl']"
        >
          <span slot="phone_email" slot-scope="record"
            >{{ record.phone }} / {{ record.email }}</span
          >
          <span slot="action" slot-scope="record">
            <a-space>
              <employee-form
                title="Ubah Data Karyawan"
                :employeeData="record"
                :roles="roles"
              ></employee-form>
              <delete-button @click="deleteConfirm(record)"></delete-button>
            </a-space>
          </span>
        </a-table>
      </template>
    </content-layout>
  </div>
</template>

<script>
import DeleteButton from "../../../../components/button/delete-button.vue";
import employeeColumns from "../../../../config/table/employee";
import ContentLayout from "../../../../layouts/content-layout.vue";
import EmployeeForm from "./employee-form.vue";

export default {
  components: {
    DeleteButton,
    ContentLayout,
    EmployeeForm
  },
  created() {
    this.items = this.getDefaultPagination();
    this.getItems();
  },
  data: () => ({
    recordNumber: 0,
    items: {},
    roles: [],
    filter: {
      q: null,
      page: 1,
      per_page: 15
    },
    loading: false,
    employeeColumns
  }),
  methods: {
    deleteConfirm(record) {
      this.$confirm({
        content:
          "Apakah kamu yaking ingin menghapus data karyawan " +
          record.name +
          " (" +
          record.partner.code +
          ")" +
          "?",
        okText: "Ya",
        cancelText: "Batal",
        onOk: () => {
          this.deleteItem(record);
        }
      });
    },
    onSuccessResponse(response) {
      this.items = response;
      this.roles = this.items.data_extra.roles;

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

<style scoped></style>
