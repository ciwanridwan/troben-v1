<template>
  <div>
    <div v-if="hasSlotTrigger" @click="activateModal">
      <slot name="trigger"></slot>
    </div>
    <a-button v-else @click="activateModal">{{ title }}</a-button>

    <a-modal
      v-model="visible"
      @ok="handleOk"
      @cancel="handleCancel"
      okText="Simpan"
      cancelText="Batal"
      maskClosable
      closable
      centered
    >
      <template slot="title">
        <h2>{{ title }}</h2>
      </template>
      <a-form-model
        ref="formRule"
        :rules="rules"
        :model="form"
        layout="vertical"
        hideRequiredMark
      >
        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item ref="originRegency" prop="origin_regency_id">
              <template slot="label">
                <h4>Kota Asal</h4>
              </template>
              <a-select
                show-search
                option-filter-prop="children"
                :filter-option="filterOption"
                v-model="form.origin_regency_id"
                @change="assignOriginProvince"
              >
                <a-select-option
                  v-for="regency in data_extra.regencies"
                  :key="regency.id"
                  :value="regency.id"
                  >{{ regency.name }}</a-select-option
                >
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item
              ref="destinationRegency"
              prop="destination_regency_id"
            >
              <template slot="label">
                <h4>Kota Tujuan</h4>
              </template>
              <a-select
                show-search
                option-filter-prop="children"
                :filter-option="filterOption"
                v-model="form.destination_regency_id"
              >
                <a-select-option
                  v-for="regency in data_extra.regencies"
                  :key="regency.id"
                  :value="regency.id"
                  >{{ regency.name }}</a-select-option
                >
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col v-if="data" :span="8">
            <a-form-model-item
              ref="destinationSubDistrict"
              prop="destination_id"
            >
              <template slot="label">
                <h4>Kelurahan Tujuan</h4>
              </template>
              <a-select
                show-search
                option-filter-prop="children"
                :filter-option="filterOption"
                v-model="form.destination_id"
                @change="assignZipCode"
              >
                <a-select-option
                  v-for="sub_district in sub_districts"
                  :key="sub_district.id"
                  :value="sub_district.id"
                  >{{ sub_district.name }}</a-select-option
                >
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col v-else :span="8">
            <a-form-model-item
              ref="destinationDistrict"
              prop="destination_district_id"
            >
              <template slot="label">
                <h4>Kecamatan Tujuan</h4>
              </template>
              <a-select
                show-search
                option-filter-prop="children"
                :filter-option="filterOption"
                v-model="form.destination_district_id"
              >
                <a-select-option
                  v-for="district in districts"
                  :key="district.id"
                  :value="district.id"
                  >{{ district.name }}</a-select-option
                >
              </a-select>
            </a-form-model-item>
          </a-col>

          <a-col :span="8">
            <a-form-model-item ref="serviceCode" prop="service_code">
              <template slot="label">
                <h4>Metode Pengiriman</h4>
              </template>
              <a-select v-model="form.service_code">
                <a-select-option
                  v-for="service in data_extra.services"
                  :key="service.code"
                  :value="service.code"
                >
                  <a-tooltip>
                    <template slot="title">
                      {{ service.description }}
                    </template>
                    {{ service.name }}
                  </a-tooltip>
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="desc" prop="desc">
              <template slot="label">
                <h4>Keterangan</h4>
              </template>
              <a-textarea v-model="form.desc"></a-textarea>
            </a-form-model-item>
          </a-col>
        </a-row>

        <a-row type="flex" :gutter="[10, 10]">
          <a-col :span="8">
            <a-form-model-item ref="tier_1" prop="tier_1">
              <template slot="label">
                <h4>Tarif 0 - 10 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_1"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="tier_2" prop="tier_2">
              <template slot="label">
                <h4>Tarif 11 - 30 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_2"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="tier_3" prop="tier_3">
              <template slot="label">
                <h4>Tarif 31 - 50 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_3"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="tier_4" prop="tier_4">
              <template slot="label">
                <h4>Tarif 51 - 100 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_4"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="tier_5" prop="tier_5">
              <template slot="label">
                <h4>Tarif 101 - 1.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_5"></a-input-number>
            </a-form-model-item>
          </a-col>
          <a-col :span="8">
            <a-form-model-item ref="tier_6" prop="tier_6">
              <template slot="label">
                <h4>Tarif > 1.000 Kg</h4>
              </template>
              <a-input-number v-model="form.tier_6"></a-input-number>
            </a-form-model-item>
          </a-col>
        </a-row>
      </a-form-model>
    </a-modal>
  </div>
