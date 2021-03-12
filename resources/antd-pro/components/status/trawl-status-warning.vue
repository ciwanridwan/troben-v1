<template>
  <div>
    <a-badge status="warning" text=""></a-badge>
    <span :class="['trawl-status-warning']">
      <slot></slot>
    </span>
  </div>
</template>
