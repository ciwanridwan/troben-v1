<template>
  <div>
    <a-button @click="visible = true">{{ title }}</a-button>
    <a-modal
      v-model="visible"
      @ok="handleOk"
      @cancel="handleCancel"
      maskClosable
      closable
    >
      <template slot="title">
        {{ title }}
      </template>
      <template slot="footer">
        <a-button key="back" type="danger" ghost @click="handleCancel">
          Batal
        </a-button>
        <a-button key="submit" type="success" :loading="loading">
          Simpan
        </a-button>
      </template>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <span>Kota Asal</span>
          <a-select
            show-search
            :value="form.origin_regency"
            placeholder="Masukan kota asal"
            :default-active-first-option="false"
            :show-arrow="false"
            :filter-option="false"
            :not-found-content="null"
            @search="handleRegencySearch"
            @change="handleRegencyChange"
            style="width:100%"
          >
          </a-select>
        </a-col>
        <a-col :span="8">
          <span>Kota Tujuan</span>
          <a-select
            show-search
            :value="form.origin_regency"
            placeholder="Masukan kota asal"
            :default-active-first-option="false"
            :show-arrow="false"
            :filter-option="false"
            :not-found-content="null"
            @search="handleRegencySearch"
            @change="handleRegencyChange"
            style="width:100%"
          >
          </a-select>
        </a-col>
        <a-col :span="8">
          <span>Metode Pengiriman</span>
          <a-select
            show-search
            :value="form.origin_regency"
            placeholder="Masukan kota asal"
            :default-active-first-option="false"
            :show-arrow="false"
            :filter-option="false"
            :not-found-content="null"
            @search="handleRegencySearch"
            @change="handleRegencyChange"
            style="width:100%"
          >
          </a-select>
        </a-col>

        <a-col :span="8">
          <span>Harga</span>
          <a-input v-model="form.price"></a-input>
        </a-col>
        <a-col :span="8">
          <span>Keterangan</span>
          <a-input v-model="form.desc"></a-input>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
export default {
  data() {
    return {
      form: {
        origin_regency: null,
        origin_province: null,
        origin_district: null,
        price: 0,
        desc: null
      }
    };
  },
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: "Tambah Data Ongkir"
    }
  },

  computed: {
    pricingParent() {
      return this.getParent("master-pricing-district");
    }
  },

  methods: {
    handleChange(info) {
      const status = info.file.status;
      if (status !== "uploading") {
        console.log(info.file, info.fileList);
      }
      if (status === "done") {
        this.$message.success(`${info.file.name} file uploaded successfully.`);
      } else if (status === "error") {
        this.$message.error(`${info.file.name} file upload failed.`);
      }
    },
    getGeoItems() {
      this.$http.get(this.routeUri("api.geo")).then(resp => {
        console.log(resp);
      });
    },
    handleCancel() {
      this.visible = false;
    },
    handleOk() {
      this.visible = false;
    },
    handleRegencySearch(value) {
      console.log(value);
    },
    handleRegencyChange(value) {
      console.log(value);
    }
  },
  created() {
    console.log(this.pricingParent);
  }
};
</script>
