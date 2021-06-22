<template>
  <a-divider class="trawl-divider" :orientation="orientation">
    <slot></slot>
  </a-divider>
</template>
<script>
export default {
  props: ["orientation"],
};
</script>
