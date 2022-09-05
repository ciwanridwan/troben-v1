<template>
  <content-layout>
    <template slot="title">
      <div class="red-color">Account Driver</div>
    </template>
    <template slot="content">
      <a-table :class="['trawl']" :data-source="data" :columns="columns">
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

const data = [
  {
    id: "DRV864783496",
    name: "Bagas",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "gery",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "danang",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
  {
    id: "DRV864783496",
    name: "tegar",
    phone: "089766786868",
    address: "JL. rasamala 35",
  },
];

export default {
  name: "account-driver",
  components: {
    ContentLayout,
  },
  data() {
    return {
      data,
      searchText: "",
      searchInput: null,
      searchedColumn: "",
      columns: [
        {
          title: "Id Driver",
          dataIndex: "id",
          key: "id",
        },
        {
          title: "Nama",
          dataIndex: "name",
          key: "name",
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
          dataIndex: "phone",
          key: "phone",
        },
        {
          title: "Alamat Domisili",
          dataIndex: "address",
          key: "address",
        },
      ],
    };
  },
  methods: {
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
  mounted() {
    console.log("tes", this.$laravel.jwt_token);
  },
};
</script>
<style scoped>
.highlight {
  background-color: rgb(255, 192, 105);
  padding: 0px;
}
</style>
