<template>
  <content-layout>
    <template slot="title">
      <div class="red-color">Mitra Space</div>
    </template>
    <template slot="content">
      <a-table
        :class="['trawl']"
        :loading="loading"
        :data-source="data"
        :columns="columns"
        rowKey="id"
      >
        <div
          slot="filterDropdown"
          slot-scope="{
            setSelectedKeys,
            selectedKeys,
            confirm,
            clearFilters,
            column,
          }"
          style="padding: 8px"
        >
          <a-input
            v-ant-ref="(c) => (searchInput = c)"
            :placeholder="`Search ${column.dataIndex}`"
            :value="selectedKeys[0]"
            style="width: 188px; margin-bottom: 8px; display: block"
            @change="
              (e) => setSelectedKeys(e.target.value ? [e.target.value] : [])
            "
            @pressEnter="
              () => handleSearch(selectedKeys, confirm, column.dataIndex)
            "
          />
          <a-button
            type="primary"
            icon="search"
            size="small"
            style="width: 90px; margin-right: 8px"
            @click="() => handleSearch(selectedKeys, confirm, column.dataIndex)"
          >
            Search
          </a-button>
          <a-button
            size="small"
            style="width: 90px"
            @click="() => handleReset(clearFilters)"
          >
            Reset
          </a-button>
        </div>
        <a-icon
          slot="filterIcon"
          slot-scope="filtered"
          type="search"
          :style="{ color: filtered ? '#108ee9' : undefined }"
        />
        <template slot="customRender" slot-scope="text, record, index, column">
          <span v-if="searchText && searchedColumn === column.dataIndex">
            <template
              v-for="(fragment, i) in text
                .toString()
                .split(new RegExp(`(?<=${searchText})|(?=${searchText})`, 'i'))"
            >
              <mark
                v-if="fragment.toLowerCase() === searchText.toLowerCase()"
                :key="i"
                class="highlight"
                >{{ fragment }}</mark
              >
              <template v-else>{{ fragment }}</template>
            </template>
          </span>
          <template v-else>
            {{ text }}
          </template>
        </template>
      </a-table>
    </template>
  </content-layout>
</template>

<script>
import ContentLayout from "../../../../layouts/content-layout.vue";
import axios from "axios";
import moment from "moment";

const data = [];

export default {
  name: "mitra-space",
  components: {
    ContentLayout,
  },
  data() {
    return {
      loading: false,
      data,
      searchText: "",
      searchInput: null,
      searchedColumn: "",
      columns: [
        {
          title: "Nama",
          dataIndex: "full_name",
          key: "full_name",
          scopedSlots: {
            filterDropdown: "filterDropdown",
            filterIcon: "filterIcon",
            customRender: "customRender",
          },
          onFilter: (value, record) =>
            record.name.toString().toLowerCase().includes(value.toLowerCase()),
          onFilterDropdownVisibleChange: (visible) => {
            if (visible) {
              setTimeout(() => {
                this.searchInput.focus();
              }, 0);
            }
          },
        },
        {
          title: "Nomor Telepon",
          dataIndex: "phone_number",
          key: "phone_number",
        },
        {
          title: "Email",
          dataIndex: "email",
          key: "email",
        },
        {
          title: "Alamat",
          dataIndex: "address",
          key: "address",
        },
        {
          title: "Kota",
          dataIndex: "city",
          key: "city",
        },
        {
          title: "Tanggal",
          dataIndex: "created_at",
          key: "created_at",
          customRender: (text, row, index) => {
            return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
          },
        },
      ],
    };
  },
  created() {
    this.getAccountList();
  },
  methods: {
    getAccountList() {
      this.loading = true;
      const options = {
        method: "GET",
        url: `${process.env.MIX_TB_APIV2_URL}/mitra`,
        params: { code: "ms" },
      };
      axios
        .request(options)
        .then((res) => {
          this.data = res.data.data.user;
          // console.log(res.data.data.user);
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
          this.loading = false;
        });
    },
    handleSearch(selectedKeys, confirm, dataIndex) {
      confirm();
      this.searchText = selectedKeys[0];
      this.searchedColumn = dataIndex;
    },

    handleReset(clearFilters) {
      clearFilters();
      this.searchText = "";
    },
  },
  mounted() {},
};
</script>
<style scoped>
.highlight {
  background-color: rgb(255, 192, 105);
  padding: 0px;
}
</style>
