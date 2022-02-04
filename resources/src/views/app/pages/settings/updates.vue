<template>
  <div class="main-content">
    <breadcumb :page="$t('Updates')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <b-row >
        <b-col md="12" class="text-center">
          <span style="font-size: 1.5rem;font-weight: 700;">{{ $t("Update_is_Available") }} v3.2.0</span>
        </b-col>
        
          <b-col md="12" class="text-center mt-2">
          <span style="color: blueviolet; font-weight: 600; ">{{ $t("Current_Version") }} v3.0  -> {{ $t("New_Version") }} : v3.2.0</span>
         </b-col>

          <b-col md="12" class="mt-5">

             <button  class="btn btn-success btn-sm m-1">
              {{ $t("See_what_new") }}
            </button>


            <button  class="btn btn-primary btn-sm m-1">
              {{ $t("How_to_install_updates") }}
            </button>

            <button  class="btn btn-danger btn-sm m-1">
              {{ $t("Download_Updates") }}
            </button>
            
          </b-col>
         
      </b-row>
      <b-row style="display:none">
        <b-col md="12" class="text-center">
          <span style="font-size: 1.5rem;font-weight: 700;">{{ $t("your_app_is_up_to_date") }}</span>
        </b-col>
      </b-row>
    </b-card>
  </div>
</template>



<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Updates"
  },
  data() {
    return {
      isLoading: true,
    };
  },


  methods: {
   

    DownloadBackup(file){

       // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("download_Backup/" + file, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download" , file);
          document.body.appendChild(link);
          link.click();
          // Complete the animation of theprogress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of theprogress bar.
          setTimeout(() => NProgress.done(), 500);
        });
      
    },

    //----------------------------------------  Get All backups -------------------------\\
    Get_Backups() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("GetBackup")
        .then(response => {
          this.backups = response.data.backups;
          this.totalRows = response.data.totalRows;

          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

   
  }, //end Method

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Backups();

    Fire.$on("Generate_Backup", () => {
      setTimeout(() => {
        this.Get_Backups();
      }, 500);
    });

   
  }
};
</script>