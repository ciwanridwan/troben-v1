<script>
export default {
  data() {
    return {
      form: this.$form.createForm(this),
      loading: false,
    };
  },
  methods: {
    handleSubmit(e) {
      e.preventDefault();
      this.form.validateFields((err, formData) => {
        if (!err) {
	formData['device_name'] = 'web'
          this.loading = true;
          this.$http
            .post(this.routeUri("auth.login.store"), formData)
            .then((response) => this.handleSuccessResponse(response.data))
            .catch((error) => this.handleErrorResponse(error.response.data.data));
        }
      });
    },
    handleSuccessResponse(data) {
      this.$notification.success({
        message: "Authenticated",
        description: "You will be redirected in a few moment.",
      });
      setTimeout(() => (window.location.href = "/"), 3000);
    },
    handleErrorResponse(data) {
      this.loading = false;

      this.form.setFields(
        _.mapValues(data, function (item, key) {
          return {
            errors: item.map(function (val) {
              return {
                field: key,
                message: val,
              };
            }),
          };
        })
      );
      this.form.setFields({ password: { value: null } });

      this.$notification.error({
        message: "Failed to authenticate",
        description: "We are unable to authenticate your credentials.",
      });
    },
  },
};
</script>
<template>
  <basic-layout>
    <div class="top">
      <div class="header">
        <img alt="logo" class="logo" src="/assets/logo.png" />
      </div>
      <div class="desc">{{ config.app.description }}</div>
    </div>
    <div class="login">
      <a-form
        id="formLogin"
        class="user-layout-login"
        ref="formLogin"
        :form="form"
        @submit="handleSubmit"
      >
        <a-form-item>
          <a-input
            size="large"
            type="text"
            placeholder="username"
            v-decorator="['username', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="user" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item>
          <a-input
            size="large"
            type="password"
            autocomplete="false"
            placeholder="password"
            v-decorator="['password', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item>
          <a-checkbox v-decorator="['remember']">Remember Me</a-checkbox>
        </a-form-item>

        <a-form-item>
          <a-button
            size="large"
            type="primary"
            htmlType="submit"
            class="login-button"
            :loading="loading"
            :disabled="loading"
            >Login
          </a-button>
        </a-form-item>
      </a-form>
    </div>
  </basic-layout>
</template>
