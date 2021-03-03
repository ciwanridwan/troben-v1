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
        <h2>{{ title }}</h2>
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
          <trawl-input label="Kota Asal">
            <template slot="input">
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
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kota Tujuan">
            <template slot="input">
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
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Metode Pengiriman">
            <template slot="input">
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
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="16">
          <trawl-input label="Keterangan">
            <template slot="input">
              <a-textarea v-model="form.desc" :rows="5"></a-textarea>
            </template>
          </trawl-input>
        </a-col>
      </a-row>

      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <trawl-input label="Tarif 0-10 Kg">
            <template slot="input">
              <a-input v-model="form.tier1"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tarif 11-30 Kg">
            <template slot="input">
              <a-input v-model="form.tier2"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tarif 31-50 Kg">
            <template slot="input">
              <a-input v-model="form.tier3"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tarif 51-100 Kg">
            <template slot="input">
              <a-input v-model="form.tier4"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tarif 101-1.000 Kg">
            <template slot="input">
              <a-input v-model="form.tier5"></a-input>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Tarif > 1.000 Kg">
            <template slot="input">
              <a-input v-model="form.tier6"></a-input>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script>
import trawlInput from "../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  data() {
    return {
      geo: {
        origin: [],
        destination: []
      },
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
  mounted() {
    let parentData = { ...this.pricingParent.$data };
    console.log(parentData);
  }
};
</script>
