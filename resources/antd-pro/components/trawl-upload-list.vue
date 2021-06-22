<template>
  <div class="trawl-upload-list">
    <a-upload
      :before-upload="() => false"
      list-type="picture-card"
      :file-list="fileList"
      @preview="handlePreview"
      @change="handleChange"
    >
      <div v-if="fileList.length < 5">
        <a-button
          class="trawl-upload-list--button trawl-button-success"
          icon="plus"
          size="large"
          type="primary"
        ></a-button>
      </div>
    </a-upload>
    <a-modal :visible="previewVisible" :footer="null" @cancel="handleCancel">
      <template slot="closeIcon">
        <a-icon type="close" @click="handleCancel"></a-icon>
      </template>
      <img alt="example" style="width: 100%" :src="previewImage" />
    </a-modal>
  </div>
</template>
<script>
function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = (error) => reject(error);
  });
}
export default {
  data() {
    return {
      previewVisible: false,
      previewImage: "",
      fileList: [],
    };
  },
  methods: {
    handleCancel() {
      this.previewVisible = false;
    },
    async handlePreview(file) {
      if (!file.url && !file.preview) {
        file.preview = await getBase64(file.originFileObj);
      }
      this.previewImage = file.url || file.preview;
      this.previewVisible = true;
    },
    handleChange({ fileList }) {
      this.fileList = fileList;
    },
  },
  watch: {
    fileList: {
      handler: function (value) {
        this.$emit("change", value);
        this.$emit("input", value);
      },
    },
  },
};
</script>
