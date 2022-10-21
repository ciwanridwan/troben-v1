<template>
  <div>
    <content-layout>
      <template slot="content">
        <!-- table -->
        <a-table
          :columns="driverColumns"
          :loading="loading"
          :data-source="filteredItems"
          :class="['trawl']"
          rowKey="id"
        >
          <span slot="number" slot-scope="number" class="fw-bold">{{
            number
          }}</span>
          <span slot="name" slot-scope="name, record" class="fw-bold">
            <a-space
              @click="showModal(record)"
              class="cursor-pointer uppercase"
            >
              {{ name }}
            </a-space>
          </span>
          <span slot="phone" slot-scope="phone">
            <u>{{ phone }}</u>
          </span>
          <span slot="created_at" slot-scope="created_at">
            <u>{{ created_at }}</u>
          </span>
          <span slot="address" slot-scope="address">
            <u>{{ address }}</u>
          </span>
          <span slot="status" slot-scope="record">
            <a-dropdown>
              <template #overlay>
                <a-menu>
                  <a-menu-item key="1" @click="changeStatus(record.id)">
                    <UserOutlined />
                    Non Active
                  </a-menu-item>
                </a-menu>
              </template>
              <a-button
                type="primary"
                class="trawl-outline-button-success"
                v-if="record.is_active"
              >
                Active
                <a-icon class="ml-1" type="down" />
              </a-button>
              <a-button
                type="primary"
                class="trawl-outline-button-danger disabled"
                v-else
              >
                Non Active
                <a-icon class="ml-1" type="down" />
              </a-button>
            </a-dropdown>
          </span>
        </a-table>

        <a-modal
          v-model="visible"
          :width="550"
          @cancel="onCancel"
          :closable="true"
          :mask-closable="true"
          footer=""
        >
          <template slot="closeIcon"
            ><a-icon type="close" @click="onCancel"></a-icon
          ></template>
          <template slot="title">
            <div class="red-color">Agen TrawlBens</div>
          </template>
          <a-row type="flex" :gutter="[24, 24]">
            <a-col :span="8">
              <img
                alt="detail-image"
                class="detail-image"
                :src="data_modal.avatar"
                v-if="data_modal.avatar"
              />
              <img
                alt="detail-image"
                class="detail-image"
                src="/assets/empty-image.png"
                v-else
              />
            </a-col>
            <a-col :span="16">
              <div class="gray-color size-12">Nama</div>
              <div class="fw-bold">
                {{ data_modal.name }}
              </div>
              <div class="line-gray"></div>
              <div class="gray-color size-12 mt-1">nomor telepon</div>
              <div class="fw-bold">
                {{ data_modal.phone }}
              </div>
              <div class="line-gray"></div>
              <div class="gray-color size-12 mt-1">Alamat</div>
              <div class="fw-bold">
                {{ data_modal.address }}
              </div>
              <div class="line-gray"></div>
              <div class="gray-color size-12 mt-1">Akun Bank</div>
              <div class="fw-bold">
                {{ data_modal.account_number }}
              </div>
            </a-col>
          </a-row>
        </a-modal>
      </template>
    </content-layout>
  </div>
</template>

<script>
import driverColumns from "../../../../config/table/driver-trawltruck";
import ContentLayout from "../../../../layouts/content-layout.vue";
import axios from "axios";

export default {
  name: "account-agent",
  components: {
    ContentLayout,
  },

  data() {
    return {
      loading: false,
      driverColumns,
      visible: false,
      lists: {
        data: [],
      },
      data_modal: {},
      check_status: null,
      is_active: false,
    };
  },
  created() {
    this.getDriverList();
  },
  computed: {
    filteredItems() {
      return this.lists.data.filter((item) => {
        if (this.check_status != null) {
          return item.is_active == this.check_status;
        } else {
          return true;
        }
      });
    },
  },
  methods: {
    onCancel() {
      this.visible = false;
    },
    getDriverList() {
      this.loading = true;
      axios
        .get(`https://ae.trawlbens.com/account/accountList`, {
          headers: {
            Authorization: `Bearer ${this.$laravel.jwt_token}`,
          },
        })
        .then((res) => {
          this.lists = res.data;
          let numbering = 1;
          this.lists.data.forEach((o, k) => {
            o.number = numbering++;
          });
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
          this.loading = false;
        });
    },
    showModal(key) {
      this.visible = true;
      this.data_modal = key;
    },
    changeStatus(id) {
      var data = { is_active: this.is_active };
      axios
        .patch(`https://ae.trawlbens.com/account/setAccountStatus`, data, {
          params: {
            id: id,
          },
          headers: {
            Authorization: `Bearer ${this.$laravel.jwt_token}`,
          },
        })
        .then((res) => {
          this.getDriverList();
          this.$message.success(`Change status success`);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>

<style lang="scss">
.trawl-outline-button-success {
  background-color: #fff;
  padding: 0 23px;
  height: 40px;
  border: 2px solid #3d8824;
  color: #fff;
  border-radius: 50px !important;
  &:hover {
    background-color: #fff;
    border: 2px solid #3d8824;
    color: #fff;
    border-radius: 50px !important;
  }
}
.trawl-outline-button-danger {
  background-color: #fff;
  padding: 0 23px;
  height: 40px;
  border: 2px solid #e60013;
  color: #fff;
  border-radius: 50px !important;
  &:hover {
    background-color: #fff;
    border: 2px solid #e60013;
    color: #fff;
    border-radius: 50px !important;
  }
}
.ml-1 {
  margin-left: 25px !important;
}
.ant-table-thead > tr > th .ant-table-column-sorter {
  display: unset !important;
}
.trawl .ant-table-thead tr th {
  text-align: unset !important;
}
.fw-bold {
  font-weight: 700;
}
.black-color {
  color: #000 !important;
}
.red-color {
  color: #e60013 !important;
}
.detail-image {
  width: 100%;
  border-radius: 10px;
}
.ant-modal-mask {
  opacity: 0.4;
}
.gray-color {
  color: gray !important;
}
.mt-1 {
  margin-top: 10px;
}
.size-12 {
  font-size: 12px;
}
.line-gray {
  width: 100%;
  height: 1px;
  background-color: #dfe6e9;
  margin-top: 10px;
}
.cursor-pointer {
  cursor: pointer;
}
.uppercase {
  text-transform: capitalize;
}
.ant-btn-primary:hover,
.ant-btn-primary:focus {
  background-color: #3d8824;
  border-color: #3d8824;
  color: #fff;
}
</style>
