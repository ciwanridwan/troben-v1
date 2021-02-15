<template>
  <div class="basic-layout">
    <div class="content">
      <slot></slot>
    </div>

    <slot name="footer">
      <page-footer/>
    </slot>
  </div>
</template>
<script>
export default {
  name: 'BasicLayout'
}
</script>
