<template>
  <a-row type="flex">
    <a-col :span="iconSize"></a-col>
    <a-col :span="24 - iconSize">
      <a-divider :style="{ margin: '10px 0px' }"></a-divider>
    </a-col>
  </a-row>
</template>
<script>
export default {
  props: {
    iconSize: {
      type: Number,
      default: 4
    }
  }
};
</script>
