<template>
  <div>
    <h2>Info Owner</h2>
    <a-form-model ref="ruleForm" :model="form" :rules="rules">
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <trawl-input label="Nama Lengkap">
            <template slot="input">
              <a-form-model-item ref="name" prop="name">
                <a-input v-model="form.name"></a-input>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Nomor Hp">
            <template slot="input">
              <a-form-model-item ref="phone" prop="phone">
                <a-input v-model="form.phone"></a-input>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Email">
            <template slot="input">
              <a-form-model-item ref="email" prop="email">
                <a-input v-model="form.email"></a-input>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Username">
            <template slot="input">
              <a-form-model-item ref="username" prop="username">
                <a-input v-model="form.username"></a-input>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Kata Sandi">
            <template slot="input">
              <a-form-model-item ref="password" prop="password">
                <a-input-password v-model="form.password"></a-input-password>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="8">
          <trawl-input label="Ulangi Kata Sandi">
            <template slot="input">
              <a-form-model-item
                ref="password_confirmation"
                prop="password_confirmation"
              >
                <a-input-password
                  v-model="form.password_confirmation"
                ></a-input-password>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
        <a-col :span="16">
          <trawl-input label="Alamat Lengkap">
            <template slot="input">
              <a-form-model-item ref="address" prop="address">
                <a-textarea v-model="form.address" :rows="5"></a-textarea>
              </a-form-model-item>
            </template>
          </trawl-input>
        </a-col>
      </a-row>
      <a-row type="flex" :gutter="[10, 10]">
        <a-col :span="8">
          <h3>Foto Owner</h3>
          <a-button icon="plus" size="large" type="primary"></a-button>
        </a-col>
      </a-row>
    </a-form-model>
  </div>
</template>

<script>
import trawlInput from "../../../../components/trawl-input.vue";
export default {
  components: { trawlInput },

  data() {
    return {
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
  }
};
</script>
