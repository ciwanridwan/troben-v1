<template>
  <a-card>
    <a-form-model
      ref="formRules"
      :model="form"
      :rules="rules"
      :hideRequiredMark="true"
      layout="vertical"
    >
      <a-space direction="vertical" :style="{ width: '100%' }">
        <div>
          <h3 class="trawl-text-bolder">
            Penerima
          </h3>
          <a-row type="flex">
            <a-col :span="6">
              <a-form-model-item label="Nama Penerima" prop="receiver_name">
                <a-input
                  size="large"
                  v-model="form.receiver_name"
                  placeholder="Nama Penerima"
                ></a-input>
              </a-form-model-item>
            </a-col>
          </a-row>
        </div>

        <div>
          <h3 class="trawl-text-bolder">
            Lokasi Penerima
          </h3>
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="6">
              <a-form-model-item
                label="Provinsi"
                prop="destination_province_id"
              >
                <a-select
                  show-search
                  v-model="form.destination_province_id"
                  size="large"
                  placeholder="- pilih provinsi -"
                  :filter-option="filterOptionMethod"
                >
                  <a-select-option
                    v-for="index in 12"
                    :key="index"
                    :value="index"
                  >
                    Jack
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item
                label="Kota / Kabupaten"
                prop="destination_regency_id"
              >
                <a-select
                  show-search
                  v-model="form.destination_regency_id"
                  size="large"
                  placeholder="- pilih Kota / Kabupaten -"
                  :filter-option="filterOptionMethod"
                >
                  <a-select-option
                    v-for="index in 12"
                    :key="index"
                    :value="index"
                  >
                    Jack
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item
                label="Kecamatan"
                prop="destination_district_id"
              >
                <a-select
                  show-search
                  v-model="form.destination_district_id"
                  size="large"
                  placeholder="- pilih Kecamatan -"
                  :filter-option="filterOptionMethod"
                >
                  <a-select-option
                    v-for="index in 12"
                    :key="index"
                    :value="index"
                  >
                    Jack
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
          </a-row>
          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="6">
              <a-form-model-item
                label="Kelurahan"
                prop="destination_sub_district_id"
              >
                <a-select
                  show-search
                  v-model="form.destination_sub_district_id"
                  size="large"
                  placeholder="- pilih Kelurahan -"
                  :filter-option="filterOptionMethod"
                >
                  <a-select-option
                    v-for="index in 12"
                    :key="index"
                    :value="index"
                  >
                    Jack
                  </a-select-option>
                </a-select>
              </a-form-model-item>
            </a-col>
            <a-col :span="6">
              <a-form-model-item label="Kelurahan" prop="destination_zip_code">
                <a-input
                  size="large"
                  v-model="form.destination_zip_code"
                  disabled
                >
                </a-input>
              </a-form-model-item>
            </a-col>
          </a-row>

          <a-row type="flex" :gutter="[12, 12]">
            <a-col :span="12">
              <a-form-model-item
                label="Alamat Lengkap"
                prop="destination_address"
              >
                <a-textarea
                  rows="5"
                  type="textarea"
                  size="large"
                  v-model="form.destination_address"
                >
                </a-textarea>
              </a-form-model-item>
            </a-col>
          </a-row>
        </div>
        <div>
          <h3 class="trawl-text-bolder">
            Metode Pengiriman
          </h3>
          <a-form-model-item>
            <a-radio-group v-model="form.service_type">
              <trawl-radio-button
                v-for="(service, index) in services"
                :key="index"
                :value="'test'"
              >
                <template slot="icon">
                  <a-icon :component="service.icon"></a-icon>
                </template>
                {{ service.title }}
              </trawl-radio-button>
            </a-radio-group>
          </a-form-model-item>
        </div>
      </a-space>
    </a-form-model>
  </a-card>
</template>
<script>
import trawlRadioButton from "../../../trawl-radio-button.vue";
import { services } from "../../../../data/services";
export default {
  components: { trawlRadioButton },
  data() {
    return {
      services,
      form: {
        receiver_name: null,
        destination_province_id: null,
        destination_regency_id: null,
        destination_district_id: null,
        destination_sub_district_id: null,
        destination_zip_code: null,
        destination_address: null,
        service_type: null
      },
      rules: {
        receiver_name: [{ required: true }],
        destination_province_id: [{ required: true }],
        destination_regency_id: [{ required: true }],
        destination_district_id: [{ required: true }],
        destination_sub_district_id: [{ required: true }],
        destination_zip_code: [{ required: true }],
        destination_address: [{ required: true }],
        service_type: null
      }
    };
  },
  mounted() {
    console.log(this.services);
  }
};
</script>