</template>
<script>
import trawlInput from "../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },
  data() {
    return {
      form: {
        origin_province_id: null,
        origin_regency_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_id: null,
        service_code: null,
        zip_code: null,
        desc: null,
        tier_1: null,
        tier_2: null,
        tier_3: null,
        tier_4: null,
        tier_5: null,
        tier_6: null
      },
      rules: {
        origin_regency_id: [{ required: true }]
      }
    };
  },
  props: {
    data: {},
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
    },
    data_extra: {
      type: Object
    },
    method: {
      type: String,
      default: "POST"
    }
  },

  computed: {
    hasSlotTrigger() {
      return !!this.$slots.trigger;
    },
    pricingParent() {
      return this.getParent("master-pricing-district");
    },
    districts() {
      return _.filter(this.data_extra.districts, {
        regency_id: this.form.destination_regency_id
      });
    },
    sub_districts() {
      return _.filter(this.data_extra.sub_districts, {
        regency_id: this.form.destination_regency_id
      });
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
    fillForm() {
      let destination = _.find(
        this.data_extra.sub_districts,
        this.data.destination
      );
      this.form = {
        origin_province_id: this.data.origin_province.id,
        origin_regency_id: this.data.origin_regency.id,
        destination_regency_id: destination ? destination.regency_id : null,
        destination_id: this.data.destination.id,
        service_code: this.data.service.code,
        ...this.data
      };
      if (this.data.origin_district) {
        this.form.origin_district_id = this.origin_district.id;
      }
      if (this.data.origin_sub_district) {
        this.form.origin_sub_district_id = this.origin_sub_district.id;
      }
    },
    reloadParent() {
      let parent = this.getParent("master-pricing-district");
      parent.getItems();
    },
    activateModal() {
      if (this.data) {
        this.fillForm();
      }
      this.visible = true;
    },
    handleCancel() {
      this.visible = false;
    },
    handleOk() {
      if (this.method === "POST") {
        this.storePricing();
      } else if (this.method === "PATCH") {
        this.updatePricing();
      }
    },
    updatePricing() {
      let url = this.routeUri(this.getRoute()) + "/" + this.data.hash;
      this.$http
        .patch(url, this.form)
        .then(resp => this.onSuccessUpdate(resp))
        .catch(err => this.onErrorValidation(err));
    },
    storePricing() {
      this.$http
        .post(this.routeUri(this.getRoute()), this.form)
        .then(resp => this.onSuccessStore(resp))
        .catch(err => this.onErrorValidation(err));
    },
    onSuccessStore(resp) {
      this.$notification.success({
        message: "Berhasil menambahkan data ongkir"
      });
      this.visible = false;
      this.reloadParent();
    },
    onSuccessUpdate(resp) {
      this.$notification.success({
        message: "Berhasil mengubah data ongkir"
      });

      this.visible = false;
      this.reloadParent();
    },
    assignOriginProvince() {
      let regency = _.find(this.data_extra.regencies, {
        id: this.form.origin_regency_id
      });
      this.form.origin_province_id = regency.province_id;
    },
    assignZipCode() {
      let sub_district = _.find(this.data_extra.sub_districts, {
        id: this.form.destination_id
      });
      this.form.zip_code = sub_district.zip_code;
    }
  }
};
</script>
