<template>
  <div>
    <div class="customizer" :class="{ open: isOpen }">
      <div class="handle" @click="isOpen = !isOpen">
        <i class="i-Gear spin"></i>
      </div>

      <vue-perfect-scrollbar
        :settings="{ suppressScrollX: true, wheelPropagation: false }"
        class="customizer-body ps rtl-ps-none"
      >
        <div
          class
          v-if="getThemeMode.layout != 'vertical-sidebar' && getThemeMode.layout != 'vertical-sidebar-two'"
        >
          <div class="card-header" id="headingOne">
            <p class="mb-0">RTL</p>
          </div>

          <div class="card-body">
            <label class="checkbox checkbox-primary">
              <input type="checkbox" id="rtl-checkbox" @change="changeThemeRtl" />
              <span>Enable RTL</span>
              <span class="checkmark"></span>
            </label>
          </div>
        </div>

        <div class>
          <div class="card-header">
            <p class="mb-0">Dark Mode</p>
          </div>

          <div class="card-body">
            <label class="switch switch-primary mr-3 mt-2" v-b-popover.hover.left="'Dark Mode'">
              <input type="checkbox" @click="changeThemeMode" />
              <span class="slider"></span>
            </label>
          </div>
        </div>

         <div class>
          <div class="card-header">
            <p class="mb-0">Language</p>
          </div>

          <div class="card-body">
             <div class="menu-icon-language">
              <a @click="SetLocal('en')">
                <i title="en" class="flag-icon flag-icon-squared flag-icon-gb"></i> English
              </a>
              <a @click="SetLocal('fr')">
                <i title="fr" class="flag-icon flag-icon-squared flag-icon-fr"></i>
                <span class="title-lang">French</span>
              </a>
              <a @click="SetLocal('ar')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-sa"></i>
                <span class="title-lang">Arabic</span>
              </a>
              <a @click="SetLocal('tur')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-tr"></i>
                <span class="title-lang">Turkish</span>
              </a>

              <a @click="SetLocal('sm_ch')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-cn"></i>
                <span class="title-lang">Simplified Chinese</span>
              </a>

              <a @click="SetLocal('thai')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-th"></i>
                <span class="title-lang">Thaï</span>
              </a>

              <a @click="SetLocal('hn')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-in"></i>
                <span class="title-lang">Hindi</span>
              </a>

              <a @click="SetLocal('de')">
                <i title="de" class="flag-icon flag-icon-squared flag-icon-de"></i>
                <span class="title-lang">German</span>
              </a>
              <a @click="SetLocal('es')">
                <i title="es" class="flag-icon flag-icon-squared flag-icon-es"></i>
                <span class="title-lang">Spanish</span>
              </a>
              <a @click="SetLocal('it')">
                <i title="it" class="flag-icon flag-icon-squared flag-icon-it"></i>
                <span class="title-lang">Italien</span>
              </a>
              <a @click="SetLocal('Ind')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-id"></i>
                <span class="title-lang">Indonesian</span>
              </a>

              <a @click="SetLocal('tr_ch')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-cn"></i>
                <span class="title-lang">Traditional Chinese</span>
              </a>

              <a @click="SetLocal('ru')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-ru"></i>
                <span class="title-lang">Russian</span>
              </a>

              <a @click="SetLocal('vn')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-vn"></i>
                <span class="title-lang">Vietnamese</span>
              </a>
              <a @click="SetLocal('kr')">
                <i title="sa" class="flag-icon flag-icon-squared flag-icon-kr"></i>
                <span class="title-lang">Korean</span>
              </a>
            </div>
          </div>
        </div>
      </vue-perfect-scrollbar>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";

export default {
  data() {
    return {
      isOpen: false,
       langs: [
        "en",
        "fr",
        "ar",
        "de",
        "es",
        "it",
        "Ind",
        "thai",
        "tr_ch",
        "sm_ch",
        "tur",
        "ru",
        "hn",
        "vn",
        "kr"
      ],
      
    };
  },

  computed: {
    ...mapGetters(["getThemeMode", "getcompactLeftSideBarBgColor"]),
  },

  methods: {
    ...mapActions([
      "changeThemeRtl",
      "changeThemeLayout",
      "changeThemeMode",
      "changecompactLeftSideBarBgColor",
    ]),

    SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("language/setLanguage", locale);
      Fire.$emit("ChangeLanguage");
    },
   
  },
};
</script>

<style lang="scss" scoped>
</style>