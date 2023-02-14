<script>
export default {
  data() {
    return {
      form: this.$form.createForm(this),
      loading: false,
      found: false,
      foundInput: '',
    };
  },
  methods: {
    handleSubmit(e) {
        let that = this
      e.preventDefault();
      this.form.validateFields((err, formData) => {
        if (!err) {
          this.loading = true;
          this.$http
            .post(this.routeUri("auth.change.submit"), formData)
            .then(function(response) {
                that.loading = false
                console.log(response.data)

                if (response.data.status) {
                    that.$notification.success({
                        message: "Success",
                        description: response.data.msg,
                    });

                    that.found = false;
                    that.foundInput = '';
                } else {
                    that.$notification.error({
                        message: "Invalid",
                        description: response.data.msg,
                    });
                }

            })
            .catch(function(error) {
                that.loading = false
                that.$notification.error({
                    message: "Invalid",
                    description: "Something error happend",
                });
                console.log(error.response.data.data)
            });
        }
      });
    },
    handleSearch(e) {
        let that = this
      e.preventDefault();
      this.form.validateFields((err, formData) => {
        if (!err) {
          this.loading = true;
          this.$http
            .post(this.routeUri("auth.change.check"), formData)
            .then(function(response) {
                console.log(response.data)
                if (response.data.status) {
                    that.foundInput = ''
                    that.found = true
                    that.loading = false
                } else {
                    that.foundInput = response.data.search
                    that.found = false
                    that.loading = false
                }
            })
            .catch(function(error) {
                that.foundInput = ''
                that.found = false
                that.loading = false
                that.$notification.error({
                    message: "Failed to find Account",
                    description: "We are unable to find your Account.",
                });
                console.log(error.response.data.data)
            });
        }
      });
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
        id="formChange"
        class="user-layout-login"
        ref="formChange"
        :form="form"
        @submit="handleSubmit"
      >
        <a-form-item>
          <a-input
            size="large"
            type="text"
            placeholder="username / email"
            v-decorator="['username', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="user" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item v-if="found">
          <a-input
            size="large"
            type="password"
            autocomplete="false"
            placeholder="old password"
            v-decorator="['password_old', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item v-if="found">
          <a-input
            size="large"
            type="password"
            autocomplete="false"
            placeholder="new password"
            v-decorator="['password_new', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item v-if="found">
          <a-input
            size="large"
            type="password"
            autocomplete="false"
            placeholder="new password confirmation"
            v-decorator="['password_new_confirm', { rules: [{ required: true }] }]"
          >
            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </a-form-item>

        <a-form-item v-if="!found">
          <a-button
            @click="handleSearch"
            size="large"
            type="info"
            htmlType="button"
            class="find-button"
            :loading="loading"
            :disabled="loading"
            >Find Account
          </a-button>
        </a-form-item>

        <span class="trawl-status-warning" v-if="foundInput != ''">Account {{ foundInput }} not found</span>

        <a-form-item v-if="found">
          <a-button
            size="large"
            type="warning"
            htmlType="submit"
            class="login-button"
            :loading="loading"
            :disabled="loading"
            >Change Password
          </a-button>
        </a-form-item>
      </a-form>
    </div>
  </basic-layout>
</template>
