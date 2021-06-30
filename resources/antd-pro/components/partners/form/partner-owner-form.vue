<template>
  <div>
    <h2>Info Owner</h2>
    <a-form-model
      ref="ruleForm"
      :model="form"
      :rules="rules"
      layout="vertical"
      hideRequiredMark
    >
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <a-form-model-item ref="name" prop="name">
            <template slot="label">
              <h3>Nama Lengkap</h3>
            </template>
            <a-input v-model="form.name"></a-input>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="phone" prop="phone">
            <template slot="label">
              <h3>Nomor Hp</h3>
            </template>
            <a-input v-model="form.phone"></a-input>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="email" prop="email">
            <template slot="label">
              <h3>Email</h3>
            </template>
            <a-input v-model="form.email"></a-input>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="username" prop="username">
            <template slot="label">
              <h3>Username</h3>
            </template>
            <a-input v-model="form.username"></a-input>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item ref="password" prop="password">
            <template slot="label">
              <h3>Kata Sandi</h3>
            </template>
            <a-input-password v-model="form.password"></a-input-password>
          </a-form-model-item>
        </a-col>
        <a-col :span="8">
          <a-form-model-item
            ref="password_confirmation"
            prop="password_confirmation"
          >
            <template slot="label">
              <h3>Ulangi Kata Sandi</h3>
            </template>
            <a-input-password
              v-model="form.password_confirmation"
            ></a-input-password>
          </a-form-model-item>
        </a-col>
        <a-col :span="16">
          <a-form-model-item ref="address" prop="address">
            <template slot="label">
              <h3>Alamat Lengkap</h3>
            </template>
            <a-textarea v-model="form.address" :rows="5"></a-textarea>
          </a-form-model-item>
        </a-col>
      </a-row>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <a-form-model-item ref="photo" prop="photo">
            <template slot="label">
              <h3>Foto Owner</h3>
            </template>
            <trawl-upload-list :maxFile="1" v-model="form.photo" />
          </a-form-model-item>
        </a-col>
      </a-row>
    </a-form-model>
  </div>
</template>

<script>
import trawlInput from "../../trawl-input.vue";
import TrawlUploadList from "../../trawl-upload-list.vue";
export default {
  components: { trawlInput, TrawlUploadList },

  data() {
    return {
      headers: {
        Accept: "Application/json"
      },
      photoPreview: null,
      form: {
        name: null,
        phone: null,
        email: null,
        username: null,
        password: null,
        password_confirmation: null,
        address: null,
        photo: null
      },
      rules: {
        name: [{ required: true }],
        phone: [{ required: true }],
        email: [{ required: true }],
        username: [{ required: true }],
        password: [{ required: true }],
        password_confirmation: [{ required: true }]
      }
    };
  },
  watch: {
    form: {
      handler: function(value) {
        this.$emit("input", value);
      },
      deep: true
    }
  }
};
</script>

<style lang="scss">
#owner-photo-preview {
  width: 80px;
  height: 80px;
  border-radius: 7px;
}
</style>
