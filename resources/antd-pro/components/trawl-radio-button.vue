<template>
  <a-radio-button class="trawl-radio-button trawl-text-center" :value="value">
    <div class="trawl-radio-button--icon">
      <slot name="icon"></slot>
    </div>
    <slot></slot>
  </a-radio-button>
</template>
<script>
export default {
  props: {
    value: null
  }
};
</script>
