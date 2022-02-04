<template>
  <div class="main-header">
    <div class="logo">
       <router-link to="/app/dashboard">
        <img :src="'/images/'+currentUser.logo" alt width="60" height="60">
       </router-link>
    </div>

    <div @click="sideBarToggle" class="menu-toggle">
      <div></div>
      <div></div>
      <div></div>
    </div>

    <div style="margin: auto"></div>

    <div class="header-part-right">
      <router-link 
        v-if="currentUserPermissions && currentUserPermissions.includes('Pos_view')"
        class="btn btn-outline-primary tn-sm btn-rounded"
        to="/app/pos"
      >
      <span class="ul-btn__text ml-1">POS</span>
      </router-link>
      <!-- Full screen toggle -->
      <i class="i-Full-Screen header-icon d-none d-sm-inline-block" @click="handleFullScreen"></i>
      <!-- Grid menu Dropdown -->

      <div class="dropdown">
        <b-dropdown
          id="dropdown"
          text="Dropdown Button"
          class="m-md-2 d-none  d-md-block"
          toggle-class="text-decoration-none"
          no-caret
          variant="link"
        >
          <template slot="button-content">
            <i
              class="i-Globe text-muted header-icon"
              role="button"
              id="dropdownMenuButton"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            ></i>
          </template>
          <vue-perfect-scrollbar
            :settings="{ suppressScrollX: true, wheelPropagation: false }"
            ref="myData"
            class="dropdown-menu-right rtl-ps-none notification-dropdown ps scroll"
          >
            <div class="menu-icon-grid">
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
          </vue-perfect-scrollbar>
        </b-dropdown>
      </div>
      <!-- Notificaiton -->
      <div class="dropdown">
        <b-dropdown
          id="dropdown-1" 
          text="Dropdown Button"
          class="m-md-2 badge-top-container d-none  d-sm-inline-block"
          toggle-class="text-decoration-none"
          no-caret
          variant="link"
        >
          <template slot="button-content" >
            <span class="badge badge-primary" v-if="notifs_alert > 0">1</span>
            <i class="i-Bell text-muted header-icon"></i>
          </template>
          <!-- Notification dropdown -->
          <vue-perfect-scrollbar
            :settings="{ suppressScrollX: true, wheelPropagation: false }"
            :class="{ open: getSideBarToggleProperties.isSideNavOpen }"
            ref="myData"
            class="dropdown-menu-right rtl-ps-none notification-dropdown ps scroll"
          >
            <div class="dropdown-item d-flex" v-if="notifs_alert > 0">
              <div class="notification-icon">
                <i class="i-Bell text-primary mr-1"></i>
              </div>
              <div class="notification-details flex-grow-1"
              v-if="currentUserPermissions && currentUserPermissions.includes('Reports_quantity_alerts')">
               <router-link  tag="a" to="/app/reports/quantity_alerts" >
                <p class="text-small text-muted m-0">
                  {{notifs_alert}} {{$t('ProductQuantityAlerts')}}
                  </p>
               </router-link>
              </div>
            </div>
           
          </vue-perfect-scrollbar>
        </b-dropdown>
      </div>
      <!-- Notificaiton End -->

      <!-- User avatar dropdown -->
      <div class="dropdown">
        <b-dropdown
          id="dropdown-1"
          text="Dropdown Button"
          class="m-md-2 user col align-self-end d-md-block"
          toggle-class="text-decoration-none"
          no-caret
          variant="link"
        >
          <template slot="button-content" >
            <img
              :src="'/images/avatar/'+currentUser.avatar"
              id="userDropdown"
              alt
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
          </template>

          <div class="dropdown-menu-right" aria-labelledby="userDropdown">
            <div class="dropdown-header">
              <i class="i-Lock-User mr-1"></i> <span >{{currentUser.username}}</span>
            </div>
            <router-link to="/app/profile" class="dropdown-item">{{$t('profil')}}</router-link>
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('setting_system')"
              to="/app/settings/System_settings"
              class="dropdown-item"
            >{{$t('Settings')}}</router-link>
            <a class="dropdown-item" href="#" @click.prevent="logoutUser">{{$t('logout')}}</a>
          </div>
        </b-dropdown>
      </div>
    </div>
  </div>

  <!-- header top menu end -->
</template>
<script>
import Util from "./../../../utils";
// import Sidebar from "./Sidebar";
import { isMobile } from "mobile-device-detect";
import { mapGetters, mapActions } from "vuex";
import { mixin as clickaway } from "vue-clickaway";
// import { setTimeout } from 'timers';
import FlagIcon from "vue-flag-icon";

export default {
  mixins: [clickaway],
  components: {
    FlagIcon
  },

  data() {
  
    return {
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
      
      isDisplay: true,
      isStyle: true,
      isSearchOpen: false,
      isMouseOnMegaMenu: true,
      isMegaMenuOpen: false,
      is_Load:false,
      // alerts:0,
     
    };
  },
 
   computed: {
     
     ...mapGetters([
       "currentUser",
      "getSideBarToggleProperties",
      "currentUserPermissions",
      "notifs_alert",
    ]),


  },

  methods: {
    
    ...mapActions([
      "changeSecondarySidebarProperties",
      "changeSidebarProperties",
      "changeThemeMode",
      "logout",
    ]),

    logoutUser() {
      this.$store.dispatch("logout");
    },

    SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("language/setLanguage", locale);
      Fire.$emit("ChangeLanguage");
    },

    handleFullScreen() {
      Util.toggleFullScreen();
    },
    logoutUser() {
      this.logout();
    },

    closeMegaMenu() {
      this.isMegaMenuOpen = false;
    },
    toggleMegaMenu() {
      this.isMegaMenuOpen = !this.isMegaMenuOpen;
    },
    toggleSearch() {
      this.isSearchOpen = !this.isSearchOpen;
    },

    sideBarToggle(el) {
      if (
        this.getSideBarToggleProperties.isSideNavOpen &&
        this.getSideBarToggleProperties.isSecondarySideNavOpen &&
        isMobile
      ) {
        this.changeSidebarProperties();
        this.changeSecondarySidebarProperties();
      } else if (
        this.getSideBarToggleProperties.isSideNavOpen &&
        this.getSideBarToggleProperties.isSecondarySideNavOpen
      ) {
        this.changeSecondarySidebarProperties();
      } else if (this.getSideBarToggleProperties.isSideNavOpen) {
        this.changeSidebarProperties();
      } else if (
        !this.getSideBarToggleProperties.isSideNavOpen &&
        !this.getSideBarToggleProperties.isSecondarySideNavOpen &&
        !this.getSideBarToggleProperties.isActiveSecondarySideNav
      ) {
        this.changeSidebarProperties();
      } else if (
        !this.getSideBarToggleProperties.isSideNavOpen &&
        !this.getSideBarToggleProperties.isSecondarySideNavOpen
      ) {

        this.changeSidebarProperties();
        this.changeSecondarySidebarProperties();
      }
    }
  }
};
</script>



