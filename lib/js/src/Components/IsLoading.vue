<template>
  <div class="is-loading"></div>
</template>

<script>
export default {
  name: "IsLoading",
}
</script>

<style scoped lang="scss">
.is-loading {
  background: #eee;
  background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
  border-radius: 2px;
  background-size: 200% 100%;
  animation: 1.5s shine linear infinite;
  display: flex;
  @keyframes shine {
    to {
      background-position-x: -200%;
    }
  }

}
</style>