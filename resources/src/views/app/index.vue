<template>
  <div>
    <component :is="getThemeMode.layout"></component>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";

export default {
  components: {},
  data() {
    return {};
  },
  computed: {
    ...mapGetters(["getThemeMode"])
  },
  methods: {}
};
</script>
