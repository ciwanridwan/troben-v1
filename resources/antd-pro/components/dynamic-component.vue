<template>
  <div>
    <slot :record="record"> </slot>
  </div>
</template>
<script>
export default {
  props: ["record"]
};
</script>
